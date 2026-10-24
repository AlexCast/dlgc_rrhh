<?php
/**
 * InputSanitizer.php
 * Helper centralizado para normalización y sanitización de entradas de usuario.
 *
 * Reglas aplicadas:
 *  - username / email: trim + minúsculas + eliminación de caracteres no permitidos.
 *  - texto general: trim + mayúsculas (UTF-8).
 *  - contraseñas: se preservan tal cual (sin normalización de caso).
 */

declare(strict_types=1);

class InputSanitizer
{
    /**
     * Normaliza un correo electrónico: trim, minúsculas y elimina espacios,
     * comas, comillas, backslashes y cualquier carácter fuera del whitelist
     * permitido para emails ([a-z0-9._%+-@]).
     */
    public static function email(string $email): string
    {
        $email = trim($email);
        $email = mb_strtolower($email, 'UTF-8');
        $email = preg_replace('/[^a-z0-9._%+\-@]/', '', $email);
        return $email;
    }

    /**
     * Normaliza un nombre de usuario: trim, minúsculas y elimina cualquier
     * carácter que no sea alfanumérico, guión bajo, punto o guión.
     */
    public static function username(string $username): string
    {
        $username = trim($username);
        $username = mb_strtolower($username, 'UTF-8');
        $username = preg_replace('/[^a-z0-9_.-]/', '', $username);
        return $username;
    }

    /**
     * Normaliza un identificador de login (puede ser username o email).
     * Si contiene '@' se trata como email; de lo contrario como username.
     */
    public static function loginIdentifier(string $input): string
    {
        $input = trim($input);
        return str_contains($input, '@')
            ? self::email($input)
            : self::username($input);
    }

    /**
     * Normaliza texto general: trim + mayúsculas UTF-8. Devuelve null si
     * después del trim el valor queda vacío, útil para campos opcionales.
     */
    public static function text(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $text = trim($text);

        if ($text === '') {
            return null;
        }

        return mb_strtoupper($text, 'UTF-8');
    }

    /**
     * Valida formato estándar de correo electrónico.
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida un nombre de usuario normalizado.
     * Debe comenzar con letra, tener entre 3 y 30 caracteres y contener solo
     * letras minúsculas, números, guiones bajos, puntos o guiones.
     */
    public static function validateUsername(string $username): bool
    {
        return (bool) preg_match('/^[a-z][a-z0-9_.-]{2,29}$/', $username);
    }

    /**
     * Valida que la contraseña y su confirmación cumplan los estándares actuales.
     *
     * @return array{valid: bool, errors: string[]}
     */
    public static function validatePassword(string $password, string $confirmPassword): array
    {
        $errors = [];

        if (strlen($password) < 8 || strlen($password) > 255) {
            $errors[] = 'La contraseña debe tener entre 8 y 255 caracteres.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Las contraseñas no coinciden.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Valida una cadena que debe contener únicamente dígitos.
     * Devuelve null si está vacía, supera el largo máximo o contiene no-dígitos.
     */
    public static function digits(mixed $value, int $maxLength = 0): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || preg_match('/^[0-9]+$/', $value) !== 1) {
            return null;
        }

        if ($maxLength > 0 && strlen($value) > $maxLength) {
            return null;
        }

        return $value;
    }

    /**
     * Valida un número de teléfono: guión, paréntesis, espacio o '+' opcional seguido de dígitos.
     */
    public static function telefono(mixed $value, int $maxLength = 15): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || preg_match('/^\+?[0-9()\- ]{6,}$/', $value) !== 1 || strlen($value) > $maxLength) {
            return null;
        }

        return $value;
    }

    /**
     * Valida un entero (opcionalmente acotado). Devuelve null si no es válido.
     */
    public static function intValue(mixed $value, ?int $min = null, ?int $max = null): ?int
    {
        $filtered = filter_var($value, FILTER_VALIDATE_INT);

        if ($filtered === false) {
            return null;
        }

        if ($min !== null && $filtered < $min) {
            return null;
        }

        if ($max !== null && $filtered > $max) {
            return null;
        }

        return $filtered;
    }

    /**
     * Valida un número decimal (acepta int o string numérico) y lo devuelve
     * normalizado con dos decimales. Devuelve null si no es numérico o se sale de rango.
     */
    public static function decimalString(mixed $value, ?float $min = null, ?float $max = null): ?string
    {
        if (is_int($value)) {
            $numero = (float) $value;
        } elseif (is_string($value) && is_numeric(trim($value))) {
            $numero = (float) trim($value);
        } else {
            return null;
        }

        if ($min !== null && $numero < $min) {
            return null;
        }

        if ($max !== null && $numero > $max) {
            return null;
        }

        return number_format($numero, 2, '.', '');
    }

    /**
     * Valida una fecha real en formato Y-m-d. Devuelve null si no lo es.
     */
    public static function date(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $fecha = DateTime::createFromFormat('!Y-m-d', $value);

        if ($fecha === false || $fecha->format('Y-m-d') !== $value) {
            return null;
        }

        return $value;
    }

    /**
     * Valida que el valor pertenezca a una lista cerrada (whitelist).
     */
    public static function whitelist(mixed $value, array $allowed): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return in_array($value, $allowed, true) ? $value : null;
    }

    /**
     * Normaliza texto libre acotando su longitud. Devuelve null si queda vacío o excede el máximo.
     */
    public static function boundedText(mixed $value, int $maxLength, bool $uppercase = false): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || mb_strlen($value, 'UTF-8') > $maxLength) {
            return null;
        }

        return $uppercase ? mb_strtoupper($value, 'UTF-8') : $value;
    }

    /**
     * Valida un nombre propio: solo letras (incluye tildes/ñ), espacios,
     * apóstrofes, guiones y puntos. Devuelve null si no cumple.
     */
    public static function letters(mixed $value, int $maxLength): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || mb_strlen($value, 'UTF-8') > $maxLength) {
            return null;
        }

        if (preg_match("/^[\p{L}][\p{L}\s'.\-]*$/u", $value) !== 1) {
            return null;
        }

        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Valida un número de documento según el tipo ('CC', 'PPT' o 'CE').
     * Normaliza a mayúsculas y aplica el mismo patrón que el CHECK de la base de datos.
     */
    public static function document(mixed $tipo, mixed $numero): ?string
    {
        if (!is_string($tipo) || !is_string($numero)) {
            return null;
        }

        $numero = mb_strtoupper(trim($numero), 'UTF-8');

        $patrones = [
            'CC'  => '/^[0-9]{10}$/',
            'PPT' => '/^[A-Z0-9\-]{5,20}$/',
            'CE'  => '/^[A-Z0-9]{5,20}$/',
        ];

        if (!isset($patrones[$tipo])) {
            return null;
        }

        return preg_match($patrones[$tipo], $numero) === 1 ? $numero : null;
    }
}
