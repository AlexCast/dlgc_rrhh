DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_empleados(
    wid_usuario VARCHAR(20),
    wfecha_ingreso DATE,
    wgenero VARCHAR(10),
    wfecha_nacimiento DATE,
    wtipo_sangre VARCHAR(5),
    westado_civil VARCHAR(20),
    wdireccion_casa VARCHAR(100),
    wnumero_celular VARCHAR(10),
    wid_jefe VARCHAR(20),
    wid_municipio INT,
    wfecha_egreso DATE,
    wfoto_perfil VARCHAR(255)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'No existe el jefe o municipio relacionado (u otro error de integridad).';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Esta vaina se totió.. Y no fue de la risa.. Déjeme trabajar';

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
    IF wnumero_celular IS NULL OR wnumero_celular NOT REGEXP '^[0-9]{10}$' THEN
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
    SET id_jefe = NULLIF(wid_jefe, ''),
        id_municipio = wid_municipio,
        fecha_ingreso = wfecha_ingreso,
        fecha_egreso = wfecha_egreso,
        genero = wgenero,
        fecha_nacimiento = wfecha_nacimiento,
        tipo_sangre = wtipo_sangre,
        estado_civil = westado_civil,
        direccion_casa = wdireccion_casa,
        numero_celular = wnumero_celular,
        foto_perfil = wfoto_perfil,
        usr_update = COALESCE(@app_current_user, CURRENT_USER()),
        fec_update = CURRENT_TIMESTAMP
    WHERE id_usuario = wid_usuario
      AND fec_delete IS NULL;

    IF ROW_COUNT() > 0 THEN
        RETURN 'Esta vaina funcionó.. Somos duros en ADSO';
    ELSE
        RETURN 'No se encontró el empleado o está eliminado.';
    END IF;
END //

DELIMITER ;