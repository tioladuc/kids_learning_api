<?php

class Response {

    public static function success($data = null, $message = null) {
        echo json_encode([
            'success' => true,
            'data' => $data,
            'message' => $message
        ]);
    }

    public static function error($message) {
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
    }

}