<?php

namespace App\Controller;

use App\Service\AuthenticationService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Diactoros\Response\JsonResponse;

class RecipeController
{
    private $container;
    private $authenticationService;

    public function __construct(\Pimple\Container $container)
    {
        $this->container = $container;
        $this->authenticationService = $container['authentication_service'];
    }

    public function list(Request $request): JsonResponse
    {
        $params = $request->getQueryParams();
        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = max(1, min(50, (int)($params['per_page'] ?? 10)));
        $search = $params['search'] ?? null;

        $recipes = $this->container['recipe_repository']->findAll($page, $perPage, $search);
        $total = $this->container['recipe_repository']->countAll($search);

        $data = array_map(function ($recipe) {
            return [
                'id' => $recipe->getId(),
                'name' => $recipe->getName(),
                'prep_time' => $recipe->getPrepTime(),
                'difficulty' => $recipe->getDifficulty(),
                'vegetarian' => $recipe->getVegetarian(),
                'average_rating' => $recipe->getAverageRating()
            ];
        }, $recipes);

        return new JsonResponse([
            'recipes' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        if (!$this->authenticationService->isAuthenticated($request)) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);
        if ($data === null) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        $error = $this->validateRecipeData($data);
        if ($error) {
            return new JsonResponse(['error' => $error], 400);
        }

        $id = $this->container['recipe_repository']->insert($data);
        return new JsonResponse(['id' => $id], 201);
    }

    public function get(Request $request, array $vars): JsonResponse
    {
        $id = (int)$vars['id'];
        $recipe = $this->container['recipe_repository']->findById($id);
        if (!$recipe) {
            return new JsonResponse(['error' => 'Recipe not found'], 404);
        }

        $data = [
            'id' => $recipe->getId(),
            'name' => $recipe->getName(),
            'prep_time' => $recipe->getPrepTime(),
            'difficulty' => $recipe->getDifficulty(),
            'vegetarian' => $recipe->getVegetarian(),
            'average_rating' => $recipe->getAverageRating()
        ];
        return new JsonResponse($data);
    }

    public function update(Request $request, array $vars): JsonResponse
    {
        if (!$this->authenticationService->isAuthenticated($request)) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $id = (int)$vars['id'];
        if (!$this->container['recipe_repository']->findById($id)) {
            return new JsonResponse(['error' => 'Recipe not found'], 404);
        }

        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);
        if ($data === null) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        $error = $this->validateRecipeData($data);
        if ($error) {
            return new JsonResponse(['error' => $error], 400);
        }

        $success = $this->container['recipe_repository']->update($id, $data);
        return new JsonResponse(['success' => $success]);
    }

    public function delete(Request $request, array $vars): JsonResponse
    {
        if (!$this->authenticationService->isAuthenticated($request)) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $id = (int)$vars['id'];
        if (!$this->container['recipe_repository']->findById($id)) {
            return new JsonResponse(['error' => 'Recipe not found'], 404);
        }

        $success = $this->container['recipe_repository']->delete($id);
        return new JsonResponse(['success' => $success]);
    }

    public function rate(Request $request, array $vars): JsonResponse
    {
        $id = (int)$vars['id'];
        if (!$this->container['recipe_repository']->findById($id)) {
            return new JsonResponse(['error' => 'Recipe not found'], 404);
        }

        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);
        if ($data === null || !isset($data['rating']) || !is_int($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            return new JsonResponse(['error' => 'Rating must be an integer between 1 and 5'], 400);
        }

        $this->container['rating_repository']->insert($id, $data['rating']);
        return new JsonResponse(['success' => true], 201);
    }

    private function validateRecipeData(array $data): ?string
    {
        if (!isset($data['name']) || !is_string($data['name']) || empty(trim($data['name']))) {
            return 'Name is required and must be a non-empty string';
        }
        if (!isset($data['prep_time']) || !is_string($data['prep_time'])) {
            return 'Prep time is required and must be a string';
        }
        if (!isset($data['difficulty']) || !is_int($data['difficulty']) || $data['difficulty'] < 1 || $data['difficulty'] > 3) {
            return 'Difficulty must be an integer between 1 and 3';
        }
        if (!isset($data['vegetarian']) || !is_bool($data['vegetarian'])) {
            return 'Vegetarian must be a boolean';
        }
        return null;
    }
}