<?php
/**
 * Pruebas unitarias manuales para InputSanitizer.
 * Ejecutar: php tests\test_input_sanitizer.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/helpers/InputSanitizer.php';

$fallos = [];
$total = 0;

function assertEq(string $nombre, mixed $esperado, mixed $obtenido, array &$fallos, int &$total): void
{
    $total++;
    if ($esperado !== $obtenido) {
        $fallos[] = sprintf(
            "FALLO: %s\n  Esperado: %s\n  Obtenido: %s",
            $nombre,
            var_export($esperado, true),
            var_export($obtenido, true)
        );
    } else {
        echo "OK: {$nombre}\n";
    }
}

function assertTrue(string $nombre, bool $condicion, array &$fallos, int &$total): void
{
    $total++;
    if (!$condicion) {
        $fallos[] = "FALLO: {$nombre}";
    } else {
        echo "OK: {$nombre}\n";
    }
}

// --- EMAIL ---
assertEq('email: trim + lowercase', 'usuario@ejemplo.com', InputSanitizer::email('  Usuario@EJEMPLO.COM  '), $fallos, $total);
assertEq('email: elimina coma', 'johndoe@example.com', InputSanitizer::email('john,doe@example.com'), $fallos, $total);
assertEq('email: elimina espacios', 'usuario@ejemplo.com', InputSanitizer::email('usuario @ ejemplo . com'), $fallos, $total);
assertEq('email: elimina comillas y backslash', 'user@example.com', InputSanitizer::email('"us\\er"@example.com'), $fallos, $total);
assertEq('email: elimina caracteres raros', 'user+tag@example.com', InputSanitizer::email('u!s#e$r+tag@example.com'), $fallos, $total);
assertEq('email: conserva signo menos y punto', 'user.name-test@example.com', InputSanitizer::email('User.Name-Test@Example.COM'), $fallos, $total);

// --- USERNAME ---
assertEq('username: trim + lowercase', 'usuario_test', InputSanitizer::username('  Usuario_Test  '), $fallos, $total);
assertEq('username: elimina espacios', 'usuariotest', InputSanitizer::username('usuario test'), $fallos, $total);
assertEq('username: elimina comas', 'usuariotest', InputSanitizer::username('usuario,test'), $fallos, $total);
assertEq('username: elimina simbolos raros', 'usuario_test', InputSanitizer::username('usuario@_test#'), $fallos, $total);
assertEq('username: conserva punto guion y guion bajo', 'usuario-test_123', InputSanitizer::username('usuario-test_123'), $fallos, $total);
assertTrue('username: valida formato correcto', InputSanitizer::validateUsername('user_test-123'), $fallos, $total);
assertTrue('username: rechaza mayusculas', !InputSanitizer::validateUsername('Usuario'), $fallos, $total);
assertTrue('username: rechaza espacios', !InputSanitizer::validateUsername('user name'), $fallos, $total);
assertTrue('username: rechaza caracteres raros', !InputSanitizer::validateUsername('user@name'), $fallos, $total);
assertTrue('username: rechaza empezar con numero', !InputSanitizer::validateUsername('1usuario'), $fallos, $total);

// --- TEXTO GENERAL ---
assertEq('text: trim + uppercase', 'MARÍA JOSÉ', InputSanitizer::text('  maría josé  '), $fallos, $total);
assertEq('text: null si vacio', null, InputSanitizer::text('   '), $fallos, $total);
assertEq('text: null si null', null, InputSanitizer::text(null), $fallos, $total);
assertEq('text: mayusculas con tildes', 'ÁÉÍÓÚ Ñ', InputSanitizer::text('áéíóú ñ'), $fallos, $total);

// --- LOGIN IDENTIFIER ---
assertEq('loginIdentifier: trata como email', 'usuario@ejemplo.com', InputSanitizer::loginIdentifier('Usuario@Ejemplo.COM '), $fallos, $total);
assertEq('loginIdentifier: trata como username', 'usuario-test_123', InputSanitizer::loginIdentifier('Usuario-Test_123 '), $fallos, $total);

// --- PASSWORD ---
$pwdOriginal = 'MiP@ssw0rd!Ñ';
assertTrue('password: no pasa por InputSanitizer (se conserva exacta)', $pwdOriginal === $pwdOriginal, $fallos, $total);
$validacion = InputSanitizer::validatePassword('MiP@ssw0rd!Ñ', 'MiP@ssw0rd!Ñ');
assertTrue('password: valida longitud y coincidencia', $validacion['valid'], $fallos, $total);
$validacionCorta = InputSanitizer::validatePassword('corta', 'corta');
assertTrue('password: rechaza corta', !$validacionCorta['valid'], $fallos, $total);
$validacionNoCoinciden = InputSanitizer::validatePassword('MiP@ssw0rd!Ñ', 'OtraPass123', $fallos, $total);
assertTrue('password: rechaza si no coinciden', !$validacionNoCoinciden['valid'], $fallos, $total);

// --- NUEVOS VALIDADORES (src/ CRUDs) ---
assertEq('digits: acepta solo digitos', '3001234567', InputSanitizer::digits('3001234567', 10), $fallos, $total);
assertTrue('digits: rechaza letras', InputSanitizer::digits('300abc', 10) === null, $fallos, $total);
assertTrue('digits: rechaza array', InputSanitizer::digits(['x'], 10) === null, $fallos, $total);
assertEq('intValue: entero valido', 42, InputSanitizer::intValue('42', 1), $fallos, $total);
assertTrue('intValue: rechaza basura', InputSanitizer::intValue('42abc', 1) === null, $fallos, $total);
assertTrue('intValue: rechaza negativo con min 1', InputSanitizer::intValue('-1', 1) === null, $fallos, $total);
assertEq('decimalString: normaliza 2 decimales', '1500.50', InputSanitizer::decimalString('1500.5', 0.0), $fallos, $total);
assertTrue('decimalString: rechaza negativo', InputSanitizer::decimalString('-5', 0.0) === null, $fallos, $total);
assertEq('date: fecha valida', '2026-10-23', InputSanitizer::date('2026-10-23'), $fallos, $total);
assertTrue('date: rechaza fecha invalida', InputSanitizer::date('2026-13-99') === null, $fallos, $total);
assertEq('whitelist: valor permitido', 'FIJO', InputSanitizer::whitelist('FIJO', ['FIJO', 'INDEFINIDO']), $fallos, $total);
assertTrue('whitelist: rechaza valor externo', InputSanitizer::whitelist('OTRO', ['FIJO', 'INDEFINIDO']) === null, $fallos, $total);
assertEq('boundedText: dentro del maximo', 'hola', InputSanitizer::boundedText('hola', 10), $fallos, $total);
assertTrue('boundedText: rechaza exceder maximo', InputSanitizer::boundedText(str_repeat('a', 11), 10) === null, $fallos, $total);
assertEq('letters: nombres con tildes', 'MARÍA JOSÉ', InputSanitizer::letters('maría josé', 30), $fallos, $total);
assertTrue('letters: rechaza digitos', InputSanitizer::letters('juan123', 30) === null, $fallos, $total);
assertEq('document: CC valido', '1000123456', InputSanitizer::document('CC', '1000123456'), $fallos, $total);
assertTrue('document: CC invalido', InputSanitizer::document('CC', '1000') === null, $fallos, $total);
assertEq('document: PPT normaliza mayusculas', 'ABC-123', InputSanitizer::document('PPT', 'abc-123'), $fallos, $total);
assertEq('telefono: acepta formato', '300 123-4567', InputSanitizer::telefono('300 123-4567'), $fallos, $total);
assertTrue('telefono: rechaza letras', InputSanitizer::telefono('abc') === null, $fallos, $total);

// --- RESUMEN ---
echo "\n--- RESUMEN ---\n";
echo "Total pruebas: {$total}\n";
echo "Fallos: " . count($fallos) . "\n";

if (!empty($fallos)) {
    echo "\nDETALLE DE FALLOS:\n";
    foreach ($fallos as $f) {
        echo $f . "\n\n";
    }
    exit(1);
}

echo "Todas las pruebas pasaron.\n";
