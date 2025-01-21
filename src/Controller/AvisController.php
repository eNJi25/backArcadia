<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Repository\AvisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/arcadia/api/avis', name: 'arcadia_api_avis_', methods: 'POST')]
class AvisController extends AbstractController
{
    public function __construct(
        private AvisRepository $repo,
        private SerializerInterface $serializer,
    ) {}

    #[Route('/new', name: 'new', methods:'POST')]
    public function new(Request $request): JsonResponse
    {
        if ($request) {
            $avis = $this->serializer->deserialize($request->getContent(), Avis::class, 'json');
            $avis->setCreatedAt(new \DateTimeImmutable());

            $this->repo->save($avis, true);

            return new JsonResponse(
                ["message" => "Avis bien envoyé"],
                Response::HTTP_CREATED
            );
        }

        return new JsonResponse(
            ["message" => "Erreur lors de l'envoi de votre avis"],
            Response::HTTP_NOT_FOUND
        );
    }

    #[Route('/toValidate', name: 'toValidate', methods: 'GET')]
    public function listAValider(): JsonResponse
    {
        $avis = $this->repo->findBy(['isVisible' => false]);

        if ($avis) {
            $responseData = $this->serializer->serialize($avis, 'json');
            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(["message" => "Aucun avis à valider"], Response::HTTP_OK);
    }

    #[Route('/valides', name: 'valides', methods: 'GET')]
    public function getValidAvis(): JsonResponse
    {
        $validAvis = $this->repo->findBy(
            ['isVisible' => true],
            ['createdAt' => 'DESC'],
            3
        );

        if ($validAvis) {
            $responseData = $this->serializer->serialize($validAvis, 'json');
            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(["message" => "Aucun avis"], Response::HTTP_OK);
    }


    #[Route('/accept/{id}', name: 'accept', methods: 'PUT')]
    public function accept(int $id, Request $request): JsonResponse
    {

        $avis = $this->repo->find($id);

        if (!$avis) {
            return new JsonResponse(['error' => 'Avis non trouvé.'], 404);
        }

        $avis->setIsVisible(true);
        $avis->setUpdatedAt(new \DateTimeImmutable());

        $this->repo->save($avis, true);


        return new JsonResponse(['message' => 'Avis accepté et visible maintenant.'], 200);
    }

    #[Route('/delete/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): JsonResponse
    {
        $avis = $this->repo->findOneBy(['id' => $id]);
        if ($avis) {
            $this->repo->remove($avis, true);

            return new JsonResponse(["message" => "Avis supprimé avec succès"], Response::HTTP_OK);
        }

        return new JsonResponse(["message" => "Aucun avis a supprimer"], Response::HTTP_NOT_FOUND);
    }
}
