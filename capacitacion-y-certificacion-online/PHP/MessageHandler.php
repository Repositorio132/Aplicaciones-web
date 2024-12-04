<?php


class MessageHandler {
    private static $errors = [];
    private static $success = '';

    // Agregar error
    public static function addError($message) {
        self::$errors[] = $message;
    }

    // Obtener todos los errores
    public static function getErrors() {
        return self::$errors;
    }

    // Verificar si hay errores
    public static function hasErrors() {
        return !empty(self::$errors);
    }

    // Establecer mensaje de éxito
    public static function setSuccess($message) {
        self::$success = $message;
    }

    // Obtener el mensaje de éxito
    public static function getSuccess() {
        return self::$success;
    }

    // Limpiar los mensajes
    public static function clearMessages() {
        self::$errors = [];
        self::$success = '';
    }
}
?>
