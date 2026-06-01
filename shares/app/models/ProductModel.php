<?php
class ProductModel {
    private $id;
    private $name;
    private $description;
    private $price;
    private $image;
    private $category_id;

    public function __construct($id, $name, $description, $price, $image = 'default.jpg', $category_id = null) {
        $this->id          = $id;
        $this->name        = $name;
        $this->description = $description;
        $this->price       = $price;
        $this->image       = $image;
        $this->category_id = $category_id;
    }

    public function getID()          { return $this->id; }
    public function setID($id)       { $this->id = $id; }

    public function getName()        { return $this->name; }
    public function setName($name)   { $this->name = $name; }

    public function getDescription()             { return $this->description; }
    public function setDescription($description) { $this->description = $description; }

    public function getPrice()       { return $this->price; }
    public function setPrice($price) { $this->price = $price; }

    public function getImage()       { return $this->image; }
    public function setImage($image) { $this->image = $image; }

    public function getCategoryID()            { return $this->category_id; }
    public function setCategoryID($category_id){ $this->category_id = $category_id; }
}
