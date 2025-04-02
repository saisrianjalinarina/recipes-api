<?php

namespace App\Entity;

class Recipe
{
    private $id;
    private $name;
    private $prepTime;
    private $difficulty;
    private $vegetarian;
    private $averageRating;

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }
    public function getPrepTime() { return $this->prepTime; }
    public function setPrepTime($prepTime) { $this->prepTime = $prepTime; }
    public function getDifficulty() { return $this->difficulty; }
    public function setDifficulty($difficulty) { $this->difficulty = $difficulty; }
    public function getVegetarian() { return $this->vegetarian; }
    public function setVegetarian($vegetarian) { $this->vegetarian = $vegetarian; }
    public function getAverageRating() { return $this->averageRating; }
    public function setAverageRating($averageRating) { $this->averageRating = $averageRating; }
}