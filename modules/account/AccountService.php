<?php

use Firebase\JWT\JWT;

class AccountService
{
    private AccountRepository $repo;
    private string $secretKey = "learn4kids api provides the services to be consumed by the flutter application";

    public function __construct(AccountRepository $repo)
    {
        $this->repo = $repo;
    }

    // ============================================
    // CREATE PARENT
    // ============================================

    public function parentLoadChildren(array $input): array
    {
        $parentId = $input['parent_id'];

        return $this->repo->parentLoadChildren($parentId);
    }

    // ============================================
    // CREATE PARENT
    // ============================================

    public function createParent(array $input): array
    {
        $id = uniqid("parent_");

        $data = [
            'id' => $id,
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'login' => $input['login'],
            'password' => password_hash($input['password'], PASSWORD_BCRYPT),
            'email' => $input['email']
        ];

        $this->repo->createParent($data);

        return [
            "message" => "Parent created successfully"
        ];
    }

    // ============================================
    // LOGIN
    // ============================================

    public function login(array $input): array
    {
        return $input['role'] == "parent" ? $this->loginAsParent($input) : $this->loginAsChild($input);
    }

    private function loginAsParent(array $input): array
    {        
        $parent = $this->repo->findParentByLogin($input['login']);

        if (!$parent) {
            throw new Exception("Invalid credentials");
        }
        
        if (!password_verify($input['password'], $parent['password'])) {
            throw new Exception("Invalid credentials");
        }

        
        $token = $this->generateToken($parent['id']);
        
        return [
            "token" => $token,
            "user_id" => $parent['id'],
            "parent" => $this->repo->getParentById($parent['id']),
            "children" => $this->repo->getChildrenByParentId($parent['id']),
            "role" => "parent"
        ];
    }
    private function loginAsChild(array $input): array
    {
        $child = $this->repo->findChildByLogin($input['login']);

        if (!$child) {
            throw new Exception("Invalid credentials");
        }
        
        if (!password_verify($input['password'], $child['password'])) {
            throw new Exception("Invalid credentials");
        }

        
        $token = $this->generateToken($child['id']);
        
        return [
            "token" => $token,
            "user_id" => $child['id'],
            "child" => $this->repo->getChildById($child['id']),
            "role" => "child"
        ];
    }

    private function generateToken(string $userId): string
    {
        $payload = [
            "iss" => "learn4kids",
            "iat" => time(),
            "exp" => time() + (60 * 60 * 24),
            "user_id" => $userId,
            "role" => "parent"
        ];
        
        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    // ============================================
    // ADD CHILD
    // ============================================

    public function addChild(array $input, string $parentId): array
    {
        $data = [
            'id' => uniqid("child_"),
            'parent_id' => $parentId,
            'name' => $input['name'],
            'login' => $input['login'],
            'password' => password_hash($input['password'], PASSWORD_BCRYPT),
            'passwordraw' => $input['password'],
            'parent_responsible' => $input['parent_responsible'] ?? 0
        ];

        $this->repo->addChild($data);

        return [
            "message" => "Child added successfully"
        ];
    }

    // ============================================
    // DELETE CHILD
    // ============================================

    public function deleteChild(string $childId, string $parentId): array
    {
        $this->repo->deleteChild($childId, $parentId);

        return [
            "message" => "Child deleted successfully"
        ];
    }
}