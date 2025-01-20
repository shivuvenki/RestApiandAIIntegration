<?php
require "vendor/autoload.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtMiddleware
{
    private $secretKey;

    public function __construct($secretKey)
    {
        $this->secretKey = $secretKey;
    }
    function validate_jwt($token, $secret)
    {
        try {
            $decoded = JWT::decode($token, new Key($secret, "HS256"));
            return $decoded;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized: Invalid token"]);
            exit();
        }
    }
    public function handle($request, $next)
    {
        $headers = getallheaders();
        if (isset($headers["Authorization"])) {
            $authHeader = $headers["Authorization"];
            if (preg_match("/Bearer\s(.*)/", $authHeader, $matches)) {
                $token = $matches[1];
                $this->validate_jwt($token, $this->secretKey);
                return $next($request);
            } else {
                http_response_code(401);
                echo json_encode([
                    "message" => "Unauthorized: No Bearer token",
                ]);
                exit();
            }
        } else {
            http_response_code(401);
            echo json_encode([
                "message" => "Unauthorized: Missing Authorization header",
            ]);
            exit();
        }
    }
}
?>
