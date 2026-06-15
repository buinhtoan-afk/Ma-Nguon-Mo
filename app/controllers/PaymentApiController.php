
<?php
require_once 'app/config/database.php';
class PaymentApiController {
 private $conn;
 function __construct(){ $db=new Database(); $this->conn=$db->getConnection();
 $this->conn->exec("CREATE TABLE IF NOT EXISTS payments(id INT AUTO_INCREMENT PRIMARY KEY,order_id INT,payment_method VARCHAR(50),amount DECIMAL(15,2) DEFAULT 0,payment_status ENUM('pending','paid','failed') DEFAULT 'pending',created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
 }
}
