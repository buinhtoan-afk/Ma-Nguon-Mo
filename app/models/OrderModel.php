<?php
class OrderModel {
    private $id;
    private $user_id;
    private $fullname;
    private $phone;
    private $address;
    private $city;
    private $note;
    private $shipping_method;
    private $payment_method;
    private $total;
    private $status;
    private $created_at;

    public function __construct($data) {
        $this->id              = $data['id']              ?? 0;
        $this->user_id         = $data['user_id']         ?? null;
        $this->fullname        = $data['fullname']        ?? '';
        $this->phone           = $data['phone']           ?? '';
        $this->address         = $data['address']         ?? '';
        $this->city            = $data['city']            ?? '';
        $this->note            = $data['note']            ?? '';
        $this->shipping_method = $data['shipping_method'] ?? 'standard';
        $this->payment_method  = $data['payment_method']  ?? 'cod';
        $this->total           = $data['total']           ?? 0;
        $this->status          = $data['status']          ?? 'pending';
        $this->created_at      = $data['created_at']      ?? '';
    }

    public function getID()             { return $this->id; }
    public function getUserID()         { return $this->user_id; }
    public function getFullname()       { return $this->fullname; }
    public function getPhone()          { return $this->phone; }
    public function getAddress()        { return $this->address; }
    public function getCity()           { return $this->city; }
    public function getNote()           { return $this->note; }
    public function getShippingMethod() { return $this->shipping_method; }
    public function getPaymentMethod()  { return $this->payment_method; }
    public function getTotal()          { return $this->total; }
    public function getStatus()         { return $this->status; }
    public function getCreatedAt()      { return $this->created_at; }
}
