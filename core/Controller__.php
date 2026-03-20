<?php

require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

abstract class Controller
{
    protected array $requestData = [];
    protected array $request = [];

    public static function getConnection(): PDO {
        return Database::getConnection();
    }

    public function __construct()
    {
        $this->parseJsonRequest();
        $this->recordStatisticsData();//echo "Here we have done it!"; exit;
        
        
    }
    
    public function recordStatisticsData()
    {
        $token = AuthMiddleware::getCurrentToken();
        if($token != null) {
                $user = AuthMiddleware::handle();
                $user['user_id'];
                $logFile = __DIR__ . '/../log_file_'. $user['user_id'] . '.txt';
                $logData = [
                    'time' => date('Y-m-d H:i:s'),
                    'server' => $_SERVER,
                    'request' => $this->request
                ];
                
            $logText = json_encode($logData, JSON_PRETTY_PRINT) . PHP_EOL . "----------------------";
            file_put_contents($logFile, $logText, FILE_APPEND);
        }
    }
    
    
    /**
     * Parse JSON body from Flutter
     */
    private function parseJsonRequest(): void
    {
        $input = file_get_contents("php://input");
        $this->request = $this->requestData = $input ? json_decode($input, true) ?? [] : [];
    }

    /**
     * Get a value from request safely
     */
    protected function input(string $key, $default = null)
    {
        return $this->requestData[$key] ?? $default;
    }

    /**
     * Validate required fields
     */
    protected function validate(array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($this->requestData[$field]) || $this->requestData[$field] === '') {
                Response::error("Field '{$field}' is required.");
                exit;
            }
        }
    }

    /**
     * Standard success response
     */
    protected function success($data = null, string $message = null): void
    {
        Response::success($data, $message);
        exit;
    }

    /**
     * Standard error response
     */
    protected function error(string $message, int $code = 400): void
    {
        http_response_code($code);
        Response::error($message);
        exit;
    }

    /**
     * Get Authorization header (for JWT later)
     */
    protected function getAuthorizationHeader(): ?string
    {
        return $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    }
}
