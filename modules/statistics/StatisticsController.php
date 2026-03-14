<?php
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

require_once __DIR__ . '/StatisticsService.php';
require_once __DIR__ . '/StatisticsRepository.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/Controller.php';

class StatisticsController extends Controller
{
    private StatisticsService $statisticsService;

    private StatisticsService $service;

    public function __construct()
    {
        parent::__construct();
        $db = self::getConnection();
        $repo = new StatisticsRepository($db);
        $this->service = new StatisticsService($repo);
    }

    public function visitedCourses()
    {
        AuthMiddleware::handle();

        try {

            $input = $this->request;

            if (!isset($input['child_id'])) {
                throw new Exception("child_id is required");
            }

            $childId = $input['child_id'];

            $data = $this->service->loadVisitedCourses($childId);

            $this->success($data);

        } catch (Exception $e) {

            $this->error($e->getMessage(), 400);
        }
    }
}