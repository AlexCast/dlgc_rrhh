DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_empleados(
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
    -- En MariaDB, SQLSTATE '23000' agrupa violaciones de duplicados (23505) y llaves foráneas (23503)
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'El empleado ya existe o no existe el usuario, jefe o municipio relacionado.';
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
    -- Reemplazo de la negación de expresión regular de PGSQL (!~) a MariaDB (NOT REGEXP)
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

    INSERT INTO t_empleados (
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
        fec_insert
    )
    VALUES (
        wid_usuario,
        NULLIF(wid_jefe, ''),
        wid_municipio,
        wfecha_ingreso,
        wfecha_egreso,
        wgenero,
        wfecha_nacimiento,
        wtipo_sangre,
        westado_civil,
        wdireccion_casa,
        wnumero_celular,
        wfoto_perfil,
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Esta vaina funcionó.. Somos duros en ADSO';
END //

DELIMITER ;