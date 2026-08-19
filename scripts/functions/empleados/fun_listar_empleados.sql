CREATE OR REPLACE FUNCTION fun_listar_empleados(
    wid_usuario t_empleados.id_usuario%TYPE
) RETURNS BOOLEAN AS
$$
DECLARE
    wreg_empleado t_empleados%ROWTYPE;
BEGIN
    IF wid_usuario IS NULL OR wid_usuario = '' THEN
        RAISE NOTICE 'El ID del empleado no puede ser nulo o vacío.';
        RETURN FALSE;
    END IF;

    SELECT
        id_usuario,
        id_jefe,
		id_municipio,
        fecha_ingreso,
        fecha_egreso,
        genero,
        fecha_nacimiento,
        tipo_sangre,
        estado_civil,
        direccion_casa,
        numero_celular,
        foto_perfil,
        usr_insert,
        fec_insert,
        usr_update,
        fec_update,
        usr_delete,
        fec_delete
    INTO wreg_empleado
    FROM t_empleados
    WHERE id_usuario = wid_usuario
      AND fec_delete IS NULL;

    IF NOT FOUND THEN
        RAISE NOTICE 'El empleado con ID % no existe o está eliminado.', wid_usuario;
        RETURN FALSE;
    END IF;

    RAISE NOTICE 'ID: %, Jefe: %, Municipio: %, Ingreso: %, Egreso: %, Género: %, Nacimiento: %, Sangre: %, Estado civil: %, Dirección: %, Celular: %, Foto: %',
        wreg_empleado.id_usuario,
        wreg_empleado.id_jefe,
		wreg_empleado.id_municipio,
        wreg_empleado.fecha_ingreso,
        wreg_empleado.fecha_egreso,
        wreg_empleado.genero,
        wreg_empleado.fecha_nacimiento,
        wreg_empleado.tipo_sangre,
        wreg_empleado.estado_civil,
        wreg_empleado.direccion_casa,
        wreg_empleado.numero_celular,
        wreg_empleado.foto_perfil;

    RETURN TRUE;

EXCEPTION
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN FALSE;
END;
$$
LANGUAGE PLPGSQL;