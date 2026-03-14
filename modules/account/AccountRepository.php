<?php

class AccountRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ===============================
    // PARENT
    // ===============================

    public function parentLoadChildren($parentId): array
    {
        $sql = "SELECT * FROM learn4kids_children c
                WHERE parent_id = :parent_id
                ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':parent_id' => $parentId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===============================
    // PARENT
    // ===============================

    public function createParent(array $data): bool
    {
        $sql = "INSERT INTO learn4kids_parents
                (id, first_name, last_name, login, password, email, is_active, activation_code)
                VALUES (:id, :first_name, :last_name, :login, :password, :email, 1, NULL)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($data);
    }

    public function findParentByLogin(string $login)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM learn4kids_parents WHERE login = :login"
        );
        $stmt->execute(['login' => $login]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findChildByLogin(string $login)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM learn4kids_children WHERE login = :login"
        );
        $stmt->execute(['login' => $login]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ===============================
    // GET CHILD BY ID
    // ===============================

    public function getChildById(string $childId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                c.id,
                c.name,
                c.login,
                c.parent_responsible,
                c.parent_id,
                p.first_name AS parent_first_name,
                p.last_name AS parent_last_name,
                p.email AS parent_email
            FROM learn4kids_children c
            INNER JOIN learn4kids_parents p 
                ON c.parent_id = p.id
            WHERE c.id = :id
            LIMIT 1"
        );

        $stmt->execute([
            'id' => $childId
        ]);

        $child = $stmt->fetch(PDO::FETCH_ASSOC);

        return $child ?: null;
    }
    // ===============================
    // GET CHILDREN BY PARENT
    // ===============================

    public function getChildrenByParentId(string $parentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                id,
                name,
                login,
                parent_responsible
            FROM learn4kids_children
            WHERE parent_id = :parent_id"
        );

        $stmt->execute([
            'parent_id' => $parentId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===============================
    // GET PARENT BY ID
    // ===============================

    public function getParentById(string $parentId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                id,
                first_name,
                last_name,
                login,
                email,
                is_active,
                activation_code
            FROM learn4kids_parents
            WHERE id = :id
            LIMIT 1"
        );

        $stmt->execute([
            'id' => $parentId
        ]);

        $parent = $stmt->fetch(PDO::FETCH_ASSOC);

        return $parent ?: null;
    }

    // ===============================
    // CHILD
    // ===============================

    public function addChild(array $data): bool
    {
        $sql = "INSERT INTO learn4kids_children
                (id, parent_id, name, login, password, parent_responsible)
                VALUES (:id, :parent_id, :name, :login, :password, :parent_responsible)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($data);
    }

    public function deleteChild(string $childId, string $parentId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM learn4kids_children
             WHERE id = :id AND parent_id = :parent_id"
        );

        return $stmt->execute([
            'id' => $childId,
            'parent_id' => $parentId
        ]);
    }
}