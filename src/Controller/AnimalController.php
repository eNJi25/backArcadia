<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Entity\ImageAnimal;
use App\Entity\Race;
use App\Repository\AnimalRepository;
use App\Repository\HabitatRepository;
use App\Repository\ImageAnimalRepository;
use App\Repository\RaceRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/arcadia/api/animal', 'arcadia_api_animal_')]
class AnimalController extends AbstractController
{

    public function __construct(
        private AnimalRepository $animalRepo,
        private RaceRepository $raceRepo,
        private HabitatRepository $habitatRepo,
        private ImageAnimalRepository $imageRepo,
        private SerializerInterface $serializer
    ) {}

    #[Route('/new', name: 'new', methods: 'POST')]
    public function new(Request $request): JsonResponse
    {
        try {
            $animalData = $request->request->all();

            $file = $request->files->get('imageFile');
            $habitatData = $animalData['habitat'];
            $raceData = $animalData['race'];

            $race = $this->raceRepo->findOneBy(['nom' => $raceData]);

            if (!$race) {
                $race = new Race();
                $race->setNom($raceData);
                $race->setCreatedAt(new \DateTimeImmutable());
                $this->raceRepo->save($race, true);
            }

            $habitat = $this->habitatRepo->findOneBy(['nom' => $habitatData]);

            $animal = new Animal();
            $animal->setCreatedAt(new \DateTimeImmutable());
            $animal->setHabitat($habitat);
            $animal->setPrenom($animalData['prenom']);
            $animal->setRace($race);
            $this->animalRepo->save($animal, true);

            if ($file) {
                $image = new ImageAnimal();
                $image->setCreatedAt(new \DateTimeImmutable());
                $image->setAnimal($animal);
                $image->setImageFile($file);
                $this->imageRepo->save($image, true);
            }

            return new JsonResponse(["message" => "Animal crée avec succés"], Response::HTTP_CREATED);

        } catch(Exception $e) {
            return new JsonResponse(
                ['message' => 'Erreur lors de la création de l\'habitat : ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
