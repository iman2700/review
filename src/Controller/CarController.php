<?php

namespace App\Controller;

use App\Entity\Car;
use App\Repository\CarRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Car API",
 *     version="1.0.0"
 * )
 */

class CarController extends BaseApiController
{

    private $carRepository;
    private $validator;

    public function __construct(CarRepository $carRepository, ValidatorInterface $validator)
    {
        $this->carRepository = $carRepository;
        $this->validator = $validator;


    }
    /**
     * @OA\Get(
     *     path="/api/cars",
     *     summary="Get a list of cars",
     *     tags={"Cars"},
     *     @OA\Response(response="200", description="Successful operation"),
     * )
     */
    #[Route('/', name: 'car_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $cars = $this->carRepository->findAllEntities();
        $carData = [];
        foreach ($cars as $car) {
            $carData[] = [
                'id' => $car->getId(),
                'brand' => $car->getBrand(),
                'color' => $car->getColor(),
                'model' => $car->getModel(),
            ];
        }

        return $this->json($carData);
    }

    /**
     * @OA\Get(
     *     path="/api/cars/{id}",
     *     summary="Get a car by ID",
     *     tags={"Cars"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the car",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Successful operation"),
     *     @OA\Response(response="404", description="Car not found")
     * )
     */
    #[Route('/car/{id}', name: 'getCarById', methods: ['GET'])]
    public function getCarById(int $id): Response
    {
        $car = $this->carRepository->findEntityById($id);
        if (!$car) {
            return $this->notFoundMessage('Car not found');
        }
        $carData = [
            'id' => $car->getId(),
            'brand' => $car->getBrand(),
            'color' => $car->getColor(),
            'model' => $car->getModel(),
        ];

        return $this->json($carData);
    }
    /**
     * @OA\Post(
     *     path="/api/cars",
     *     summary="Create a new car",
     *     tags={"Cars"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CarInput")
     *     ),
     *     @OA\Response(response="200", description="Car created successfully"),
     *     @OA\Response(response="400", description="Invalid input data")
     * )
     */
    #[Route('/entities', name: 'car_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $requestData = json_decode($request->getContent(), true);
        // Set properties from the request
        $car = new Car();
        $car->setBrand($requestData['brand']);
        $car->setColor($requestData['color']);
        $car->setModel($requestData['model']);

        // Validate the entity
        $errors = $this->validator->validate($car);

        if (count($errors) > 0) {
            return $this->failedMessage($errors);
        }

        // Persist and flush to DB
        $this->carRepository->saveEntity($car);

        return $this->successMessage('Car created successfully');
    }
    /**
     * @OA\Put(
     *     path="/api/cars/{id}",
     *     summary="Update a car",
     *     tags={"Cars"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the car",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CarInput")
     *     ),
     *     @OA\Response(response="200", description="Car updated successfully"),
     *     @OA\Response(response="400", description="Invalid input data"),
     *     @OA\Response(response="404", description="Car not found")
     * )
     */
    #[Route('/entities/{id}', name: 'car_update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        // Find the entity to update
        $car = $requestData->findEntityById($id);

        if (!$car) {
            return $this->notFoundMessage('Car not found');
        }

        $requestData = json_decode($request->getContent(), true);

        $car->setBrand($requestData['brand'] ?? $car->getBrand());
        $car->setColor($requestData['color'] ?? $car->getColor());
        $car->setModel($requestData['model'] ?? $car->getModel());
        // Validate the entity
        $errors = $this->validator->validate($car);

        if (count($errors) > 0) {
            return $this->handleValidationErrors($errors);
        }
        // Save the updated entity
        $this->carRepository->updateEntity($car);
        return $this->successMessage('Car updated successfully');

    }
    /**
     * @OA\Delete(
     *     path="/api/cars/{id}",
     *     summary="Delete a car",
     *     tags={"Cars"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the car",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Car deleted successfully"),
     *     @OA\Response(response="404", description="Car not found")
     * )
     */
    #[Route('/entities/{id}', name: 'car_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        // Find the entity to delete
        $entity = $this->carRepository->findEntityById($id);

        if (!$entity) {
            return $this->notFoundMessage('Entity not found');
        }

        // Delete the entity
        $this->carRepository->deleteEntity($entity);

        return $this->successMessage('Entity deleted successfully');
    }

}
