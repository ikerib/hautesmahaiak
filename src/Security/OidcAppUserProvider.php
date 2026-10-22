<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Drenso\OidcBundle\Model\OidcTokens;
use Drenso\OidcBundle\Model\OidcUserData;
use Drenso\OidcBundle\Security\UserProvider\OidcUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * SSO Municipal-eko erabiltzailea datu baseko User entitatearekin lotzen du.
 *
 * Login bakoitzean (ensureUserExists) User-a sortu edo eguneratzen da SSO-ko claim-ekin,
 * rolak barne. mailfrom/mailto bezalako eremu lokalak ez dira ukitzen.
 *
 * @implements OidcUserProviderInterface<User>
 */
final class OidcAppUserProvider implements OidcUserProviderInterface
{
    /** @var array<string, User> ensureUserExists() eta loadOidcUser() arteko zubia */
    private array $pendingUsers = [];

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function ensureUserExists(string $userIdentifier, OidcUserData $userData, OidcTokens $tokens): void
    {
        $this->pendingUsers[$userIdentifier] = $this->upsertUser($userIdentifier, $userData);
    }

    public function loadOidcUser(string $userIdentifier): UserInterface
    {
        $user = $this->pendingUsers[$userIdentifier] ?? null;
        unset($this->pendingUsers[$userIdentifier]);

        if (!$user instanceof User) {
            throw new UserNotFoundException(sprintf('OIDC user "%s" could not be loaded after authentication.', $userIdentifier));
        }

        return $user;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => $identifier]);
        if (!$user instanceof User) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Expected %s, got %s.', User::class, $user::class));
        }

        $refreshed = $this->em->getRepository(User::class)->find($user->getId());
        if (!$refreshed instanceof User) {
            throw new UserNotFoundException(sprintf('User id=%d no longer exists.', (int) $user->getId()));
        }

        return $refreshed;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    private function upsertUser(string $username, OidcUserData $userData): User
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$user instanceof User) {
            $user = new User();
            $user->setUsername($username);
        }

        $email = $userData->getUserDataString('email');
        $user->setEmail('' !== $email ? $email : null);

        $name = $userData->getUserDataString('name');
        $user->setDisplayname('' !== $name ? $name : $username);

        $employeeId = $userData->getUserDataString('employee_id');
        if ('' !== $employeeId) {
            $user->setNA($employeeId);
        }

        $description = $userData->getUserDataString('description');
        if ('' !== $description) {
            $user->setLanpostua($description);
        }

        $department = $userData->getUserDataString('department');
        if ('' !== $department) {
            $user->setSaila($department);
        }

        $roles = array_values(array_filter($userData->getUserDataArray('roles'), 'is_string'));
        $user->setRoles($roles);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
