<?php

require_once __DIR__ . '/AccountService.php';
require_once __DIR__ . '/AccountRepository.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../core/Controller.php';

require_once __DIR__ . '/../../middleware/AuthMiddleware.php';

class AccountController extends Controller
{
    private AccountService $service;

    public function __construct()
    {
        parent::__construct();
        $db = self::getConnection();
        $repo = new AccountRepository($db);
        $this->service = new AccountService($repo);
    }

    // ============================================
    // CREATE PARENT
    // ============================================

    public function parentLoadChildren()
    {
        try {
            $result = $this->service->parentLoadChildren($this->request);
            $this->success($result);
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    // ============================================
    // CREATE PARENT
    // ============================================

    public function createParent()
    {
        try {
            $result = $this->service->createParent($this->request);
            $this->success($result);
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    // ============================================
    // LOGIN
    // ============================================

    public function login()
    {
        //$this->success(["message" => "Logged in successfully"]);
        try {
            $result = $this->service->login($this->request);
            $this->success($result);
        } catch (Exception $e) {
            $this->error($e->getMessage(), 401);
        }
    }

    // ============================================
    // LOGOUT
    // ============================================

    public function logout()
    {
        // JWT is stateless → just return success
        $this->success(["message" => "Logged out successfully"]);
    }

    // ============================================
    // ADD CHILD (Protected)
    // ============================================

    public function addChild()
    {
        $user = AuthMiddleware::handle();

        try {
            $result = $this->service->addChild(
                $this->request,
                $user['user_id']
            );

            $this->success($result);
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    // ============================================
    // DELETE CHILD (Protected)
    // ============================================

    public function deleteChild()
    {
        $user = AuthMiddleware::handle();

        try {
            $result = $this->service->deleteChild(
                $this->request['child_id'],
                $user['user_id']
            );

            $this->success($result);
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    // ============================================
    // LOAD PAYMENT (Protected)
    // ============================================

    public function loadPayment()
    {
        $user = AuthMiddleware::handle();

        try {
            $result = $this->service->loadPayment(
                $this->request['parent_id']
            );

            $this->success($result);
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    public function loadLevels() {
        $result = $this->service->loadLevels();
        $this->success($result);
    }
}