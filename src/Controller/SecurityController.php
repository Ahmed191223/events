<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
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

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName)
    {
        // Check if there is a target path stored in the session for redirection
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Get the authenticated user from the token
        $user = $token->getUser();

        // Check if the user has the ROLE_CLIENT role
        if (in_array("ROLE_CLIENT", $user->getRoles(), true)) {
            return new RedirectResponse($this->urlGenerator->generate("app_voiture"));
        }

        // Check if the user has the ROLE_AGENT role
        if (in_array("ROLE_AGENT", $user->getRoles(), true)) {
            return new RedirectResponse($this->urlGenerator->generate("app_client_index"));
        }

        // Default redirection if no matching role
        return new RedirectResponse($this->urlGenerator->generate("app_location_new"));
    }

}
