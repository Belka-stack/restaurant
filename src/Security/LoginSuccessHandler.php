<?php
namespace App\Security;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        /** @var User $user */
        $user = $token->getUser();

        return new JsonResponse([
            'user'     => $user->getUserIdentifier(),
            'apiToken' => $user->getApiToken(),
            'roles'    => $user->getRoles(),
            'message'  => 'Authentification réussie !',
        ]);
    }
}