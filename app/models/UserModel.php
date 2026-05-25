<?php
class UserModel {
    private $id;
    private $fullname;
    private $email;
    private $phone;
    private $password;
    private $created_at;

    public function __construct($id, $fullname, $email, $phone, $password, $created_at = '') {
        $this->id         = $id;
        $this->fullname   = $fullname;
        $this->email      = $email;
        $this->phone      = $phone;
        $this->password   = $password;
        $this->created_at = $created_at;
    }

    public function getID()        { return $this->id; }
    public function getFullname()  { return $this->fullname; }
    public function getEmail()     { return $this->email; }
    public function getPhone()     { return $this->phone; }
    public function getPassword()  { return $this->password; }
    public function getCreatedAt() { return $this->created_at; }
}
