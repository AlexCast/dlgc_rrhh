CREATE OR REPLACE FUNCTION fun_update_usuarios(
    wid_usuario t_usuarios.id_usuario%TYPE,
    wprimer_nombre t_usuarios.primer_nombre%TYPE,
    wprimer_apellido t_usuarios.primer_apellido%TYPE,
    wcorreo t_usuarios.correo%TYPE,
    wsegundo_nombre t_usuarios.segundo_nombre%TYPE DEFAULT NULL,
    wsegundo_apellido t_usuarios.segundo_apellido%TYPE DEFAULT NULL
) RETURNS VARCHAR AS 
$$
BEGIN
    IF wid_usuario IS NULL OR wid_usuario = '' THEN
        RETURN 'el id del usuario no puede ser nulo';
    END IF;
    IF wprimer_nombre IS NULL OR LENGTH(wprimer_nombre) < 1 THEN
        RETURN 'El primer nombre no puede ser nulo o vacío.';
    END IF;

    UPDATE t_usuarios
    SET primer_nombre = wprimer_nombre,
        segundo_nombre = wsegundo_nombre,
        primer_apellido = wprimer_apellido,
        segundo_apellido = wsegundo_apellido,
        correo = wcorreo
        WHERE id_usuario = wid_usuario
            AND fec_delete IS NULL;
            
    IF FOUND THEN
        RAISE NOTICE 'Ya actualicé el usuario %', wid_usuario;
        RETURN 'Esta vaina funcionó.. Somos duros en ADSO';
    ELSE
        RAISE NOTICE 'Pequeño demonio, no funcionó esta joda... Y ahora????';
        RETURN 'Eche pa la primaria porque de esto no va a comer... Sorry';
    END IF;
    
EXCEPTION
    WHEN SQLSTATE '23505' THEN
        RAISE NOTICE 'El registro ya existe.. Trabaje bien o ábrase llaveee';
        RETURN 'El correo ya está registrado por otro usuario.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';
        RETURN 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';
END;
$$ 
LANGUAGE PLPGSQL;
