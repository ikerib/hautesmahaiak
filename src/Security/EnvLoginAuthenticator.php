<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * Login sinplea: erabiltzailea eta pasahitza .env-eko APP_LOGIN_USER / APP_LOGIN_PASSWORD dira.
 *
 * Datu baseko User entitatea mantentzen da (mailfrom/mailto jakinarazpenetarako); lehen loginean sortzen da.
 */
class EnvLoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EntityManagerInterface $em,
        #[Autowire('%env(APP_LOGIN_USER)%')]
        private readonly string $loginUser,
        #[Autowire('%env(APP_LOGIN_PASSWORD)%')]
        private readonly string $loginPassword,
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $username = (string) $request->request->get('_username', '');
        $password = (string) $request->request->get('_password', '');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);

        // Biak beti konparatu (denbora konstantean), erabiltzailea asmatzeko pistarik ez emateko
        $userOk = hash_equals($this->loginUser, $username);
        $passwordOk = hash_equals($this->loginPassword, $password);
        if ('' === $this->loginUser || '' === $this->loginPassword || !$userOk || !$passwordOk) {
            throw new CustomUserMessageAuthenticationException('Erabiltzailea edo pasahitza ez da zuzena.');
        }

        return new SelfValidatingPassport(
            new UserBadge($username, fn (string $identifier) => $this->getOrCreateUser($identifier)),
            [new CsrfTokenBadge('authenticate', (string) $request->request->get('_csrf_token'))]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_hauteskundea_index'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    private function getOrCreateUser(string $username): User
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$user instanceof User) {
            $user = new User();
            $user->setUsername($username);
            $user->setDisplayname($username);
        }

        $user->setRoles([User::ROLE_ADMIN]);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
