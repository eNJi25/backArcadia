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
        } catch (Exception $e) {
            return new JsonResponse(
                ['message' => 'Erreur lors de la création de l\'animal : ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/edit/{id}', name: 'edit', methods: 'POST')]
    public function edit(Request $request, int $id): JsonResponse
    {
        try {
            $animalData = $request->request->all();
            $animal = $this->animalRepo->findOneBy(['id' => $id]);
            $file = $request->files->get('imageFile');

            if ($file) {
                $image = $this->imageRepo->findOneBy(['animal' => $id]);
                $image->setImageFile($file);
                $this->imageRepo->save($image, true);
            }

            if (isset($animalData['habitat'])) {
                $animal->setHabitat($animalData['habitat']);
            }

            if (isset($animalData['prenom'])) {
                $animal->setPrenom($animalData['prenom']);
            }

            if (isset($animalData['race'])) {
                $race = $this->raceRepo->findOneBy(['nom' => $animalData['race']]);
                if (!$race) {
                    $race = new Race();
                    $race->setNom($animalData['race']);
                    $race->setCreatedAt(new \DateTimeImmutable());
                    $this->raceRepo->save($race, true);
                }
            }

            $animal->setUpdatedAt(new \DateTimeImmutable());

            $this->animalRepo->save($animal, true);

            return new JsonResponse(
                ["message" => "Animal modifié avec succès"],
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return new JsonResponse(
                ['message' => 'Erreur lors de la modification de l\'animal : ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/delete/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): JsonResponse
    {
        $animal = $this->animalRepo->findOneBy(['id' => $id]);
        if ($animal) {
            $this->animalRepo->remove($animal, true);

            return new JsonResponse(["message" => "Animal supprimé avec succès"], Response::HTTP_OK);
        }

        return new JsonResponse(["message" => "Aucun animal trouvé"], Response::HTTP_NOT_FOUND);
    }

    #[Route('/show/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): JsonResponse
    {
        $animal = $this->animalRepo->findOneBy(['id' => $id]);
        $image = $this->imageRepo->findOneBy(['animal' => $id]);

        if ($animal && $image) {
            $responseData = [
                'id' => $animal->getId(),
                'prenom' => $animal->getPrenom(),
                'race' => $animal->getRace() ? $animal->getRace()->getNom() : null,
                'image' => $image->getImageName(),
                'nourritureDernierRepas' => $animal->getNourritureDernierRepas() ? $animal->getNourritureDernierRepas() : null,
                'quantiteDernierRepas' => $animal->getNourritureDernierRepas() ? $animal->getQuantiteDernierRepas() : null,
                'dateDernierRepas' => $animal->getNourritureDernierRepas() ? $animal->getDateDernierRepas() : null,
            ];
            return new JsonResponse($responseData, Response::HTTP_OK);
        }


        return new JsonResponse(["message" => "Aucun animal trouvé"], Response::HTTP_NOT_FOUND);
    }

    #[Route(path: '/lastMeal/{id}', name: 'last_meal', methods: 'POST')]
    public function lastMeal(Request $request, int $id)
    {
        if ($request) {
            $animalData = $request->request->all();
            $animal = $this->animalRepo->findOneBy(['id' => $id]);

            $animal->setNourritureDernierRepas($animalData['nourriture_dernier_repas']);
            $animal->setQuantiteDernierRepas($animalData['quantite_dernier_repas']);
            $animal->setDateDernierRepas(new \DateTimeImmutable());
            $animal->setUpdatedAt(new \DateTimeImmutable());

            $this->animalRepo->save($animal, true);

            return new JsonResponse(
                ["message" => "Dernier repas de l'animal enregisré"],
                Response::HTTP_CREATED
            );
        }

        return new JsonResponse(
            ["message" => "Erreur lors de l'envoi du dernier repas de l'animal"],
            Response::HTTP_NOT_FOUND
        );
    }

    #[Route('/showlastAnimals/{habitatId}', name: 'show_lastAnimal_byHabitat', methods: 'GET')]
    public function showLastAnimalsByHabitat(int $habitatId): JsonResponse
    {
        $habitat = $this->habitatRepo->findOneBy(['id' => $habitatId]);

        if (!$habitat) {
            return new JsonResponse(['message' => 'Habitat non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $animals = $this->animalRepo->findBy(
            ['habitat' => $habitat],
            ['id' => 'DESC'],
            4
        );

        $data = [];
        foreach ($animals as $animal) {
            $image = $this->imageRepo->findOneBy(['animal' => $animal->getId()]);
            $data[] = [
                'prenom' => $animal->getPrenom(),
                'imageSlug' => $image->getImageName()
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/showAllAnimals/{habitatId}', name: 'show_AllAnimal_byHabitat', methods: 'GET')]
    public function showAllAnimalsByHabitat(int $habitatId): JsonResponse
    {
        $habitat = $this->habitatRepo->findOneBy(['id' => $habitatId]);

        if (!$habitat) {
            return new JsonResponse(['message' => 'Habitat non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $animals = $this->animalRepo->findBy(
            ['habitat' => $habitat]
        );

        $data = [];
        foreach ($animals as $animal) {
            $image = $this->imageRepo->findOneBy(['animal' => $animal->getId()]);
            $data[] = [
                'id' => $animal->getId(),
                'prenom' => $animal->getPrenom(),
                'imageSlug' => $image->getImageName()
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/showAnimalsHome', name: 'show_animals_home', methods: 'GET')]
    public function showAllAnimals(): JsonResponse
    {
        $habitats = $this->habitatRepo->findAll();
        $animalsData = [];

        foreach ($habitats as $habitat) {
            $animal = $this->animalRepo->findOneBy(['habitat' => $habitat]);
            $image = $this->imageRepo->findOneBy(['animal' => $animal]);
            if ($animal) {

                $data = [
                    'animal' => [
                        'id' => $animal->getId(),
                        'prenom' => $animal->getPrenom(),
                        'imageSlug' => $image->getImageName()
                    ]
                ];

                $animalsData[] = $data;
            }
        }
        if (empty($animalsData)) {
            return new JsonResponse(['message' => 'Aucun animal trouvé pour les habitats'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($animalsData, Response::HTTP_OK);
    }
}
