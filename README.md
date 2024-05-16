# Hautesmahaiak

Hauteskundeak direnean herritarrek hautatuak izan diren kontsultatzeko aplikazioa.
Bi atal ditu

### Kontsulta:

Herritarraren gunea. NAN eta jaioteguna eskatuko ditu, eta hautatua izan den edo ez erantzungo dio.
Horretaz gain, informazio gehigarria eskainkiko dio.


### Admin gunea

Bertan hauteskunde desberdinak kudeatu ahalko ditugu. Hauteskunde berria sortzean bi fitxategi
igo behar ditugu csv formatuan:
- Errolda (NAN, Jaioteguna)
- Zozketa

Zozketatuko Herritarraren datuak, datu basean gordeko dira eta erroldako informazioa erabiliko da 
datuak osatzeko (Jaioteguna).

    Atal hau garapenean dago oraindik. Orain momentuan fitxategi bakarraigo behar datu guztiekin, zozketa + jaioteguna.

Titularrak direnak berdez agertuko zaizkigu eta botoi baten bidez baja emango zaio eta automatikoki hurrengoa jarriko du
aktibo.

Horretaz gain, zehaztutako helbidera email bat bidaliko du aldaketaren jakinarazpenarekin.


## Instalatzeko prozesua

Aplikazioa jartzeko docker instalatua izatea beharrezkoa da.

Sortu `compose.yml` fitxategia:

    services:
        app:
            container_name: hautesmahaiak-app
            image: ikerib/hautesmahaiak:latest
            volumes:
                - .env:/var/www/html/.env.local
                - data_var:/var/www/html/var
            ports:
                - '80:80'
                - '443:443'
            depends_on:
                - db
    
        db:
            container_name: hautesmahaiak-mysql
            image: mysql:8.2.0
            ports:
                - '3306:3306'
            environment:
                MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
                MYSQL_DATABASE: ${MYSQL_DATABASE}
                MYSQL_USER: ${MYSQL_USER}
                MYSQL_PASSWORD: ${MYSQL_PASSWORD}
            volumes:
                - hautesmahaiak-mysql-data:/var/lib/mysql
            command: [ 'mysqld', '--character-set-server=utf8mb4', '--collation-server=utf8mb4_unicode_ci' ]
    
    volumes:
        hautesmahaiak-mysql-data:
        data_var:

eta `.env` fitxategi bat beharko duzu. Moldatu hau zure beharretara:

    ###> symfony/framework-bundle ###
    APP_ENV=prod
    APP_DEBUG=0
    APP_SECRET=8a8bbe2b50414d28742e295f870e470c
    ###< symfony/framework-bundle ###
    
    ###> doctrine/doctrine-bundle ###
    DATABASE_URL="mysql://dbuser:dbpass@db:3306/hautesmahaiak?charset=utf8mb4"
    ###< doctrine/doctrine-bundle ###
    
    ###> DOCKER ###
    MYSQL_DATABASE=hautesmahaiak
    MYSQL_ROOT_PASSWORD=dbpass
    MYSQL_USER=dbuser
    MYSQL_PASSWORD=dbpass
    ###< DOCKER ###
    
    ###> oAuth ###
    CLIENT_ID='client_id'
    CLIENT_SECRET='cliente_secret'
    ###< oAuth###
    
    ###> LDAP konexiorako datuak ###
    LDAP_IP=XXX.XXX.XXX.XXX
    LDAP_BASE_DN=DC=domain,DC=net
    LDAP_SEARCH_DN=CN=user,CN=Users,DC=pasaia,DC=net
    LDAP_PASSWD=pass
    LDAP_ADMIN_TALDEAK="" # adibidea => 'Rol-taldea1, Rol-taldea2'
    LDAP_USER_TALDEA="" # adibidea => 'Rol-taldea1, Rol-taldea2'
    ###< LDAP konexiorako datuak ###
    
    ###> symfony/webapp-meta ###
    MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
    ###< symfony/webapp-meta ###
    
    UID=0
    
    ###> symfony/mailer ###
    # MAILER_DSN=null://null
    ###< symfony/mailer ###

Abiarazi sistema `docker compose up -d`

Aplikazioa martxan egongo da baina hainbat pausu behar dira, horretan laguntzeko `deploy.sh` fitxategia exekutatu.

    docker compose exec app sh deploy.sh 
