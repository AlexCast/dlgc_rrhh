<?php
/**
 * conexion.php
 * Archivo de conexión segura a la base de datos MariaDB utilizando PDO.
 *
 * Buenas prácticas aplicadas:
 * - Credenciales en variables (idealmente deberían venir de variables de entorno,
 *   no de un archivo versionado en el repositorio).
 * - PDO::ERRMODE_EXCEPTION para que cualquier error de SQL lance una excepción
 *   en lugar de fallar silenciosamente.
 * - PDO::FETCH_ASSOC como modo de recuperación por defecto (arreglos asociativos).
 * - Manejo de errores mediante try-catch, devolviendo un mensaje genérico
 *   al usuario final y registrando el detalle real solo en el log del servidor.
 */

require_once __DIR__ . '/helpers/EnvLoader.php';
load_environment_file(dirname(__DIR__) . '/.env');

// --- Parámetros de conexión ---
$host   = getenv('DB_HOST') ?: '127.0.0.1';  // Host del servidor MariaDB
$port   = getenv('DB_PORT') ?: '3306';       // Puerto por defecto de MariaDB
$dbname = getenv('DB_NAME') ?: 'db_dlgc_rrhh';  // Nombre de la base de datos
$user   = getenv('DB_USER') ?: 'root';   // Usuario de la base de datos
$pass   = getenv('DB_PASSWORD') ?: '0149';       // Contraseña del usuario

// --- Cadena DSN (Data Source Name) para MariaDB ---
$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

// --- Opciones de configuración de PDO ---
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones ante errores
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Devuelve arreglos asociativos por defecto
    PDO::ATTR_EMULATE_PREPARES   => false,                   // Usa sentencias preparadas nativas (más seguro)
];

try {
    // Se crea la instancia de conexión PDO
    $conexion = new PDO($dsn, $user, $pass, $options);

    // Propagar el usuario real de la aplicación a MariaDB para las auditorías.
    // Solo si ya hay sesión activa; si no, dejamos que MariaDB use CURRENT_USER.
    if (session_status() === PHP_SESSION_ACTIVE) {
        $actorAuditoria = $_SESSION['nombre_completo'] ?? $_SESSION['username'] ?? '';
        $actorAuditoria = trim((string) $actorAuditoria);
        if ($actorAuditoria !== '') {
            try {
                $sentenciaAuditoria = $conexion->prepare('SET @app_current_user = ?');
                $sentenciaAuditoria->execute([$actorAuditoria]);
            } catch (PDOException $eAuditoria) {
                // No debe romper la aplicación; solo registramos el error para diagnóstico.
                error_log('Error al configurar @app_current_user: ' . $eAuditoria->getMessage());
            }
        }
    }
} catch (PDOException $e) {
    // Se registra el error real en el log del servidor (no se muestra al usuario)
    error_log('Error de conexión a la base de datos: ' . $e->getMessage());


    // Se devuelve un mensaje genérico, sin exponer credenciales ni detalles internos
    http_response_code(500);
    die('No se pudo establecer conexión con la base de datos. Intente más tarde.');
}
