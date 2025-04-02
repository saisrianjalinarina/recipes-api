<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\DBAL\Connection;

class RecipeRepository
{
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function findAll(int $page = 1, int $perPage = 10, ?string $search = null): array
    {
        $query = $this->db->createQueryBuilder();
        $query->select('r.*, COALESCE(AVG(rt.rating), 0) as average_rating')
              ->from('recipes', 'r')
              ->leftJoin('r', 'ratings', 'rt', 'r.id = rt.recipe_id')
              ->groupBy('r.id')
              ->orderBy('r.id', 'ASC');

        if ($search) {
            $query->where('r.name ILIKE :search')
                  ->setParameter('search', '%' . $search . '%');
        }

        $offset = ($page - 1) * $perPage;
        $query->setFirstResult($offset)->setMaxResults($perPage);

        $results = $query->execute()->fetchAll();

        return array_map(function ($data) {
            $recipe = new Recipe();
            $recipe->setId((int)$data['id']);
            $recipe->setName($data['name']);
            $recipe->setPrepTime($data['prep_time']);
            $recipe->setDifficulty((int)$data['difficulty']);
            $recipe->setVegetarian((bool)$data['vegetarian']);
            $recipe->setAverageRating((float)$data['average_rating']);
            return $recipe;
        }, $results);
    }

    public function countAll(?string $search = null): int
    {
        $query = $this->db->createQueryBuilder();
        $query->select('COUNT(DISTINCT r.id)')
              ->from('recipes', 'r');

        if ($search) {
            $query->where('r.name ILIKE :search')
                  ->setParameter('search', '%' . $search . '%');
        }

        return (int)$query->execute()->fetchColumn();
    }

    public function findById(int $id): ?Recipe
    {
        $query = $this->db->createQueryBuilder();
        $query->select('r.*, COALESCE(AVG(rt.rating), 0) as average_rating')
              ->from('recipes', 'r')
              ->leftJoin('r', 'ratings', 'rt', 'r.id = rt.recipe_id')
              ->where('r.id = :id')
              ->setParameter('id', $id)
              ->groupBy('r.id');

        $data = $query->execute()->fetch();
        if (!$data) {
            return null;
        }

        $recipe = new Recipe();
        $recipe->setId((int)$data['id']);
        $recipe->setName($data['name']);
        $recipe->setPrepTime($data['prep_time']);
        $recipe->setDifficulty((int)$data['difficulty']);
        $recipe->setVegetarian((bool)$data['vegetarian']);
        $recipe->setAverageRating((float)$data['average_rating']);
        return $recipe;
    }

    public function insert(array $data): int
    {
        $this->db->insert('recipes', [
            'name' => $data['name'],
            'prep_time' => $data['prep_time'],
            'difficulty' => $data['difficulty'],
            'vegetarian' => $data['vegetarian']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $rows = $this->db->update('recipes', [
            'name' => $data['name'],
            'prep_time' => $data['prep_time'],
            'difficulty' => $data['difficulty'],
            'vegetarian' => $data['vegetarian']
        ], ['id' => $id]);
        return $rows > 0;
    }

    public function delete(int $id): bool
    {
        $rows = $this->db->delete('recipes', ['id' => $id]);
        return $rows > 0;
    }
}