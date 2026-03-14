<?php

class StatisticsRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function loadVisitedCourses(string $childId): array
    {
        $sql = "
            SELECT 
                v.id,
                v.child_id,
                v.course_code,
                v.id_item_course,
                v.time_spent,
                v.last_connection,
                c.name AS course_name
            FROM learn4kids_visited_courses v
            LEFT JOIN learn4kids_courses c
                ON v.course_code = c.code
            WHERE v.child_id = :child_id
            ORDER BY v.last_connection DESC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':child_id' => $childId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}