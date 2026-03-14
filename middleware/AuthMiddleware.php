<?php















require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    private static string $secretKey = "learn4kids api provides the services to be consumed by the flutter application";

    public static function handle(): array
    {
        $token = self::getBearerToken();

        if (!$token) {
            self::unauthorized("Missing Authorization header");
        }

        try {

            $decoded = JWT::decode($token, new Key(self::$secretKey, 'HS256'));

            return (array) $decoded;

        } catch (Exception $e) {

            self::unauthorized("Invalid or expired token");

        }
    }

    /**
     * Extract Bearer token from headers
     */
    private static function getBearerToken(): ?string
    {
        $authHeader = null;

        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        } 
        elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } 
        elseif (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                $authHeader = $headers['Authorization'];
            }
        }

        if (!$authHeader) {
            return null;
        }

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function generateToken($userId, $role)
{
    $payload = [
        "iss" => "learn4kids",
        "iat" => time(),
        "exp" => time() + (60 * 60 * 24), // 24h
        "user_id" => $userId,
        "role" => $role
    ];

    return JWT::encode($payload, $this->secretKey, 'HS256');
}

    private static function unauthorized(string $message): void
    {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => $message
        ]);
        exit;
    }
}