<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Ldap\Adapter\CollectionInterface;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class PasaiaLdapService
{
    private string $ip;
    private string $ldap_username;
    private string $basedn;
    private string $passwd;
    private string $ldapAdminTaldea;
    private string $ldapUserTaldea;
    private EntityManagerInterface $em;

    public function __construct(
        string $ip,
        string $ldap_username,
        string $basedn,
        string $passwd,
        string $ldapAdminTaldea,
        string $ldapUserTaldea,
        EntityManagerInterface $em
    ) {
        $this->ip = $ip;
        $this->ldap_username = $ldap_username;
        $this->basedn = $basedn;
        $this->passwd = $passwd;
        $this->ldapAdminTaldea = $ldapAdminTaldea;
        $this->ldapUserTaldea = $ldapUserTaldea;
        $this->em = $em;
    }

    // LDAP
    public function checkCredentials($username, $password): bool
    {
        $ip = $this->ip;
        $searchdn = "CN=$username,CN=Users,DC=pasaia,DC=net";

        /**
         * LDAP KONTSULTA EGIN erabiltzailearen bila.
         */
        $srv = "ldap://$ip:389";
        $ldap = Ldap::create('ext_ldap', ['connection_string' => $srv]);
        try {
            $ldap->bind($searchdn, $password);
        } catch (ConnectionException $e) {
            // LDAP-en ez dagoenez datu basean begiratzea nahi dugu orain beraz false itzuliko dugu, bestela komentatutakoa litzateke egokiena
            // throw new CustomUserMessageAuthenticationException('Pasahitza ez da zuzena.');
            return false;
        }

        return true;
    }

    public function createDbUserFromLdapData($username): User
    {
        $ldapQuery = $this->getLdapInfoByUsername($username);
        /** @var Entry $ldapData */
        $ldapData = $ldapQuery[0];
        $user = new User();
        $user->setUsername($username);
        $user = $this->syncUserInfoFromLdap($user, $ldapData);

        return $user;
    }

    public function updateDbUserDataFromLdapByUsername($username): User
    {
        $dbUser = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
        $ldapQuery = $this->getLdapInfoByUsername($username);
        $ldapData = $ldapQuery[0];
        $dbUser = $this->syncUserInfoFromLdap($dbUser, $ldapData);

        return $dbUser;
    }

    private function syncUserInfoFromLdap(User $user, $ldapData): User
    {
        $ldap = $ldapData->getAttributes();

        if (array_key_exists('employeeID', $ldap)) {
            $user->setNA((string) $ldap['employeeID'][0]);
        }

        if (array_key_exists('mail', $ldap)) {
            $user->setEmail((string) $ldap['mail'][0]);
        }

        if (array_key_exists('givenName', $ldap)) {
            $user->setIzena((string) $ldap['givenName'][0]);
        }

        if (array_key_exists('sn', $ldap)) {
            $user->setAbizena((string) $ldap['sn'][0]);
        }

        if (array_key_exists('givenName', $ldap) && array_key_exists('sn', $ldap)) {
            $user->setDisplayname((string) $ldap['givenName'][0].' '.(string) $ldap['sn'][0]);
        } elseif (!array_key_exists('givenName', $ldap)) {
            $user->setDisplayname((string) $ldap['sn'][0]);
        } elseif (!array_key_exists('sn', $ldap)) {
            $user->setDisplayname((string) $ldap['givenName'][0]);
        } else {
            $user->setDisplayname($user->getUsername());
        }

        if (array_key_exists('description', $ldap)) {
            $user->setLanpostua((string) $ldap['description'][0]);
        }

        if (array_key_exists('department', $ldap)) {
            $user->setSaila((string) $ldap['department'][0]);
        }

        $ldapTaldeak = $this->getLdapUserMembershipGroupsRecursivelyByUsername($user->getUsername());
        //        $user->setLdapTaldeak($ldapTaldeak);

        // begiratu talde bat baino gehiago dituen komaz bereizita
        $lat = []; // array ldap admin taldeak
        $lkt = []; // array ldap kudeatu taldeak
        $lut = []; // array ldap user taldeak
        if (false !== strpos($this->ldapAdminTaldea, ',')) {
            $lat = explode(',', $this->ldapAdminTaldea);
            $lat = array_map('trim', $lat);
        } else {
            $lat[] = $this->ldapAdminTaldea;
        }
        //        if (strpos($this->ldapKudeatuTaldea, ',') !== false){
        //            $lkt = explode(',', $this->ldapAdminTaldea);
        //            $lkt = array_map('trim',$lkt);
        //        } else {
        //            $lkt[] = $this->ldapAdminTaldea;
        //        }
        if (false !== strpos($this->ldapUserTaldea, ',')) {
            $lut = explode(',', $this->ldapAdminTaldea);
            $lut = array_map('trim', $lut);
        } else {
            $lut[] = $this->ldapAdminTaldea;
        }

        foreach ($lat as $at) {
            if ($this->in_array_r($at, $ldapTaldeak, false)) {
                $rol[] = 'ROLE_ADMIN';
            }
        }

        foreach ($lkt as $at) {
            if ($this->in_array_r($at, $ldapTaldeak, false)) {
                $rol[] = 'ROLE_KUDEATU';
            }
        }

        foreach ($lut as $at) {
            if ($this->in_array_r($at, $ldapTaldeak, false)) {
                $rol[] = 'ROLE_USER';
            }
        }

        $user->setRoles($rol);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function getLdapInfoByUsername($username): CollectionInterface
    {
        $ip = $this->ip;
        $basedn = $this->basedn;
        $passwd = $this->passwd;

        $srv = "ldap://$ip:389";
        $ldap = Ldap::create('ext_ldap', ['connection_string' => $srv]);
        $ldap->bind('CN=izfeprint,CN=Users,DC=pasaia,DC=net', $passwd);

        return $ldap->query($basedn, "(sAMAccountName=$username)", [])->execute();
    }

    public function getLdapInfoByNA($NA): CollectionInterface
    {
        $ip = $this->ip;
        $basedn = $this->basedn;
        $passwd = $this->passwd;

        $srv = "ldap://$ip:389";
        $ldap = Ldap::create('ext_ldap', ['connection_string' => $srv]);
        $ldap->bind('CN=izfeprint,CN=Users,DC=pasaia,DC=net', $passwd);

        return $ldap->query($basedn, "(employeeID=$NA)", [])->execute();
    }

    public function hydrateAll(array $ldapEntries): array
    {
        $users = [];
        /** @var Entry $ldapEntry */
        foreach ($ldapEntries as $ldapEntry) {
            // Bereziak kendu
            if ($ldapEntry->hasAttribute('department')) {
                if ('Bereziak' !== $ldapEntry->getAttribute('department')[0]) {
                    $users[] = $this->hydrateOne($ldapEntry);
                }
            }
        }

        return $users;
    }

    public function hydrateOne(Entry $ldapEntry): User
    {
        $user = new User();
        $user->setUsername($ldapEntry->getAttribute('sAMAccountName')[0]);
        if ($ldapEntry->hasAttribute('mail')) {
            $user->setEmail($ldapEntry->getAttribute('mail')[0]);
        }
        if ($ldapEntry->hasAttribute('department')) {
            $user->setSaila($ldapEntry->getAttribute('department')[0]);
        }
        if ($ldapEntry->hasAttribute('employeeID')) {
            $user->setNA($ldapEntry->getAttribute('employeeID')[0]);
        }
        if ($ldapEntry->hasAttribute('description')) {
            $user->setLanpostua($ldapEntry->getAttribute('description')[0]);
        }
        if ($ldapEntry->hasAttribute('displayName')) {
            $user->setDisplayname($ldapEntry->getAttribute('displayName')[0]);
        }
        if ($ldapEntry->hasAttribute('memberof')) {
            $members = $ldapEntry->getAttribute('memberof');
            $udaltzainAdministrariaDa = false;
            foreach ($members as $key => $value) {
                $sp = ldap_explode_dn($value, 1);
                if ('APP-Web_Aurki2de' === $sp[0]) {
                    $rol = ['ROLE_ADMIN'];
                    $user->setRoles($rol);
                }
                if ('ROL-Antolakuntza_Informatika' === $sp[0]) {
                    $rol = ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];
                    $user->setRoles($rol);
                }
                if (str_contains($sp[0], 'Sailburu')) {
                    $rol = ['ROLE_SAILBURUA'];
                    $user->setRoles($rol);
                }
            }
        }
        if ($ldapEntry->hasAttribute('givenName')) {
            $user->setIzena($ldapEntry->getAttribute('givenName')[0]);
        }
        if ($ldapEntry->hasAttribute('sn')) {
            $user->setAbizena($ldapEntry->getAttribute('sn')[0]);
        }

        return $user;
    }

    public function in_array_r($needle, $haystack, $strict = false): bool
    {
        foreach ($haystack as $item) {
            if (!$strict && is_string($needle) && (is_float($item) || is_int($item))) {
                $item = (string) $item;
            }

            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
                return true;
            }
        }

        return false;
    }

    public function getLdapUserMembershipGroupsRecursivelyByUsername($username): array
    {
        $ip = $this->ip;
        $basedn = $this->basedn;
        $passwd = $this->passwd;
        $ldapSarbideak = [];
        $ldapRolak = [];
        $ldapSailak = [];
        $ldapTaldeak = [];
        $ldapApp = [];

        $srv = "ldap://$ip:389";
        $ldap = Ldap::create('ext_ldap', ['connection_string' => $srv]);
        $ldap->bind('CN=izfeprint,CN=Users,DC=pasaia,DC=net', $passwd);
        $gFilter = "(member:1.2.840.113556.1.4.1941:=cn=$username,cn=users,dc=pasaia,dc=net)";
        $query = $ldap->query($basedn, $gFilter);
        $allGroups = $query->execute();

        /**
         * @var int   $key
         * @var Entry $group
         */
        foreach ($allGroups as $key => $group) {
            if ('count' !== $key) {
                $taldea = $group->getAttribute('name')[0];
                switch ($taldea) {
                    case str_starts_with($taldea, 'APP-'):
                        $ldapApp[] = $taldea;
                        break;
                    case str_starts_with($taldea, 'ROL-'):
                        $ldapRolak[] = $taldea;
                        break;
                    case str_starts_with($taldea, 'Saila-'):
                        $ldapSailak[] = $taldea;
                        break;
                    case str_starts_with($taldea, 'SARBIDE-'):
                        $ldapSarbideak[] = $taldea;
                        break;
                    case str_starts_with($taldea, 'TALDEA-'):
                        $ldapTaldeak[] = $taldea;
                        break;
                }
            }
        }

        return [
            'app' => $ldapApp,
            'rol' => $ldapRolak,
            'saila' => $ldapSailak,
            'sarbide' => $ldapSarbideak,
            'taldeak' => $ldapTaldeak,
        ];
    }

    // Oauth
    public function getLangileaByNA($NA): User
    {
        /** @var Entry $ldapUser */
        $ldapUser = $this->getLdapInfoByNA($NA);
        $hUser = $this->hydrateOne($ldapUser[0]);

        return $this->createDbUserFromLdapData($hUser->getUsername());
    }

    public function checkValidGiltzaPassport($NA): bool
    {
        /** @var Entry $ldapUser */
        $ldapUser = $this->getLdapInfoByNA($NA);
        $hUser = $this->hydrateOne($ldapUser[0]);
        $ldapTaldeak = $this->getLdapUserMembershipGroupsRecursivelyByUsername($hUser->getUsername());

        // Begiratu .env taldeetako batean dagoen
        $isAdmin = $this->checkIsInArray($ldapTaldeak, $this->ldapAdminTaldea);
        $isUser = $this->checkIsInArray($ldapTaldeak, $this->ldapUserTaldea);

        return $isAdmin || $isUser;
    }

    private function checkIsInArray($bilaketArray, $bilatuBeharrekoElementua): bool
    {
        $elements = explode(', ', $bilatuBeharrekoElementua);
        foreach ($bilaketArray as $subArray) {
            foreach ($elements as $element) {
                if (in_array($element, $subArray, true)) {
                    return true;
                }
            }
        }

        return false;
    }
}
