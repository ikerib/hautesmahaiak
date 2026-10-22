# CLAUDE.md

Guía para trabajar en este repositorio.

## Qué es

**Hautesmahaiak**: aplicación del Ayuntamiento de Pasaia para que la ciudadanía consulte si ha salido
elegida en el sorteo de **mesas electorales**, y para que el personal municipal gestione las elecciones
y las sustituciones.

- **Parte pública (kontsulta)**: el ciudadano introduce NAN/DNI y fecha de nacimiento y se le dice si es
  miembro de mesa en la elección activa, con el mensaje correspondiente (hautatua / ez hautatua).
- **Parte de gestión (`/kudeatu`, `ROLE_ADMIN`)**: alta de elecciones mediante un asistente de 5 pasos,
  importación de CSV, listado y filtrado de los ciudadanos sorteados, y sustitución (baja del titular →
  pasa a activo el siguiente suplente, con aviso por email).

El código, los nombres de dominio, los commits y la UI están en **euskera** (con traducción al castellano).
Mantener esa convención: entidades, variables y mensajes nuevos en euskera.

## Stack

- PHP 8.3, **Symfony 6.4** (LTS), Doctrine ORM 3, MySQL 8.2
- Frontend sin bundler: **AssetMapper + importmap** (`importmap.php`), **Tailwind** vía
  `symfonycasts/tailwind-bundle`, **Flowbite** (+ `tales-from-a-dev/flowbite-bundle` como tema de formularios)
- Symfony UX: Stimulus, Turbo (Drive + Frames + Streams), LiveComponent, TwigComponent, Dropzone, Icons
- Editor de texto enriquecido: **Trix** (los campos de mensajes son `HiddenType` + editor Trix)
- Autenticación: login de formulario simple con **un único usuario** definido en `.env` (`APP_LOGIN_USER` / `APP_LOGIN_PASSWORD`)
- Paginación: Pagerfanta. Errores: Sentry. Mail en dev: Mailpit (`compose.override.yaml`)
- **No hay tests** (`tests/` no existe) ni migraciones: el esquema se aplica con `doctrine:schema:update --force`.

## Comandos

Todo corre en Docker (contenedor `hautesmahaiak`, usuario `appuser`).

```bash
make start            # docker compose up -d (app en :80, mysql en :3306, mailpit :8025)
make ssh              # bash dentro del contenedor (alias sf=bin/console)
make composer-install
make logs

# dentro del contenedor
bin/console doctrine:schema:update --force
bin/console tailwind:build --watch      # recompilar CSS mientras se desarrolla
bin/console importmap:require <paquete> # añadir dependencia JS
php-cs-fixer fix                        # estilo @Symfony (.php-cs-fixer.dist.php)
```

`deploy.sh` (se ejecuta dentro del contenedor de producción) hace: schema update, importmap:install,
tailwind:build, asset-map:compile, cache:clear y `chown www-data var/`.

## Arquitectura

### Entidades (`src/Entity`)

- **Hauteskundea** (elección): `izena_eu/es`, `data`, `active`, `oharrak_eu/es`,
  `mezua_hautatua_eu/es`, `mezua_ez_hautatua_eu/es`. OneToMany → Herritarra (cascade remove).
  `HauteskundeaRepository::getActive()` usa `getOneOrNullResult()` → **solo puede haber una elección activa**;
  si hay dos, lanza excepción y la consulta pública muestra `default/akatsa.html.twig`.
- **Herritarra** (persona sorteada): `dist`, `secc`, `mesa`, `cargofinal` (código del cargo), `kargua` (eu),
  `cargo` (es), `ident` (DNI), nombre/apellidos, `eguna/hilabetea/urtea` + `jaioteguna` (DATE),
  `active` (smallint).
- **Errolda** (padrón): tabla **temporal** (`ident`, `dianac`, `mesnac`, `anonac`) que solo sirve para
  completar la fecha de nacimiento al importar; se vacía con TRUNCATE al terminar.
- **User**: `username` (= `APP_LOGIN_USER`), `NA` (opcional, heredado), `roles` (JSON, `ROLE_ADMIN` al hacer login),
  `mailfrom`/`mailto` para las notificaciones de sustitución. Sin contraseña.

### Lógica de cargos y estados

- Códigos `CARGOFINAL`: `P`, `V1`, `V2` (titulares) y sus suplentes `PS1..PSn`, `V1S1..`, `V2S1..`.
- Al importar: `active = 1` si el código está en `P, PS1, PS2, V1, V1S1, V1S2, V2, V2S1, V2S2`; si no, `0`.
- `active`: **1** = activo/convocado, **0** = en reserva, **-1** = baja.
- Sustitución (`HerritarraController::ordezkatu`): `getNextCode()` recorre la secuencia
  `P, PS1..PS30, V1, V1S1..V1S30, V2, V2S1..V2S30` y `getHurrengoa()` busca recursivamente en la misma
  `dist/secc/mesa` el siguiente con `active = 0`. Al confirmar: titular → `-1`, siguiente → `1`, y se envía
  email (`herritarra/_email_ordezkatu.html.twig`).
- La tabla código→texto (`setKarguaByCode`) está **duplicada** en `CsvHelper` y `HerritarraController`;
  si se cambia, cambiar las dos (o extraerla).

### Flujo de alta de elección (asistente)

`HauteskundeaController`, rutas `/kudeatu/wizard` → `/{id}/wizard2..5`:
1. nombre eu/es, fecha, activa — 2. notas (oharrak) — 3. mensaje "hautatua" — 4. mensaje "ez hautatua" —
5. subir dos CSV con Dropzone: **errolda** y **zozketa**.

En el paso 5, `App\Utils\CsvHelper`: `importErrolda()` → `importZozketa()` → `mergeCsv()` (rellena
`jaioteguna` a partir de Errolda por `ident`; si falta alguien, error "Ez da herritarra Erroldan topatu") →
`hustuErrolda()` (TRUNCATE). Los errores se muestran en `hauteskundea/csverror.html.twig`.

- CSV con separador `;`, cabecera en la primera fila, columnas por nombre (`DIST`, `SECC`, `MESA`,
  `NLOCAL`, `NLOCALB`, `DOMI1`, `DOMI2`, `CARGOFINAL`, `IDENT`, `NOMBRE`, `APELLIDO1`, `APELLIDO2`;
  padrón: `IDENT`, `DIANAC`, `MESNAC`, `ANONAC`). Ejemplos en `doc/demo/`.
- Los ficheros se mueven a `/tmp/errolda.csv` / `/tmp/zozketa.csv` (nombres fijos).
- Existe además una importación antigua de un solo fichero (`/kudeatu/{_locale}/herritarra/dropzone/{id}`,
  `app_herritarra_fitxategia`) que espera las columnas `Eguna/Hilabetea/Urtea` en el propio sorteo.

### Rutas principales

| Ruta | Controlador | Notas |
|---|---|---|
| `/` → `/kontsulta/{_locale}/` | `DefaultController` | Público. Layout `frontend.html.twig` |
| `/kudeatu[/{_locale}]/…` | `HauteskundeaController` | CRUD + wizard + `hustu` (borra los ciudadanos de una elección) |
| `/kudeatu/{_locale}/herritarra/{hauteskundeaid}/index` | `HerritarraController` | Filtros `dists[]`, `seccs[]`, `mesas[]`, `egoeras[]`, `query`; responde Turbo Stream si se pide |
| `/kudeatu/user/…` | `UserController` | CRUD de usuarios |
| `/login`, `/logout` | `SecurityController` | Login con el usuario del `.env` |
| `/healthcheck` | `DefaultController` | **Lanza una excepción a propósito** (prueba de Sentry), no es un healthcheck real |

### Seguridad (`src/Security`)

- `EnvLoginAuthenticator`: formulario en `/login` (`SecurityController`, `templates/security/login.html.twig`).
  Compara `_username`/`_password` con `APP_LOGIN_USER`/`APP_LOGIN_PASSWORD` del `.env` usando `hash_equals`,
  con token CSRF `authenticate`. Si alguna de las dos variables está vacía, nadie puede entrar.
- Tras validar, carga o crea la entidad `User` con ese `username` y le pone `ROLE_ADMIN`. La entidad se
  mantiene porque `mailfrom`/`mailto` se usan para los avisos de sustitución y la cabecera enlaza a sus ajustes.
- `access_control`: `^/login` público, `^/kudeatu` → `ROLE_ADMIN`; el resto (consulta ciudadana) es público.
  Logout en `/logout` (en Twig, `logout_path()`); `login_throttling` a 5 intentos/minuto.
- Historia: hasta 2024 era LDAP + Giltza; en 2026-10 se intentó el SSO Municipal pero el servidor de producción
  no llega a `sso.pasaia.net`, así que se dejó este login simple.

### Frontend

- Entrypoints: `assets/app.js` (gestión: Turbo + view transitions, Flowbite re-init en `turbo:render`, Trix)
  y `assets/frontend.js` (parte pública).
- Controladores Stimulus en `assets/controllers/`: `autosubmit`, `closeable`, `datepicker` (flowbite-datepicker
  con locales eu/es en `assets/lib/`), `modala`, `trix`.
- Modales y formularios en `turbo-frame`; patrón `renderBlock(..., 'stream_success')` para respuestas Turbo Stream.
  Variantes Tailwind propias: `turbo-frame:` y `modal:`.
- LiveComponents `HauteskundeaForm` / `HauteskundeaFormWizard` (`src/Twig/Components/Form`). Ojo: en
  `HauteskundeaForm` el `ComponentWithFormTrait` está desactivado porque la validación en vivo hace
  desaparecer el editor Trix.
- Iconos locales en `assets/icons/` (`<twig:ux:icon name="mdi:user-off" />`).

### Traducciones

`translations/messages+intl-icu.{eu,es}.yaml`, `validators.*`, `security.*`, `pagerfanta.*`.
Locale por defecto `eu`, fallback `es`. Las rutas llevan `{_locale}`.

## Despliegue

- CI (`.github/workflows/docker-image.yml`): en cada push a `main` construye `docker/DockerfileProd` y
  publica `ikerib/hautesmahaiak:latest` en Docker Hub.
- Servidor de producción: máquina propia **172.23.64.16** (`ssh root@172.23.64.16`), dominio
  `hautesmahaiak2.pasaia.net` (resuelve directo a esa IP, **solo HTTP en el puerto 80**, sin proxy delante).
  Ficheros en `/root/docker/`: `compose.yml` (aún con ese nombre) y `.env` montado como `.env.local`.
  **Despliegue** (el servidor no tiene internet, no puede hacer pull):
  1. `docker build -f docker/DockerfileProd -t ikerib/hautesmahaiak:AAAA-MM-DD[-vN] .` (los assets se compilan dentro).
  2. `docker save <tag> | gzip -1 | ssh root@172.23.64.16 'gunzip | docker load'`.
  3. En `/root/docker/compose.yml` cambiar el tag; `docker compose up -d --no-deps app && docker compose exec -T app sh deploy.sh`.
  - ⚠️ Usar siempre `deploy.sh` (acaba con `chown -R www-data var/`). Un `cache:clear` suelto como root deja
    `var/cache/prod` de root: la caché no se escribe y `login_throttling` deja de bloquear sin dar error.
  - ⚠️ El `.env` del servidor debe ser **644**: se monta como `.env.local` y lo lee `www-data`; con 600 la app da Fatal error.
  - Copias y vuelta atrás en `/root/backups/`; la imagen anterior a 2026-10 es `ikerib/hautesmahaiak:pre-sso-20261022-0622`.
- Convención del autor: en producción los ficheros se llaman siempre `compose.yaml` y `Makefile` y viven en
  `dist/` (se copian con `scp -r dist/. servidor:/ruta/`). **Ese `dist/` todavía no existe en este repo**;
  el `compose.yml` y el `Makefile` de la raíz son los de desarrollo.

## Cosas a tener en cuenta

- `.env` está versionado con valores de ejemplo y `APP_ENV=prod`; los valores reales van en `.env.local`
  (no versionado). En dev, cambiar a `APP_ENV=dev` en `.env.local`.
- La consulta pública busca por `ident` + `jaioteguna` + `active = 1` **sin filtrar por elección**: depende
  de que en BD solo estén los datos de la elección vigente (o de usar `hustu`).
- `HerritarraRepository::getAllQueryBuilder()` no filtra por `hauteskundea` y los `orderBy` se pisan entre
  sí (solo vale el último, `mesa`); el parámetro `$sort` no se usa realmente.
- En `ordezkatu` el email se envía `->to($user->getMailfrom())` (no a `mailto`).
- Tras cambiar entidades, no hay migraciones: `bin/console doctrine:schema:update --dump-sql` para revisar
  y `--force` para aplicar.
- **Bloqueo de red (2026-10-22):** 172.23.64.16 no tiene salida a internet ni llega a `sso.pasaia.net`
  (172.28.64.27, puertos 80/443 sin respuesta). Mientras no se abra esa regla, no se puede usar el SSO Municipal
  en ese servidor. Las imágenes se le pasan con `docker save | ssh … docker load` (no puede hacer pull).
