
<?php
require_once 'app/config/database.php';
require_once 'app/helpers/JwtHelper.php';
class AuthApiController {
 private $conn;
 function __construct(){ $db=new Database(); $this->conn=$db->getConnection(); }
 private function json($d,$c=200){ http_response_code($c); header('Content-Type: application/json'); echo json_encode($d); exit; }
 public function register(){ $i=json_decode(file_get_contents("php://input"),true); $hash=password_hash($i['password'],PASSWORD_DEFAULT);
 $st=$this->conn->prepare("INSERT INTO user(fullname,email,password) VALUES(?,?,?)"); $st->execute([$i['fullname'],$i['email'],$hash]); $this->json(['success'=>true]);}
 public function login(){ $i=json_decode(file_get_contents("php://input"),true); $st=$this->conn->prepare("SELECT * FROM user WHERE email=?"); $st->execute([$i['email']]); $u=$st->fetch(PDO::FETCH_ASSOC);
 if(!$u||!password_verify($i['password'],$u['password'])) $this->json(['success'=>false],401);
 $t=JwtHelper::generate(['id'=>$u['id'],'email'=>$u['email'],'exp'=>time()+3600]); $this->json(['success'=>true,'token'=>$t]);}
}
