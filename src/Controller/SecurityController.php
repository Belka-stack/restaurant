<?php

namespace App\Controller;


use App\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// Les routes comportent des attributs permettant faire des test sur le Bundle Nelmio à l'url suivante : https://127.0.0.1:8000/api/doc afin d'améliorer la documentation.Un template Twig a été générer specifique via la commande : composer require twig asset
#[Route('/api', name: 'app_api_')]
final class SecurityController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager,private SerializerInterface $serializer)
    {

    }
    #[Route('/registration', name: 'registration', methods: ['POST'])]
    // Documantation API: Attributs à la route /registration
    #[OA\Post(
    path: '/api/registration',
    summary: "Inscription d'un nouvel utilisateur",
    tags: ['Security'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                new OA\Property(property: 'password', type: 'string', example: 'password123'),
                new OA\Property(property: 'firstName', type: 'string', example: 'Jean'),
                new OA\Property(property: 'lastName', type: 'string', example: 'Dupont'),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'Utilisateur créé'),
        new OA\Response(response: 400, description: 'Requête invalide'),
    ]
    )]

    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $user->setApiToken(bin2hex(random_bytes(32)));
        $user->setUuid(Uuid::v4());
        $user->setCreatedAt(new \dateTime());

        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse([
            'user' => $user->getUserIdentifier(), 'apiToken' => $user->getApiToken(), 'role' => $user->getRoles()
        ],Response::HTTP_CREATED
    );
    }

    #[Route('/login', name: 'login', methods: 'POST')]
    // Documantation API: Ajout d'attributs à la route /login
    #[OA\Post(
    path: '/api/login',
    summary: 'Connexion d\'un utilisateur',
    tags: ['Security'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', example: 'lisa@gmail.com'),
                new OA\Property(property: 'password', type: 'string', example: 'password'),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Authentification réussie'),
        new OA\Response(response: 401, description: 'Identifiants manquants ou incorrects'),
    ]
    )]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'Missing credentials'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'user'  => $user->getUserIdentifier(),
            'apiToken' => $user->getApiToken(),
            'roles' => $user->getRoles(),
        ]);
    }

    //Route /account/me : Cette route utilise l'attribut #[CurrentUser] pour récupérer l'utilisateur actuellement authentifié et retourne ses informations sous forme de réponse JSON.



    #[Route('/account/me', name: 'account_me', methods: ['GET'])]
    // Documantation API: Ajout d'attributs à la route /account/me
    #[OA\Get(
    path: '/api/account/me',
    summary: 'Retourne les informations de l\'utilisateur connecté',
    security: [ ['X-AUTH-TOKEN' => []] ], //security={{"Bearer":{}}} sur chaque méthode annotée OpenAPI (cela permet à Swagger/Nelmio d’exiger un token Bearer pour tester ces routes)
    tags: ['Security'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Informations de l\'utilisateur',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'firstName', type: 'string'),
                    new OA\Property(property: 'lastName', type: 'string'),
                    new OA\Property(property: 'guestNumber', type: 'integer'),
                    new OA\Property(property: 'allergy', type: 'string'),
                ]
            )
        ),
        new OA\Response(response: 404, description: 'Utilisateur non trouvé')
    ]
    )]

    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        $user = $this->getUser();

        if (null instanceof User) {
            return $this->json(['message' => 'User not found'], 404);
        }

        $data = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstname(),
            'lastName' => $user->getLastName(),
            'guestNumber' => $user->getGuestNumber(),
            'allergy' => $user->getAllergy(),
        ];
        return new JsonResponse($data);
    }

    // Route /account/edit : Cette route attend une requête PUT avec un corps JSON contenant les champs à mettre à jour. Elle met à jour les informations de l'utilisateur, y compris le mot de passe (qui est haché avant d'être stocké) et la date de mise à jour.


    #[Route('/account/edit', name: 'account_edit', methods: ['PUT'])]
    // Documantation API: Ajout d'attributs à la route /account/edit
    #[OA\Put(
    path: '/api/account/edit',
    summary: 'Met à jour les informations de l\'utilisateur connecté',
    security: [ ['X-AUTH-TOKEN' => []] ],
    tags: ['Security'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'firstName', type: 'string', example: 'Alice'),
                new OA\Property(property: 'lastName', type: 'string', example: 'Durand'),
                new OA\Property(property: 'guestNumber', type: 'integer', example: 2),
                new OA\Property(property: 'allergy', type: 'string', example: 'gluten'),
                new OA\Property(property: 'password', type: 'string', example: 'newpassword123'),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Utilisateur mis à jour'),
        new OA\Response(response: 400, description: 'Requête invalide'),
        new OA\Response(response: 401, description: 'Non authentifié')
    ]
    )]

    public function edit(Request $request, UserPasswordHasherInterface $passwordHasher, #[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'User not found'],
            Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['message' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['firstName'])){
            $user->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])){
            $user->setLastName($data['lastName']);
        }
        if (isset($data['guestNumber'])){
            $user->setGuestNumber($data['guestNumber']);
        }
        if (isset($data['allergy'])){
            $user->setAllergy($data['allergy']);
        }
        if (isset($data['password'])){
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }

        $user->setUpdatedAt(new \DateTime());
        $this->manager->flush();

        return new JsonResponse(['status' => 'User updated ']);
    }

}
