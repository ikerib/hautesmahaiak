<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class OauthAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_giltza';
    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $entityManager;
    private RouterInterface $router;
    private PasaiaRoleService $pasaiaRoleService;
    private PasaiaLdapService $pasaiaLdapService;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        ClientRegistry $clientRegistry,
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        PasaiaRoleService $pasaiaRoleService,
        PasaiaLdapService $pasaiaLdapService
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->pasaiaRoleService = $pasaiaRoleService;
        $this->pasaiaLdapService = $pasaiaLdapService;
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return 'oauth_check' === $request->attributes->get('_route');
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('giltza');
        $accessToken = $this->fetchAccessToken($client);

        // begiratu baimendutako ldap taldean dagoen
        $user = $client->fetchUserFromToken($accessToken);

        $checkValidGiltzaPassport = $this->pasaiaLdapService->checkValidGiltzaPassport($user->getId());

        if (false === $checkValidGiltzaPassport) {
            throw new CustomUserMessageAuthenticationException('Erabiltzaileak ez du baimenik');
        }

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                $user = $client->fetchUserFromToken($accessToken);
                // 1) have they logged in with Facebook before? Easy!
                $langilea = $this->entityManager->getRepository(User::class)->findOneBy(['NA' => $user->getId()]);

                if ($langilea) {
                    $langilea->setRoles($this->pasaiaRoleService->getRoles($langilea->getNA()));
                    $this->entityManager->persist($langilea);
                    $this->entityManager->flush();

                    return $langilea;
                }

                return $this->pasaiaLdapService->getLangileaByNA($user->getId());

                //                throw new UserNotFoundException();
            })
        );
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
            return new RedirectResponse($this->urlGenerator->generate('app_default'));
        }

        return new RedirectResponse($this->urlGenerator->generate('app_default'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    public function start(Request $request, ?AuthenticationException $authException = null): RedirectResponse
    {
        return new RedirectResponse(
            '/login/', // might be the site, where users choose their oauth provider
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
