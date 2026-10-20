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
}
