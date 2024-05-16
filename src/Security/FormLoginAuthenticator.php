<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class FormLoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_ldap';
    private PasaiaLdapService $pasaiaLdapService;
    private UserRepository $langileaRepository;
    private PasaiaRoleService $pasaiaRoleService;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        PasaiaLdapService $pasaiaLdapService,
        UserRepository $langileaRepository,
        PasaiaRoleService $pasaiaRoleService,
    ) {
        $this->pasaiaLdapService = $pasaiaLdapService;
        $this->langileaRepository = $langileaRepository;
        $this->pasaiaRoleService = $pasaiaRoleService;
    }

    public function supports(Request $request): bool
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username', '');
        $password = $request->request->get('password');
        $username = $request->request->get('username');
        $csrfToken = $request->request->get('_csrf_token');

        $request->getSession()->set(Security::LAST_USERNAME, $username);
        $result = $this->pasaiaLdapService->checkCredentials($username, $password);

        if ($result) {
            $dbLangilea = $this->langileaRepository->findOneBy(['username' => $username]);
            if (!$dbLangilea) {
                //                throw new CustomUserMessageAuthenticationException('Ez dago erabiltzailerik datu horiekin.');
                $this->pasaiaLdapService->createDbUserFromLdapData($username);
            } else {
                // eguneratu datuak ldap-etik
                $this->pasaiaLdapService->updateDbUserDataFromLdapByUsername($username);
            }

            return new SelfValidatingPassport(new UserBadge($username, function ($userIdentifier) {
                $langilea = $this->langileaRepository->findOneBy(['username' => $userIdentifier]);
                if (!$langilea) {
                    throw new UserNotFoundException();
                }
                $langilea->setRoles($this->pasaiaRoleService->getRoles($langilea->getNA()));

                return $langilea;
            }));
        }
        throw new CustomUserMessageAuthenticationException('Ez dago erabiltzailerik datu horiekin.');
        // LDAP-ekoa ez denez, begiratu aplikazioan sortutako erabiltzailea den
        //        return new Passport(
        //            new UserBadge($username),
        //            new PasswordCredentials($password),
        //            [
        //                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
        //                new RememberMeBadge()
        //            ],
        //
        //        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /** @var User $user */
        $user = $token->getUser();
        $userid = $user->getId();
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        if (in_array('ROLE_ADMIN', $token->getRoleNames())) {
            return new RedirectResponse($this->urlGenerator->generate('app_hauteskundea_index'));
        }

        return new RedirectResponse($this->urlGenerator->generate('app_hauteskundea_index'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
