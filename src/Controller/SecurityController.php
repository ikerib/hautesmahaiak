<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
//    #[Route('/login', name: 'app_login')]
//    public function applogin(Request $request): RedirectResponse
//    {
//        $locale = $request->query->get('_locale');
//        if (!$locale) {
//            $locale = 'eu';
//        }
//
//        return $this->redirectToRoute('app_login_locale', ['_locale' => $locale]);
//    }

//    #[Route('/choose/{_locale}', name: 'app_login_locale')]
    #[Route('/login', name: 'app_login')]
    public function home(): Response
    {
        return $this->render('security/choose_login.html.twig', []);
    }

    #[Route('/login/giltza', name: 'app_giltza')]
    public function giltza(): RedirectResponse
    {
        return $this->redirectToRoute('oauth_connect');
    }

    #[Route(path: '/login/ldap', name: 'app_ldap')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/app/logout', name: 'app_logout')]
    public function logout(): void
    {
        // throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/login/giltza/connect', name: 'oauth_connect')]
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry->getClient('giltza')->redirect(['urn:izenpe:identity:global urn:izenpe:fea:properties urn:safelayer:eidas:authn_details']);
    }

    #[Route(path: '/login/giltza/connect/check', name: 'oauth_check')]
    public function check(Request $request, ClientRegistry $clientRegistry): void
    {
    }
}
