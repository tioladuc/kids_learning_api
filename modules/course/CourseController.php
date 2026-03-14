<?php

require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

require_once __DIR__ . '/CourseService.php';
require_once __DIR__ . '/CourseRepository.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/Controller.php';

class CourseController extends Controller
{
    private CourseService $service;

    public function __construct()
    {
        parent::__construct();
        $db = self::getConnection();
        $repo = new CourseRepository($db);
        $this->service = new CourseService($repo);
    }

    public function loadChildPendingCourses()
    {
        $user = AuthMiddleware::handle();
        $childId = $this->request['child_id'];

        $courses = $this->service->loadChildPendingCourses($childId);
        $this->success($courses);
    }

    public function loadChildPickCourses()
    {
        $user = AuthMiddleware::handle();
        $childId = $this->request['child_id'];

        $courses = $this->service->loadChildPickCourses($childId);
        $this->success($courses);
    }

    public function loadAvailableCourses()
    {
        $user = AuthMiddleware::handle();
        $childId = $this->request['child_id'];

        $courses = $this->service->loadAvailableCourses($childId);
        $this->success($courses);
    }

    public function pickCourse()
    {    	
        $user = AuthMiddleware::handle();

        $result = $this->service->pickCourse(
            $this->request['child_id'],
            $this->request['course_code']
        );

        $this->success($result);
    }

    public function removeCourse()
    {    	
        $user = AuthMiddleware::handle();

        $result = $this->service->removeCourse(
            $this->request['child_id'],
            $this->request['course_code']
        );

        $this->success($result);
    }

    public function payCourse()
    {
        $user = AuthMiddleware::handle();

        $result = $this->service->payCourse(
            $this->request['child_id'],
            $this->request['course_code'],
            (float)$this->request['amount'],
            $user['user_id']
        );

        $this->success($result);
    }
}
