<?php

namespace App\Controller;


use App\Entity\Review;


use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;
/**
 * @OA\Info(
 *     title="Review API",
 *     version="1.0.0"
 * )
 */
class ReviewController extends BaseApiController
{
    private ReviewRepository $reviewRepository;
    private $validator;
    public function __construct(ReviewRepository $reviewRepository, ValidatorInterface $validator)
    {
        $this->reviewRepository = $reviewRepository;
        $this->validator = $validator;
    }
    /**
     * @OA\Get(
     *     path="/review",
     *     summary="Get a list of reviews",
     *     tags={"Reviews"},
     *     @OA\Response(response="200", description="Successful operation"),
     * )
     */

    #[Route('/review', name: 'review_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $reviews = $this->reviewRepository->findAllEntities();
        $reviewData = [];
        foreach ($reviews as $review) {
            $reviewData[] = [
                'id' => $review->getId(),
                'brand' => $review->getStarRating(),
                'color' => $review->getReviewText(),
                'model' => $review->getModel(),
            ];
        }

        return $this->json($reviewData);
    }
    /**
     * @OA\Post(
     *     path="/entities",
     *     summary="Create a new review",
     *     tags={"Reviews"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ReviewInput")
     *     ),
     *     @OA\Response(response="200", description="Review created successfully"),
     *     @OA\Response(response="400", description="Invalid input data")
     * )
     */
    #[Route('/entities', name: 'review_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $requestData = json_decode($request->getContent(), true);
        // Set properties from the request
        $review = new Review();

        $review->setStarRating($requestData['starRating']);
        $review->setReviewText($requestData['reviewText']);

        // Validate the entity
        $errors = $this->validator->validate($review);

        if (count($errors) > 0) {
            return $this->failedMessage($errors);
        }

        // Persist and flush to DB
        $this->reviewRepository->saveEntity($review);
        return $this->successMessage('Car created successfully');
    }
    /**
     * @OA\Put(
     *     path="/entities/{id}",
     *     summary="Update a review",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the review",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ReviewInput")
     *     ),
     *     @OA\Response(response="200", description="Review updated successfully"),
     *     @OA\Response(response="400", description="Invalid input data"),
     *     @OA\Response(response="404", description="Review not found")
     * )
     */
    #[Route('/entities/{id}', name: 'review_update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        // Find the entity to update
        $review = $this->reviewRepository->findEntityById($id);

        if (!$review) {
            return $this->notFoundMessage('review not found');
        }
        $review->setStarRating($requestData['star_rating'] ?? $review->getStarRating());
        $review->setReviewText($requestData['review_text'] ?? $review->getReviewText());

        // Validate the entity
        $errors = $this->validator->validate($review);

        if (count($errors) > 0) {
            return $this->handleValidationErrors($errors);
        }
        // Save the updated entity
        $this->reviewRepository->updateEntity($review);
        return $this->successMessage('Review updated successfully');

    }

    /**
     * @OA\Delete(
     *     path="/entities/{id}",
     *     summary="Delete a review",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the review",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Review deleted successfully"),
     *     @OA\Response(response="404", description="Review not found")
     * )
     */
    #[Route('/entities/{id}', name: 'review_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        // Find the entity to delete
        $review = $this->reviewRepository->findEntityById($id);
        if (!$review) {
            return $this->notFoundMessage('Review not found');
        }

        // Delete the entity
        $this->reviewRepository->deleteEntity($review);

        return $this->successMessage('Review deleted successfully');
    }
    /**
     * @OA\Get(
     *     path="/review/{id}",
     *     summary="Get a review by ID",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the review",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Successful operation"),
     *     @OA\Response(response="404", description="Review not found")
     * )
     */
    #[Route('/review/{id}', name: 'getReviewById', methods: ['GET'])]
    public function getReviewById(int $id): Response
    {
        $review = $this->reviewRepository->findEntityById($id);
        if (!$review) {
            return $this->notFoundMessage('Review not found');
        }
        $reviewData = [
            'id' => $review->getId(),
            'rating' => $review->getStarRating(),
            'review' => $review->getReviewText(),
            'car' => $review->getCar(),

        ];

        return $this->json($reviewData);
    }

    /**
     * @OA\Get(
     *     path="/entities/{id}",
     *     summary="Get high rating reviews",
     *     tags={"Reviews"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the entity",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Successful operation"),
     * )
     */
    #[Route('/entities/{id}', name: 'highRatingReview', methods: ['GET'])]
    public function highRatingReview($id): JsonResponse
    {
        $reviews = $this->reviewRepository->findHighRatingReviews($id);
        $reviewData = [];
        foreach ($reviews as $review) {
            $reviewData[] = [
                'id' => $review->getId(),
                'brand' => $review->getStarRating(),
                'color' => $review->getReviewText(),
                'model' => $review->getModel(),
            ];
        }

        return $this->json($reviewData);
    }

}
