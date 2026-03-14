<?php

require_once __DIR__ . '/AudioService.php';
require_once __DIR__ . '/AudioRepository.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/Controller.php';

require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

class AudioController extends Controller
{
    private AudioService $service;

    public function __construct()
    {
        parent::__construct();
        $db = self::getConnection();
        $repo = new AudioRepository($db);
        $this->service = new AudioService($repo);
    }

    public function getOne()
    {
        AuthMiddleware::handle();

        echo json_encode([
            "success" => true,
            "data" => null,
            "message" => null
        ]); 
    }
    // ============================================
    // ADD AUDIO
    // ============================================

    public function addAudio()
    {
        AuthMiddleware::handle();

        try {
            $result = $this->service->addAudio($_POST, $_FILES);
            $this->success($result);
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    // ============================================
    // UPDATE AUDIO
    // ============================================

    public function updateAudio()
    {
        AuthMiddleware::handle();

        try {
            $result = $this->service->updateAudio($_POST, $_FILES);
            $this->success($result);
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    // ============================================
    // DELETE AUDIO
    // ============================================

    public function deleteAudio()
    {
        AuthMiddleware::handle();

        try {
            $result = $this->service->deleteAudio((int)$_POST['id']);
            $this->success($result);
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }
    
    public function loadAudios()
    {
        AuthMiddleware::handle();
        try {
            
            $audios = $this->service->loadAudios($this->request);

            echo json_encode([
                "success" => true,
                "data" => $audios,
                "message" => null
            ]);

        } catch (Exception $e) {

            http_response_code(400);

            echo json_encode([
                "success" => false,
                "data" => null,
                "message" => $e->getMessage()
            ]);
        }
    }
}
