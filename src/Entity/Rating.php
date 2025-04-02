<?php

namespace App\Entity;

class Rating
{
    private $id;
    private $recipeId;
    private $rating;

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getRecipeId() { return $this->recipeId; }
    public function setRecipeId($recipeId) { $this->recipeId = $recipeId; }
    public function getRating() { return $this->rating; }
    public function setRating($rating) { $this->rating = $rating; }
}