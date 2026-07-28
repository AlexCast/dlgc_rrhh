CREATE OR REPLACE FUNCTION fun_update_empleados(
    wid_usuario t_empleados.id_usuario%TYPE,
    wfecha_ingreso t_empleados.fecha_ingreso%TYPE,
    wgenero t_empleados.genero%TYPE,
    wfecha_nacimiento t_empleados.fecha_nacimiento%TYPE,
    wtipo_sangre t_empleados.tipo_sangre%TYPE,
    westado_civil t_empleados.estado_civil%TYPE,
    wdireccion_casa t_empleados.direccion_casa%TYPE,
    wnumero_celular t_empleados.numero_celular%TYPE,
    wid_jefe t_empleados.id_jefe%TYPE,
    wid_municipio t_empleados.id_municipio%TYPE,
    wfecha_egreso t_empleados.fecha_egreso%TYPE DEFAULT NULL,
    wfoto_perfil t_empleados.foto_perfil%TYPE DEFAULT NULL
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_usuario IS NULL OR wid_usuario = '' THEN
        RETURN 'El id_usuario no puede ser nulo o vacío.';
    END IF;
    IF wfecha_ingreso IS NULL THEN
        RETURN 'La fecha de ingreso no puede ser nula.';
    END IF;
    IF wfecha_nacimiento IS NULL THEN
        RETURN 'La fecha de nacimiento no puede ser nula.';
    END IF;
    IF wdireccion_casa IS NULL OR wdireccion_casa = '' THEN
        RETURN 'La dirección de casa no puede ser nula o vacía.';
    END IF;
    IF wnumero_celular IS NULL OR wnumero_celular !~ '^[0-9]{10}$' THEN
        RETURN 'El número celular debe tener exactamente 10 dígitos.';
    END IF;
    IF wid_jefe IS NOT NULL AND wid_jefe = wid_usuario THEN
        RETURN 'El id_jefe no puede ser igual al id_usuario.';
    END IF;
    IF wid_municipio IS NULL OR wid_municipio <= 0 THEN
        RETURN 'El ID del municipio no es válido.';
    END IF;
    IF wfecha_egreso IS NOT NULL AND wfecha_egreso < wfecha_ingreso THEN
        RETURN 'La fecha de egreso no puede ser menor a la fecha de ingreso.';
    END IF;

    UPDATE t_empleados
    SET
        id_jefe = NULLIF(wid_jefe, ''),
        id_municipio = wid_municipio,
        fecha_ingreso = wfecha_ingreso,
        fecha_egreso = wfecha_egreso,
        genero = wgenero,
        fecha_nacimiento = wfecha_nacimiento,
        tipo_sangre = wtipo_sangre,
        estado_civil = westado_civil,
        direccion_casa = wdireccion_casa,
        numero_celular = wnumero_celular,
        foto_perfil = wfoto_perfil
    WHERE id_usuario = wid_usuario
      AND fec_delete IS NULL;

    IF FOUND THEN
        RAISE NOTICE 'Empleado % actualizado correctamente.', wid_usuario;
        RETURN 'Esta vaina funcionó.. Somos duros en ADSO';
    ELSE
        RETURN 'No se encontró el empleado o está eliminado.';
    END IF;

EXCEPTION
    WHEN SQLSTATE '23503' THEN
        RETURN 'No existe el jefe o municipio relacionado.';
    WHEN SQLSTATE '23514' THEN
        RETURN 'Alguno de los datos no cumple las reglas de validación.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';
END;
$$
LANGUAGE PLPGSQL;