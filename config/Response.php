<?php

class Response {
    public static function json($data, $status_code = 200) {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function success($data = [], $message = "Success", $status_code = 200) {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status_code);
    }

    public static function error($message = "Error", $status_code = 400, $errors = []) {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status_code);
    }

    public static function unauthorized($message = "Unauthorized") {
        self::error($message, 401);
    }

    public static function forbidden($message = "Forbidden") {
        self::error($message, 403);
    }

    public static function notFound($message = "Not found") {
        self::error($message, 404);
    }
}
