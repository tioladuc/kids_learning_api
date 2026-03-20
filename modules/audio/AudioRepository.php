<?php

class AudioRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function addAudio(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO learn4kids_audios
             (child_id, title, description, audio_url)
             VALUES (:child_id, :title, :description, :audio_url)"
        );

        return $stmt->execute($data);
    }

    public function updateAudio(array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE learn4kids_audios
             SET title = :title,
                 description = :description,
                 audio_url = :audio_url
             WHERE id = :id"
        );

        return $stmt->execute($data);
    }

    public function deleteAudio(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM learn4kids_audios WHERE id = :id"
        );

        return $stmt->execute(['id' => $id]);
    }

    public function getAudioById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM learn4kids_audios WHERE id = :id LIMIT 1"
        );
	//echo "SELECT * FROM learn4kids_audios WHERE id = ". $id ." LIMIT 1";
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    public function loadAudios(string $childId): array
    {
        $sql = "SELECT 
                    id,
                    child_id,
                    title,
                    description,
                    '". $GLOBALS["URL_BASE"] ."' as base_url,
                    audio_url,
                    created_at
                FROM learn4kids_audios
                WHERE child_id = :child_id
                ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':child_id' => $childId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
