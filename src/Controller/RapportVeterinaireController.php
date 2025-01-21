<?php

namespace App\Controller;

use App\Entity\RapportVeterinaire;
use App\Repository\AnimalRepository;
use App\Repository\RapportVeterinaireRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/arcadia/api/rapport', 'arcadia_api_rapport_')]
class RapportVeterinaireController extends AbstractController{

    public function __construct(
        private RapportVeterinaireRepository $rapportRepo,
        private AnimalRepository $animalRepo,
        private UserRepository $userRepo,
        private SerializerInterface $serializer
    ) {}

    #[Route('/new', name: 'new', methods: 'POST')]
    public function new(Request $request): JsonResponse
    {
        $data = $request->request->all();
        $animalId = $data['animal'];
        $etatAnimal = $data['etat_animal'];
        $nourriturePropose = $data['nourriture_propose'] ?? null;
        $quantitePropose = $data['quantite_propose'] ?? null;
        $detailHabitat = $data['detail_habitat'] ?? null;

        $rapport = new RapportVeterinaire();
        $rapport->setCreatedAt(new \DateTimeImmutable());

        $user = $this->userRepo->find($data['user']);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_BAD_REQUEST);
        }
        $rapport->setUser($user);

        $animal = $this->animalRepo->find($animalId);
        if (!$animal) {
            return new JsonResponse(['error' => 'Animal not found'], Response::HTTP_BAD_REQUEST);
        }
        $rapport->setAnimal($animal);

        if($etatAnimal){
            $rapport->setEtatAnimal($etatAnimal);
        }

        if ($nourriturePropose) {
            $rapport->setNourriturePropose($nourriturePropose);
        }

        if ($quantitePropose) {
            $rapport->setGrammagePropose($quantitePropose);
        }

        if ($detailHabitat) {
            $rapport->setDetailAnimal($detailHabitat);
        }

        $this->rapportRepo->save($rapport, true);

        return new JsonResponse(["message" => "Rapport enregistré avec succès"],Response::HTTP_CREATED);
    }

    #[Route('/show', name: 'show', methods: 'GET')]
    public function show(): JsonResponse
    {
        $rapports = $this->rapportRepo->findAll();
        $data = [];
        foreach ($rapports as $rapport) {
            $data[] = [
                'id' => $rapport->getId(),
                'createdAt' => $rapport->getCreatedAt(),
                'detailHabitat' => $rapport->getDetailAnimal(),
                'nourriturePropose' => $rapport->getNourriturePropose(),
                'quantitePropose' => $rapport->getGrammagePropose(),
                'etatAnimal' => $rapport->getEtatAnimal(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/showlastRapports', name: 'show_lastRapports', methods: 'GET')]
    public function showLastRapports(): JsonResponse
    {
        $rapports = $this->rapportRepo->findby(
            [],
            ['createdAt' => 'DESC'],
            4
        );

        $data = [];
        foreach ($rapports as $rapport) {
            $data[] = [
                'id' => $rapport->getId(),
                'createdAt' => $rapport->getCreatedAt()->format('Y-m-d H:i:s'),
                'detailHabitat' => $rapport->getDetailAnimal(),
                'nourriturePropose' => $rapport->getNourriturePropose(),
                'quantitePropose' => $rapport->getGrammagePropose(),
                'etatAnimal' => $rapport->getEtatAnimal(),
            ];
        }


        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/last/{animalId}', name: 'last_by_animal', methods: 'GET')]
    public function getLastReportByAnimal(int $animalId): JsonResponse
    {

        $animal = $this->animalRepo->find($animalId);
        if (!$animal) {
            return new JsonResponse(['error' => 'Animal non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $lastRapport = $this->rapportRepo->findOneBy(
            ['animal' => $animal],
            ['createdAt' => 'DESC']
        );

        if (!$lastRapport) {
            return new JsonResponse(['erreur' => 'Aucun rapport pour cet animal'], Response::HTTP_NOT_FOUND);
        }

        $responseData = [
            'id' => $lastRapport->getId(),
            'createdAt' => $lastRapport->getCreatedAt(),
            'etatAnimal' => $lastRapport->getEtatAnimal(),
            'nourriturePropose' => $lastRapport->getNourriturePropose(),
            'grammagePropose' => $lastRapport->getGrammagePropose(),
            'detailAnimal' => $lastRapport->getDetailAnimal(),
            'user' => [
                'id' => $lastRapport->getUser()->getId(),
                'name' => $lastRapport->getUser()->getNom(),
            ],
            'animal' => [
                'id' => $lastRapport->getAnimal()->getId(),
                'name' => $lastRapport->getAnimal()->getPrenom(),
            ],
        ];

        return new JsonResponse($responseData, Response::HTTP_OK);
    }
}
