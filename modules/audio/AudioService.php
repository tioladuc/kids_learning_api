<?php


class AudioService
{
    private AudioRepository $repo;
    private string $uploadPath = __DIR__ . '/../../main_audio/';

    public function __construct(AudioRepository $repo)
    {
        $this->repo = $repo;
    }

    // ===============================
    // ADD AUDIO
    // ===============================

    public function addAudio(array $post, array $files): array
    {
        if (!isset($files['audio'])) {
            throw new Exception("Audio file is required");
        }

        $filePath = $this->handleUpload($files['audio']);

        $data = [
            'child_id' => $post['child_id'],
            'title' => $post['title'],
            'description' => $post['description'] ?? null,
            'audio_url' => $filePath
        ];

        $this->repo->addAudio($data);

        return ["message" => "Audio uploaded successfully"];
    }

    // ===============================
    // UPDATE AUDIO
    // ===============================

    public function updateAudio(array $post, array $files): array
    {
        $audio = $this->repo->getAudioById((int)$post['id']);

        if (!$audio) {
            throw new Exception("Audio not found");
        }

        $filePath = $audio['audio_url'];

        if (isset($files['audio'])) {

            // Delete old file
            $oldFile = __DIR__ . '/../../' . $audio['audio_url'];
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }

            $filePath = $this->handleUpload($files['audio']);
        }

        $data = [
            'id' => $post['id'],
            'title' => $post['title'],
            'description' => $post['description'] ?? null,
            'audio_url' => $filePath
        ];

        $this->repo->updateAudio($data);

        return ["message" => "Audio updated successfully"];
    }

    // ===============================
    // DELETE AUDIO
    // ===============================

    public function deleteAudio(int $id): array
    {
        $audio = $this->repo->getAudioById($id);

        if (!$audio) {
            throw new Exception("Audio not found");
        }

        $filePath = __DIR__ . '/../../' . $audio['audio_url'];

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->repo->deleteAudio($id);

        return ["message" => "Audio deleted successfully"];
    }
    
    public function loadAudios(array $post): array
    {
        
        $childId = $post['child_id'] ?? null;
        
        if (!$childId) {
            throw new Exception("child_id is required");
        }
        return $this->repo->loadAudios($childId);
    }

    // ===============================
    // HANDLE UPLOAD
    // ===============================
    private function handleUpload(?array $file = null): string
{
    $allowedTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg', 'application/octet-stream'];

    // -----------------------------------
    // CASE 1: multipart/form-data upload
    // -----------------------------------
    if ($file !== null) {

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error");
        }

        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception("Invalid audio format = " . $file['type']);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = uniqid("audio_") . "." . $extension;

        $destination = $this->uploadPath . $newName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception("Failed to move uploaded file");
        }

        return "main_audio/" . $newName;
    }

    // -----------------------------------
    // CASE 2: application/octet-stream
    // -----------------------------------
    $rawAudio = file_get_contents("php://input");

    if (!$rawAudio) {
        throw new Exception("No audio data received");
    }

    // detect mime type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->buffer($rawAudio);

    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception("Invalid audio format = " . $mimeType);
    }

    // determine extension
    $extensions = [
        'audio/mpeg' => 'mp3',
        'audio/mp3' => 'mp3',
        'audio/wav' => 'wav',
        'audio/ogg' => 'ogg'
    ];

    $extension = $extensions[$mimeType] ?? 'bin';

    $newName = uniqid("audio_") . "." . $extension;

    $destination = $this->uploadPath . $newName;

    if (file_put_contents($destination, $rawAudio) === false) {
        throw new Exception("Failed to save audio file");
    }

    return "main_audio/" . $newName;
}
    /*private function handleUpload(array $file): string
    {
        $allowedTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg', 'application/octet-stream'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception("Invalid audio format = " . $file['type']);
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error");
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = uniqid("audio_") . "." . $extension;

        $destination = $this->uploadPath . $newName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception("Failed to move uploaded file");
        }

        return "main_audio/" . $newName;
    }*/
}
