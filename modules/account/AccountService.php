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
            'email' => $input['email'],
            'codeparent' => $input['codeparent'],
            'codesecret' => ''
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
        $parent = $this->repo->findParentByLogin($input['login'], $input['codeparent']);

        if (!$parent) {
            throw new Exception("Invalid credentials");
        }
        
        if (!password_verify($input['password'], $parent['password'])) {
            throw new Exception("Invalid credentials");
        }

        $this->repo->updateSecretCode($parent['id'], false);
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
        $child = $this->repo->findChildByLogin($input['login'], $input['codeparent']);

        if (!$child) {
            throw new Exception("Invalid credentials");
        }
        
        if (!password_verify($input['password'], $child['password'])) {
            throw new Exception("Invalid credentials");
        }

        $this->repo->updateSecretCode($child['id'], true);
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


    public function changePasswordParentChild($input) {
        $data = [
            'name' => $input['name'],
            'password' => password_hash($input['new_password'], PASSWORD_BCRYPT),
            'passwordraw' => $input['new_password'],
            'level' => $input['level'] ?? '',
            'login' => $input['login'],
            'codeparent' => $input['codeparent'],
            'child_id' => $input['child_id']
        ];

        $this->repo->changePasswordParentChild($data);

        return [
            "message" => "Child update successfully",
        ];
    }
    public function changeParentPassword($input) {
        $data = [
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'new_password' => password_hash($input['new_password'], PASSWORD_BCRYPT),
            'parent_id' => $input['parent_id'] ?? '',
        ];

        $this->repo->changeParentPassword($data);

        return [
            "message" => "Parent update successfully",
        ];
    }
    // ============================================
    // ADD CHILD
    // ============================================

    public function addChild(array $input, string $parentId): array
    {
        $idChild = uniqid("child_");
        $data = [
            'id' => $idChild,
            'parent_id' => $parentId,
            'name' => $input['name'],
            'login' => $input['login'],
            'password' => password_hash($input['password'], PASSWORD_BCRYPT),
            'passwordraw' => $input['password'],
            'parent_responsible' => $input['parent_responsible'] ?? 0,
            'codeparent' => $input['codeparent'],
            'level' => $input['level'],
        ];

        $this->repo->addChild($data);

        return [
            "message" => "Child added successfully",
            "id" => $idChild,
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

    // ============================================
    // LOAD PAYMENT (Protected)
    // ============================================

    public function loadPayment(string $parentId): array
    {
        return $this->repo->loadPayment($parentId);
    }
    
    public function loadLevels(): array
    {
        return $this->repo->loadLevels();
    }

    public function sendActivationCodeParent($email): bool
    {        
        return $this->repo->sendActivationCodeParent($email);
    }

    public function resetParentPassword($input): bool
    {
        return $this->repo->resetParentPassword($input);
    }
}