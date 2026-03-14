<?php

require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

$GLOBALS['CourseArray'] = array(
    "audio" => "C000"
);

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
    /***************************************************************************/
    public function recordStatisticsData()
    {
        $keyUrl = $this->findKeyUrlCourse($_SERVER['REDIRECT_REDIRECT_SCRIPT_URL']);        
        if($keyUrl == null) return;
        $token = AuthMiddleware::getCurrentToken();
        if($token != null) {
            $user = AuthMiddleware::handle();
            
            if($keyUrl != null && isset($this->request['child_id'])) {
                $latestStat = $this->getLatestVisitedCourse(self::getConnection());
                
                $child_id = $this->request['child_id'];
                $item_course_id = isset($this->request['item_course_id']) ? $this->request['item_course_id'] : 0;
                $course_code = $GLOBALS['CourseArray'][$keyUrl];
                if($latestStat == false) { // add a stats
                    $this->addVisitedCourse(self::getConnection(), [
                        "child_id" => $child_id,
                        "course_code" => $course_code,
                        "id_item_course" => $item_course_id
                    ]);
                }
                else {
                    if($item_course_id == $latestStat['id_item_course']) { //update
                        $this->updateVisitedCourse(self::getConnection(), $latestStat);
                    }
                    else { //update the last one and add a new one
                        $this->updateVisitedCourse(self::getConnection(), $latestStat);
                        $this->addVisitedCourse(self::getConnection(), [
                            "child_id" => $child_id,
                            "course_code" => $course_code,
                            "id_item_course" => $item_course_id
                        ]);
                    }
                }
            }
                
                
               /* getLatestStatistics
                $user['user_id'];
                $logFile = __DIR__ . '/../log_file_'. $user['user_id'] . '.txt';
                $logData = [
                    'time' => date('Y-m-d H:i:s'),
                    'server' => $_SERVER,
                    'request' => $this->request
                ];
                
            $logText = json_encode($logData, JSON_PRETTY_PRINT) . PHP_EOL . "----------------------";
            file_put_contents($logFile, $logText, FILE_APPEND);*/
        }
    }
    function findKeyUrlCourse($url) {
        foreach ($GLOBALS['CourseArray'] as $key => $value) {
           if( str_contains($url, "/$key/") ) {
               return $key;
           }
        }
        return null;
    }
    function getLatestVisitedCourse(PDO $pdo) {
        $idleTimeInMinute = 10;
        $sql = "SELECT * 
                FROM learn4kids_visited_courses
                WHERE TIMESTAMPDIFF(MINUTE, last_connection, now()) < $idleTimeInMinute 
                ORDER BY last_connection DESC
                LIMIT 1";
    
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    function updateVisitedCourse(PDO $pdo, array $item) {

        if (!isset($item['id']) || !isset($item['last_connection'])) {
            throw new Exception("Invalid item");
        }
    
        $lastConnection = new DateTime($item['last_connection']);
        $now = new DateTime();
    
        $diffSeconds = $now->getTimestamp() - $lastConnection->getTimestamp();
        $diffMinutes = floor($diffSeconds / 60);
    
        if ($diffMinutes < 0) {
            $diffMinutes = 0;
        }
    
        $newTimeSpent = ($item['time_spent'] ?? 0) + $diffMinutes;
    
        $sql = "UPDATE learn4kids_visited_courses
                SET 
                    time_spent = :time_spent,
                    last_connection = NOW()
                WHERE id = :id";
    
        $stmt = $pdo->prepare($sql);
    
        $stmt->execute([
            ':time_spent' => $newTimeSpent,
            ':id' => $item['id']
        ]);
    }
    function addVisitedCourse(PDO $pdo, array $data) {

        $sql = "INSERT INTO learn4kids_visited_courses
                (child_id, course_code, id_item_course, time_spent, last_connection)
                VALUES
                (:child_id, :course_code, :id_item_course, :time_spent, NOW())";
    
        $stmt = $pdo->prepare($sql);
    
        $stmt->execute([
            ':child_id' => $data['child_id'] ?? null,
            ':course_code' => $data['course_code'] ?? null,
            ':id_item_course' => $data['id_item_course'],
            ':time_spent' => $data['time_spent'] ?? 0
        ]);
    
        return $pdo->lastInsertId();
    }
    /***************************************************************************/
    
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
