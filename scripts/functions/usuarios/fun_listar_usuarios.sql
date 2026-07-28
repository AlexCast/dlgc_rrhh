CREATE OR REPLACE FUNCTION fun_listar_usuarios(wid_usuario t_usuarios.id_usuario%TYPE) 
RETURNS BOOLEAN AS
$$
DECLARE 
    wreg_usr t_usuarios%ROWTYPE;
BEGIN
    IF wid_usuario IS NULL OR wid_usuario = '' THEN
        RAISE NOTICE 'El ID del usuario no puede ser nulo o vacío.';
        RETURN FALSE;
    END IF;

    SELECT id_usuario, id_rol, username, tipo_documento, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, correo
    INTO wreg_usr
    FROM t_usuarios
        WHERE id_usuario = wid_usuario
            AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'El usuario con ID % no existe.', wid_usuario;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Rol: %, Usuario: %, Documento: %, Nombre: % %, Apellido: % %, Correo: %',
        wreg_usr.id_usuario,
        wreg_usr.id_rol,
        wreg_usr.username,
        wreg_usr.tipo_documento,
        wreg_usr.primer_nombre,
        wreg_usr.segundo_nombre,
        wreg_usr.primer_apellido,
        wreg_usr.segundo_apellido,
        wreg_usr.correo;
    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;
