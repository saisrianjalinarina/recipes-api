<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

class RatingRepository
{
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function insert(int $recipeId, int $rating): void
    {
        $this->db->insert('ratings', [
            'recipe_id' => $recipeId,
            'rating' => $rating
        ]);
    }
}