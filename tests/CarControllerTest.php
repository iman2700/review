<?php

namespace App\Tests;

use App\Controller\CarController;
use App\Entity\Car;
use App\Repository\CarRepository;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CarControllerTest extends TestCase
{
    private $mockCarRepository;
    private $mockValidator;
    private $carController;

    protected function setUp(): void
    {
        $this->mockCarRepository = $this->createMock(CarRepository::class);
        $this->mockValidator = $this->createMock(ValidatorInterface::class);
        $this->carController = new CarController($this->mockCarRepository, $this->mockValidator);
    }

    public function testIndexAction(): void
    {
        // Mock the CarRepository to return some data
        $expectedCars = [
            [
                'id' => 1,
                'brand' => 'bmw',
                'color' => 'blue',
                'model' => 'c1',
            ],
            [
                'id' => 2,
                'brand' => 'x22',
                'color' => 'red',
                'model' => 'c2',
            ],
        ];
        $this->mockCarRepository->expects($this->once())
            ->method('findAllEntities')
            ->willReturn($expectedCars);

        // Call the index action and assert the JSON response
        $response = $this->carController->index();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(json_encode($expectedCars), $response->getContent());
    }

    public function testGetCarByIdAction(): void
    {
        // Mock the CarRepository to return an existing car
        $carId = 1;
        $expectedCar = new Car();
        $expectedCar->setId($carId);
        $expectedCar->setBrand('Honda');
        $expectedCar->setColor('Blue');
        $expectedCar->setModel('Civic');
        $this->mockCarRepository->expects($this->once())
            ->method('findEntityById')
            ->with($carId)
            ->willReturn($expectedCar);

        // Call the getCarById action and assert the JSON response
        $response = $this->carController->getCarById($carId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(json_encode([
            'id' => $carId,
            'brand' => 'bmw',
            'color' => 'blue',
            'model' => 'c1',
        ]), $response->getContent());
    }

    public function testCreateAction(): void
    {
        // Mock a request object with dummy data
        $request = new Request([], [], [], [], [], [], json_encode([
            'brand' => 'bmw',
            'color' => 'blue',
            'model' => 'c1',
        ]));

        // Mock the validator to return an empty error collection
        $this->mockValidator->method('validate')->willReturn([]);

        // Mock the CarRepository to save the entity
        $this->mockCarRepository->expects($this->once())
            ->method('saveEntity');

        // Call the create action and assert the response
        $response = $this->carController->create($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('Car created successfully', $response->getContent());
    }
    public function testUpdateAction(): void
    {
        // Mock a request object with dummy data
        $request = new Request([], [], [], [], [], [], json_encode([
            'brand' => 'x22',
            'color' => 'red',
            'model' => 'c2',
        ]));

        // Mock the CarRepository to return an existing car
        $carId = 1;
        $existingCar = new Car();
        $existingCar->setId($carId);
        $existingCar->setBrand('bmw');
        $existingCar->setColor('blue');
        $existingCar->setModel('c1');
        $this->mockCarRepository->expects($this->once())
            ->method('findEntityById')
            ->with($carId)
            ->willReturn($existingCar);

        // Mock the validator to return an empty error collection
        $this->mockValidator->method('validate')->willReturn([]);

        // Mock the CarRepository to update the entity
        $this->mockCarRepository->expects($this->once())
            ->method('updateEntity')
            ->with($existingCar);

        // Call the update action and assert the response
        $response = $this->carController->update($request, $carId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('Car updated successfully', $response->getContent());

        // Assert that the car properties are updated
        $this->assertEquals('x22', $existingCar->getBrand());
        $this->assertEquals('red', $existingCar->getColor());
        $this->assertEquals('c2', $existingCar->getModel());
    }

    public function testDeleteAction(): void
    {
        // Mock the CarRepository to return an existing car
        $carId = 1;
        $existingCar = new Car();
        $existingCar->setId($carId);
        $existingCar->setBrand('bmw');
        $existingCar->setColor('blue');
        $existingCar->setModel('c1');
        $this->mockCarRepository->expects($this->once())
            ->method('findEntityById')
            ->with($carId)
            ->willReturn($existingCar);

        // Mock the CarRepository to delete the entity
        $this->mockCarRepository->expects($this->once())
            ->method('deleteEntity')
            ->with($existingCar);

        // Call the delete action and assert the response
        $response = $this->carController->delete($carId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('Entity deleted successfully', $response->getContent());
    }

}