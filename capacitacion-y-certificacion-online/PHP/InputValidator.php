<?php
// Clase para sanear las entradas del formulario
class InputValidator {

    // Función para sanitizar los datos de entrada
    public static function sanitizeInput($input) {
        // Elimina espacios en blanco al principio y al final
        $input = trim($input);
        // Convierte caracteres especiales en entidades HTML para evitar inyección de código
        $input = htmlspecialchars($input);
        // Elimina caracteres no deseados (opcional)
        $input = filter_var($input, FILTER_SANITIZE_STRING);
        return $input;
    }
}
?>
