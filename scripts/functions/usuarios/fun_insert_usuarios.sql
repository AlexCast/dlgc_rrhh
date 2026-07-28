-- La contraseña (wcontrasena) debe llegar ya hasheada desde la aplicación
-- (PHP password_hash), esta función solo la persiste, nunca la calcula.
CREATE OR REPLACE FUNCTION fun_insert_usuarios(
    wid_usuario t_usuarios.id_usuario%TYPE,
    wid_rol t_usuarios.id_rol%TYPE,
    wusername t_usuarios.username%TYPE,
    wtipo_documento t_usuarios.tipo_documento%TYPE,
    wprimer_nombre t_usuarios.primer_nombre%TYPE,
    wprimer_apellido t_usuarios.primer_apellido%TYPE,
    wcorreo t_usuarios.correo%TYPE,
    wcontrasena t_usuarios.contrasena%TYPE,
    wsegundo_nombre t_usuarios.segundo_nombre%TYPE DEFAULT NULL,
    wsegundo_apellido t_usuarios.segundo_apellido%TYPE DEFAULT NULL
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wid_usuario IS NULL OR wid_usuario = '' THEN
        RETURN 'El id_usuario no puede ser nulo o vacío.';
    END IF;
    IF wid_rol IS NULL OR wid_rol <= 0 THEN
        RETURN 'El id_rol no puede ser nulo o inválido.';
    END IF;
    IF wusername IS NULL OR wusername = '' THEN
        RETURN 'El username no puede ser nulo o vacío.';
    END IF;
    IF wcorreo IS NULL OR wcorreo = '' THEN
        RETURN 'El correo no puede ser nulo o vacío.';
    END IF;
    IF wcontrasena IS NULL OR wcontrasena = '' THEN
        RETURN 'La contraseña no puede ser nula o vacía.';
    END IF;

    INSERT INTO t_usuarios (id_usuario, id_rol, username, tipo_documento, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, correo, contrasena)
    VALUES (wid_usuario, wid_rol, wusername, wtipo_documento, wprimer_nombre, wsegundo_nombre, wprimer_apellido, wsegundo_apellido, wcorreo, wcontrasena);

    RAISE NOTICE 'Ya inserté el usuario %', wusername;
    RETURN 'Esta vaina funcionó.. Somos duros en ADSO';

EXCEPTION
    WHEN SQLSTATE '23505' THEN  
        RAISE NOTICE 'El registro ya existe.. Trabaje bien o ábrase llaveee';
        RETURN 'El documento, usuario o correo ya están registrados.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';
        RETURN 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';
END;
$$ 
LANGUAGE PLPGSQL;
