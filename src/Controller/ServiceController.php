<?php

namespace App\Controller;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/arcadia/api/service', name: "arcadia_api_service_")]
class ServiceController extends AbstractController
{

    public function __construct(
        private ServiceRepository $repo,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    #[Route('/new', name: 'new')]
    public function new(Request $request): JsonResponse
    {
        $serviceData = $request->request->all();
        $file = $request->files->get('imageFile');

        $service = $this->serializer->deserialize(json_encode($serviceData), Service::class, 'json');

        if ($file) {
            $service->setImageFile($file);
        }

        $service->setCreatedAt(new DateTimeImmutable());

        $this->repo->save($service, true);

        return new JsonResponse(
            [
                'message' => 'Service créé avec succès',
                'id' => $service->getId()
            ],
            Response::HTTP_CREATED,
        );
    }

    #[Route(path: '/showAll', name: 'showAll', methods: 'GET')]
    public function show(): JsonResponse
    {
        $services = $this->repo->findAll();
        $responseData = $this->serializer->serialize($services, 'json');

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route(path: '/edit/{id}', name: 'edit', methods: ['POST'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        $service = $this->repo->findOneBy(['id' => $id]);

        if (!$service) {
            return new JsonResponse(['error' => 'Service non trouvé'], 404);
        }

        $serviceData = $request->request->all();

        if (isset($serviceData['nom'])) {
            $service->setNom($serviceData['nom']);
        }

        if (isset($serviceData['description'])) {
            $service->setDescription($serviceData['description']);
        }

        $service->setUpdatedAt(new DateTimeImmutable());

        $file = $request->files->get('imageFile');

        if ($file) {
            $service->setImageFile($file);
        }

        $this->repo->save($service, true);

        return new JsonResponse(
            [
                'message' => 'Service modifié avec succès',
                'id' => $service->getId()
            ],
            Response::HTTP_CREATED,
        );
    }


    #[Route(path: '/delete/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): JsonResponse
    {
        $service = $this->repo->findOneBy(['id' => $id]);

        if ($service) {
            $this->repo->remove($service, true);
            return new JsonResponse(
                [
                'message' => 'Service supprimé avec succès'
            ],
            Response::HTTP_OK);
        }
        return new JsonResponse(
            [
                'message' => "Le service n'a pas été supprimé"
            ],
            404);
    }
}
