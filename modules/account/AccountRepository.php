<?php

class AccountRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function updateSecretCode($id, $isChild) {
        $query = "update ".($isChild ? "learn4kids_children" : "learn4kids_parents")." set codesecret='". uniqid() ."' where id = '$id'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
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
                (id, first_name, last_name, login, password, email, is_active, activation_code, codeparent, codesecret)
                VALUES (:id, :first_name, :last_name, :login, :password, :email, 1, NULL, :codeparent, :codesecret)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($data);
    }

    public function findParentByLogin(string $login, string $codeparent)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM learn4kids_parents WHERE login = :login and codeparent = :codeparent"
        );
        $stmt->execute(['login' => $login, 'codeparent' => $codeparent]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findChildByLogin(string $login, string $codeparent)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM learn4kids_children WHERE login = :login and codeparent = :codeparent"
        );
        $stmt->execute(['login' => $login, 'codeparent' => $codeparent]);

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
                c.passwordraw,
                c.codeparent,
                c.codesecret,
                c.level,
                p.first_name AS parent_first_name,
                p.last_name AS parent_last_name,
                p.email AS parent_email,
                p.codeparent as parent_codeparent,
                p.codesecret as parent_codesecret
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
                passwordraw,
                parent_responsible,
                codeparent,
                codesecret,
                level
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
                activation_code,
                codeparent,
                codesecret
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
    public function changePasswordParentChild($data) {
        $sql = "UPDATE learn4kids_children SET 
                        name = :name, password = :password, 
                        passwordraw = :passwordraw, 
                        level = :level, login= :login 
                        WHERE id = :child_id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($data);
    }
    public function addChild(array $data): bool
    {
        $sql = "INSERT INTO learn4kids_children
                (id, parent_id, name, login, password, passwordraw, parent_responsible, codeparent, level)
                VALUES (:id, :parent_id, :name, :login, :password, :passwordraw, :parent_responsible, :codeparent, :level)";

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

    public function loadPayment(string $parentId)
    {
        $stmt = $this->db->prepare(
            "SELECT 	


            pay.payment_date as date,
            pay.amount as amount,
            course.name as courseName,
            child.name as childName,
            pay.is_paid as isPaid,
            
            pay.id,
            pay.parent_id,
            pay.child_id,
            pay.course_code,
            child.id as childid,
            child.parent_id as parentid,
            child.login as childlogin,
            child.password as password,
            child.parent_responsible,
            child.codeparent,
            child.codesecret,
            course.code as codecourse,
            course.amount as courseamount,
            course.validity as coursevalidity,
            course.description as coursedescription,
            course.url as courseurl
            
            
            FROM learn4kids_payments pay, 
            learn4kids_children child,
            learn4kids_courses course
            
            WHERE pay.parent_id = '$parentId' AND
            child.id = pay.child_id AND 
            course.code = pay.course_code
            
            UNION
            
            SELECT 
            IFNULL(pay.expiry_date, pay.picked_date) as date,
            course.amount as amount,
            course.name as courseName,
            child.name as childName,
            0 as isPaid,

            pay.id,
            child.parent_id as parent_id,
            pay.child_id,
            course.code as course_code,
            child.id as childid,
            child.parent_id as parentid,
            child.login as childlogin,
            child.password as password,
            child.parent_responsible,
            child.codeparent,
            child.codesecret,
            course.code as codecourse,
            course.amount as courseamount,
            course.validity as coursevalidity,
            course.description as coursedescription,
            course.url as courseurl


            FROM learn4kids_child_courses pay, 
            learn4kids_children child,
            learn4kids_courses course

            WHERE child.parent_id = '$parentId' AND 
            IFNULL(pay.expiry_date, pay.picked_date) <DATE_ADD(NOW(), INTERVAL 14 DAY) AND
            child.id = pay.child_id AND 
            course.code = pay.course_code
            "
        );
        
        $stmt->execute([]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function loadLevels() {
        $query = "SELECT * FROM learn4kids_level ORDER BY libelle";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function sendActivationCodeParent($email): bool
    {
        $queryGetParent = "SELECT * FROM learn4kids_parents WHERE email like '$email'";
        $stmt = $this->db->prepare($queryGetParent);
        $stmt->execute([]);
        $parent = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]; //print_r($parent);

        $code = random_int(100000, 999999);
        $sql = "UPDATE learn4kids_parents SET activation_code = '$code', is_active=1 where email like '$email'";
        $stmt = $this->db->prepare($sql);
        $resultat = $stmt->execute([]);

        $to = $email; //"tioladuc@gmail.com";
        $subject = "Verification Code - " . $this->getApplicationName() ;
        $message = "Here is your code and other parameter to reset your parent account 
                    <br/><br/><b>Your verification code is:</b> " . $code;
        $message .= "<br/><br/><b>Your parent's code is:</b> " . $parent['codeparent'];
        $message .= "<br/><br/><b>Your login  is:</b> " . $parent['login'];

        $headers = "From: ". $this->getSenderEmail() ."\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if(mail($to, $subject, $message, $headers)){
            //echo "Email sent";
        }else{
            //echo "Email failed";
        }

        return $resultat;
    }

    public function resetParentPassword($input): bool
    {
        $email = $input['email'];
        $code = $input['code'];
        $password = password_hash($input['new_password'], PASSWORD_BCRYPT);
        $sql = "UPDATE learn4kids_parents SET activation_code = null, password = '$password', is_active=1 where email like '$email' AND activation_code = '$code'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([]);
    }

    ////////////////////////////////////////////////////////
    ////////////////CONFIGURATION/////////////////////
    ////////////////////////////////////////////////////////
    public function getApplicationName() {
        return 'Learn4Kids';
    }
    public function getSenderEmail() {
        return 'bym-quiz@yehoshoualevivant.com';
    }
}