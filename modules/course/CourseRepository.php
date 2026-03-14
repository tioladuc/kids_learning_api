<?php

class CourseRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ============================================
    // PENDING COURSES (picked but not paid)
    // ============================================

    public function getChildPendingCourses(string $childId): array
    {
        $stmt = $this->db->prepare(
            "SELECT cc.id, c.code, c.name, c.amount, c.validity, c.description,
                    cc.picked_date
             FROM learn4kids_child_courses cc
             INNER JOIN learn4kids_courses c ON cc.course_code = c.code
             WHERE cc.child_id = :child_id
             AND cc.is_paid = 0"
        );

        $stmt->execute(['child_id' => $childId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================
    // ACTIVE COURSES (paid and not expired)
    // ============================================

    public function getChildPickCourses(string $childId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM learn4kids_courses
             WHERE code NOT IN (
                 SELECT course_code
                 FROM learn4kids_child_courses
                 WHERE child_id = :child_id
             )"
        );

        $stmt->execute(['child_id' => $childId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================
    // AVAILABLE COURSES (not yet picked)
    // ============================================

    public function getAvailableCourses(string $childId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM learn4kids_courses
             WHERE code IN (
                 SELECT course_code
                 FROM learn4kids_child_courses
                 WHERE child_id = :child_id AND is_paid = 1
             )"
        );
        
        $stmt->execute(['child_id' => $childId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================================
    // PICK COURSE
    // ============================================

    public function pickCourse(string $childId, string $courseCode): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO learn4kids_child_courses
             (child_id, course_code, is_paid, picked_date)
             VALUES (:child_id, :course_code, 0, NOW())"
        );

        return $stmt->execute([
            'child_id' => $childId,
            'course_code' => $courseCode
        ]);
    }

    // ============================================
    // REMOVE COURSE
    // ============================================

    public function removeCourse(string $childId, string $courseCode): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM learn4kids_child_courses
             WHERE child_id = :child_id AND course_code = :course_code "
        );

        return $stmt->execute([
            'child_id' => $childId,
            'course_code' => $courseCode
        ]);
    }

    // ============================================
    // PAY COURSE
    // ============================================

    public function payCourse(string $childId, string $courseCode, float $amount, string $parentId): bool
    {
        $this->db->beginTransaction();

        try {

            // update child course
            $update = $this->db->prepare(
                "UPDATE learn4kids_child_courses
                 SET is_paid = 1,
                     expiry_date = DATE_ADD(NOW(), INTERVAL 30 DAY)
                 WHERE child_id = :child_id
                 AND course_code = :course_code"
            );

            $update->execute([
                'child_id' => $childId,
                'course_code' => $courseCode
            ]);

            // insert payment record
            $insert = $this->db->prepare(
                "INSERT INTO learn4kids_payments
                 (parent_id, child_id, course_code, amount, is_paid, payment_date)
                 VALUES (:parent_id, :child_id, :course_code, :amount, 1, NOW())"
            );

            $insert->execute([
                'parent_id' => $parentId,
                'child_id' => $childId,
                'course_code' => $courseCode,
                'amount' => $amount
            ]);
            print_r([
                'parent_id' => $parentId,
                'child_id' => $childId,
                'course_code' => $courseCode,
                'amount' => $amount
            ]);
            //$this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
