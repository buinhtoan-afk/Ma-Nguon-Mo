<?php
class BannerModel {
    private $id;
    private $image;
    private $title;
    private $position; // 1, 2, 3

    public function __construct($id, $image, $title = '', $position = 1) {
        $this->id       = $id;
        $this->image    = $image;
        $this->title    = $title;
        $this->position = $position;
    }

    public function getID()       { return $this->id; }
    public function getImage()    { return $this->image; }
    public function setImage($v)  { $this->image = $v; }
    public function getTitle()    { return $this->title; }
    public function setTitle($v)  { $this->title = $v; }
    public function getPosition() { return $this->position; }
}
