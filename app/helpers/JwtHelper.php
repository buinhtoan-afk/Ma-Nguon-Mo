
<?php
class JwtHelper {
    private static $secret='SECRET_KEY_2026';
    public static function generate($payload){
        $header=self::b64(json_encode(['alg'=>'HS256','typ'=>'JWT']));
        $payload=self::b64(json_encode($payload));
        $sig=self::b64(hash_hmac('sha256',"$header.$payload",self::$secret,true));
        return "$header.$payload.$sig";
    }
    public static function verify($jwt){
        $p=explode('.',$jwt); if(count($p)!==3) return false;
        $sig=self::b64(hash_hmac('sha256',"$p[0].$p[1]",self::$secret,true));
        if(!hash_equals($sig,$p[2])) return false;
        $data=json_decode(base64_decode(strtr($p[1],'-_','+/')),true);
        if(isset($data['exp']) && $data['exp']<time()) return false;
        return $data;
    }
    private static function b64($d){ return rtrim(strtr(base64_encode($d),'+/','-_'),'=');}
}
