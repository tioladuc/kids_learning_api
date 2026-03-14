<?php

// ============================
// CORS HEADERS
// ============================
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
$GLOBALS["URL_BASE"] = "https://yehoshoualevivant.com/learn4kids/";

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/config/cors.php';

header('Content-Type: application/json');

$router = new Router();

// ACCOUNT
$router->post('/account/login', 'AccountController@login');
$router->post('/account/logout', 'AccountController@logout');
$router->post('/account/addChild', 'AccountController@addChild');
$router->post('/account/deleteChild', 'AccountController@deleteChild');
$router->post('/createParent', 'AccountController@createParent');
$router->post('/account/parentLoadChildren', 'AccountController@parentLoadChildren');

// COURSE
$router->post('/course/loadChildPendingCourses', 'CourseController@loadChildPendingCourses');
$router->post('/course/loadChildPickCourses', 'CourseController@loadChildPickCourses');
$router->post('/course/loadChildAvailableCourses', 'CourseController@loadAvailableCourses');
$router->post('/course/pickCourse', 'CourseController@pickCourse');
$router->post('/course/payCourse', 'CourseController@payCourse'); //redo the pay part according to periodicity
$router->post('/course/removeCourse', 'CourseController@removeCourse');

// AUDIO
$router->post('/audio/addAudio', 'AudioController@addAudio');
$router->post('/audio/updateAudio', 'AudioController@updateAudio');
$router->post('/audio/deleteAudio', 'AudioController@deleteAudio');
$router->post('/audio/loadAudios', 'AudioController@loadAudios');
$router->post('/audio/getOne', 'AudioController@getOne');

// STATISTICS
$router->post('/statistics/loadVisitedCourses', 'StatisticsController@visitedCourses');

$router->run();
