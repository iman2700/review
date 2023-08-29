<?php

namespace App\Tests;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Controller\ReviewController;
use App\Entity\Review;
use App\Repository\ReviewRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ReviewControllerTest extends TestCase
{
    private $mockReviewRepository;
    private $mockValidator;
    private $reviewController;

    protected function setUp(): void
    {
        $this->mockReviewRepository = $this->createMock(ReviewRepository::class);
        $this->mockValidator = $this->createMock(ValidatorInterface::class);
        $this->reviewController = new ReviewController($this->mockReviewRepository, $this->mockValidator);
    }

    public function testIndexAction(): void
    {
        // Mock the ReviewRepository to return some data
        $expectedReviews = [
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
        $this->mockReviewRepository->expects($this->once())
            ->method('findAllEntities')
            ->willReturn($expectedReviews);

        // Call the index action and assert the JSON response
        $response = $this->reviewController->index();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(json_encode($expectedReviews), $response->getContent());
    }

    public function testCreateAction(): void
    {
        // Mock a request object with dummy data
        $request = new Request([], [], [], [], [], [], json_encode([
            'starRating' => 4.5,
            'reviewText' => 'best car!',
        ]));

        // Mock the validator to return an empty error collection
        $this->mockValidator->method('validate')->willReturn([]);

        // Mock the ReviewRepository to save the entity
        $this->mockReviewRepository->expects($this->once())
            ->method('saveEntity');

        // Call the create action and assert the response
        $response = $this->reviewController->create($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('Car created successfully', $response->getContent());
    }

    public function testUpdateAction(): void
    {
        // Mock a request object with dummy data
        $request = new Request([], [], [], [], [], [], json_encode([
            'star_rating' => 4,
            'review_text' => 'Updated review',
        ]));

        // Mock the ReviewRepository to return an existing review
        $reviewId = 1;
        $expectedReview = new Review();
        $expectedReview->setId($reviewId);
        $this->mockReviewRepository->expects($this->once())
            ->method('findEntityById')
            ->with($reviewId)
            ->willReturn($expectedReview);

        // Mock the validator to return an empty error collection
        $this->mockValidator->method('validate')->willReturn([]);

        // Mock the ReviewRepository to update the entity
        $this->mockReviewRepository->expects($this->once())
            ->method('updateEntity');

        // Call the update action and assert the response
        $response = $this->reviewController->update($request, $reviewId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('Review updated successfully', $response->getContent());
    }

    public function testDeleteAction(): void
    {
        // Mock the ReviewRepository to return an existing review
        $reviewId = 1;
        $expectedReview = new Review();
        $expectedReview->setId($reviewId);
        $this->mockReviewRepository->expects($this->once())
            ->method('findEntityById')
            ->with($reviewId)
            ->willReturn($expectedReview);

        // Mock the ReviewRepository to delete the entity
        $this->mockReviewRepository->expects($this->once())
            ->method('deleteEntity');

        // Call the delete action and assert the response
        $response = $this->reviewController->delete($reviewId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('Review deleted successfully', $response->getContent());
    }

    public function testGetReviewByIdAction(): void
    {
        // Mock the ReviewRepository to return an existing review
        $reviewId = 1;
        $expectedReview = new Review();
        $expectedReview->setId($reviewId);
        $expectedReview->setStarRating(4.5);
        $expectedReview->setReviewText('good');

        $this->mockReviewRepository->expects($this->once())
            ->method('findEntityById')
            ->with($reviewId)
            ->willReturn($expectedReview);

        // Call the getReviewById action and assert the JSON response
        $response = $this->reviewController->getReviewById($reviewId);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(json_encode([
            'id' => $reviewId,
            'rating' => 4.5,
            'review' => 'best',
            'car' => $expectedReview->getCar(),
        ]), $response->getContent());
    }


    public function testHighRatingReview(): void
    {
        // Mock the ReviewRepository to return sample data
        $this->mockReviewRepository->expects($this->once())
            ->method('findHighRatingReviews')
            ->with(1) // Provide the ID for testing
            ->willReturn([
                (object) ['id' => 1, 'starRating' => 5, 'reviewText' => 'good', 'model' => 'bmw'],
                (object) ['id' => 2, 'starRating' => 4, 'reviewText' => 'best', 'model' => 'x22'],
            ]);

        // Call the highRatingReview method
        $request = new Request([], [], [], [], [], [], null);
        $request->attributes->set('id', 1); // Set the ID for testing
        $response = $this->reviewController->highRatingReview($request);

        // Assert the response
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                ['id' => 1, 'brand' => 5, 'color' => 'Great car', 'model' => 'bmw'],
                ['id' => 2, 'brand' => 4, 'color' => 'Good car', 'model' => 'x22'],
            ]),
            $response->getContent()
        );
    }

}