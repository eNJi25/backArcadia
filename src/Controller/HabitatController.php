<?php

namespace App\Controller;

use App\Entity\Habitat;
use App\Repository\HabitatRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/arcadia/api/habitat', 'arcadia_api_habitat_')]
class HabitatController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private HabitatRepository $repo
    ) {}

    #[Route('/new', name: 'new', methods: 'POST')]
    public function new(Request $request): JsonResponse
    {
        try {
            $data = $request->request->all();

            $file = $request->files->get('imageFile');

            $habitat = $this->serializer->deserialize(json_encode($data), Habitat::class, 'json');

            $habitat->setCreatedAt(new DateTimeImmutable());

            if ($file) {
                $habitat->setImageFile($file);
            }

            $this->repo->save($habitat, true);

            return new JsonResponse(
                ['message' => 'Habitat créé avec succès'],
                Response::HTTP_CREATED,
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['message' => 'Erreur lors de la création de l\'habitat : ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route(path: '/showAll', name: 'showAll', methods: 'GET')]
    public function showAll(): JsonResponse
    {
        $habitats = $this->repo->findAll();
        $responseData = $this->serializer->serialize($habitats, 'json');

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route(path: '/show/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): JsonResponse
    {

        $habitat = $this->repo->findOneBy(['id' => $id]);

        if ($habitat) {
            $responseData = $this->serializer->serialize($habitat, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(["message" => "Aucun habitat"], Response::HTTP_NOT_FOUND);
    }



    #[Route(path: '/edit/{id}', name: 'edit', methods: ['POST'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        $habitat = $this->repo->findOneBy(['id' => $id]);

        if (!$habitat) {
            return new JsonResponse(['error' => 'Habitat non trouvé'], 404);
        }

        $habitatData = $request->request->all();

        if (isset($habitatData['nom'])) {
            $habitat->setNom($habitatData['nom']);
        }

        if (isset($habitatData['description'])) {
            $habitat->setDescription($habitatData['description']);
        }

        $habitat->setUpdatedAt(new DateTimeImmutable());

        $file = $request->files->get('imageFile');

        if ($file) {
            $habitat->setImageFile($file);
        }

        $this->repo->save($habitat, true);

        return new JsonResponse(
            ['message' => 'Habitat modifié avec succès',],
            Response::HTTP_CREATED,
        );
    }

    #[Route(path: '/delete/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): JsonResponse
    {
        $habitat = $this->repo->findOneBy(['id' => $id]);

        if ($habitat) {
            $this->repo->remove($habitat, true);
            return new JsonResponse(
                [
                    'message' => 'Habitat supprimé avec succès'
                ],
                Response::HTTP_OK
            );
        }
        return new JsonResponse(
            [
                'message' => "L'habitat' n'a pas été supprimé"
            ],
            404
        );
    }
}
