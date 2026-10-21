<?php
/**
 * conexion.php
 * Archivo de conexión segura a la base de datos MariaDB utilizando PDO.
 *
 * Buenas prácticas aplicadas:
 * - Las credenciales se leen exclusivamente de .env (fuera de control de versiones).
 *   Si falta una variable obligatoria se registra en el log y se corta la ejecución
 *   con un mensaje genérico, en lugar de conectarse con valores por defecto.
 * - PDO::ERRMODE_EXCEPTION para que cualquier error de SQL lance una excepción
 *   en lugar de fallar silenciosamente.
 * - PDO::FETCH_ASSOC como modo de recuperación por defecto (arreglos asociativos).
 * - Manejo de errores mediante try-catch, devolviendo un mensaje genérico
 *   al usuario final y registrando el detalle real solo en el log del servidor.
 */

require_once __DIR__ . '/helpers/EnvLoader.php';
load_environment_file(dirname(__DIR__) . '/.env');

// --- Parámetros de conexión (obligatorios en .env) ---
// Se usa una closure para no declarar funciones globales desde un archivo incluido.
$leerVariableObligatoria = static function (string $nombre): string {
    $valor = getenv($nombre);

    if ($valor === false || trim($valor) === '') {
        error_log("Configuración de base de datos incompleta: falta {$nombre} en .env");
        http_response_code(500);
        die('No se pudo establecer conexión con la base de datos. Intente más tarde.');
    }

    return trim($valor);
};

$host   = $leerVariableObligatoria('DB_HOST');   // Host del servidor MariaDB
$dbname = $leerVariableObligatoria('DB_NAME');   // Nombre de la base de datos
$user   = $leerVariableObligatoria('DB_USER');   // Usuario de la base de datos
$pass   = $leerVariableObligatoria('DB_PASSWORD'); // Contraseña del usuario

// El puerto no es un secreto: si no se define se asume el de MariaDB.
$port = getenv('DB_PORT');
$port = ($port === false || trim($port) === '') ? '3306' : trim($port);

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
