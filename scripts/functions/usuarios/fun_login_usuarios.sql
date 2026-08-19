-- Función de apoyo para el proceso de autenticación (app/login.php).
-- A diferencia de los fun_listar_* del resto del CRUD (que solo hacen
-- RAISE NOTICE y devuelven BOOLEAN), aquí sí se necesita devolver el
-- registro real a PHP para poder validar el hash de la contraseña con
-- password_verify(), por lo que se define RETURNS TABLE.
DROP FUNCTION IF EXISTS fun_login_usuarios(VARCHAR);

CREATE OR REPLACE FUNCTION fun_login_usuarios(widentificador VARCHAR)
RETURNS TABLE (
    id_usuario        t_usuarios.id_usuario%TYPE,
    username          t_usuarios.username%TYPE,
    id_rol            t_usuarios.id_rol%TYPE,
    primer_nombre     t_usuarios.primer_nombre%TYPE,
    primer_apellido   t_usuarios.primer_apellido%TYPE,
    correo            t_usuarios.correo%TYPE,
    correo_verificado t_usuarios.correo_verificado%TYPE,
    contrasena        t_usuarios.contrasena%TYPE
) AS
$$
BEGIN
    RETURN QUERY
    SELECT u.id_usuario, u.username, u.id_rol, u.primer_nombre, u.primer_apellido, u.correo, u.correo_verificado, u.contrasena
    FROM t_usuarios u
    WHERE (u.username = widentificador OR u.correo = widentificador)
        AND u.fec_delete IS NULL;
END;
$$
LANGUAGE PLPGSQL;
