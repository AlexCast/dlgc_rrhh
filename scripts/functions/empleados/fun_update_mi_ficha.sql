DELIMITER //

CREATE OR REPLACE FUNCTION fun_update_mi_ficha(
    wid_usuario VARCHAR(20) COLLATE utf8mb4_unicode_ci,
    wprimer_nombre VARCHAR(30),
    wsegundo_nombre VARCHAR(30),
    wprimer_apellido VARCHAR(30),
    wsegundo_apellido VARCHAR(30),
    wgenero VARCHAR(10),
    wfecha_nacimiento DATE,
    wtipo_sangre VARCHAR(5),
    westado_civil VARCHAR(20),
    wdireccion_casa VARCHAR(100),
    wnumero_celular VARCHAR(10),
    wfoto_perfil VARCHAR(255)
) RETURNS VARCHAR(255)
BEGIN
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'Error de integridad al actualizar tu ficha.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'Error inesperado al actualizar tu ficha. Inténtalo de nuevo.';

    IF wid_usuario IS NULL OR wid_usuario = '' THEN
        RETURN 'No se pudo identificar tu usuario.';
    END IF;
    IF wprimer_nombre IS NULL OR wprimer_nombre = '' THEN
        RETURN 'El primer nombre es obligatorio.';
    END IF;
    IF wprimer_apellido IS NULL OR wprimer_apellido = '' THEN
        RETURN 'El primer apellido es obligatorio.';
    END IF;
    IF wfecha_nacimiento IS NULL THEN
        RETURN 'La fecha de nacimiento es obligatoria.';
    END IF;
    IF wdireccion_casa IS NULL OR wdireccion_casa = '' THEN
        RETURN 'La dirección de residencia es obligatoria.';
    END IF;
    IF wnumero_celular IS NULL OR wnumero_celular NOT REGEXP '^[0-9]{10}$' THEN
        RETURN 'El número celular debe tener exactamente 10 dígitos.';
    END IF;

    -- Autoservicio: solo se tocan campos personales + foto de perfil. Jefe,
    -- municipio, fechas de ingreso/egreso, rol, username, tipo_documento,
    -- correo, contraseña y datos laborales/seguridad social NO son parámetros
    -- de esta función y por tanto no pueden modificarse desde aquí.
    UPDATE t_usuarios
    SET primer_nombre = wprimer_nombre,
        segundo_nombre = NULLIF(wsegundo_nombre, ''),
        primer_apellido = wprimer_apellido,
        segundo_apellido = NULLIF(wsegundo_apellido, '')
    WHERE id_usuario = wid_usuario
      AND fec_delete IS NULL;

    -- Se valida la existencia con EXISTS y no con ROW_COUNT(): si el usuario
    -- guarda dos veces en el mismo segundo, el UPDATE no modifica ninguna
    -- columna y ROW_COUNT() sería 0 aunque la fila exista.
    IF NOT EXISTS (
        SELECT 1 FROM t_usuarios
        WHERE id_usuario = wid_usuario
          AND fec_delete IS NULL
    ) THEN
        RETURN 'No se encontró tu usuario activo.';
    END IF;

    UPDATE t_empleados
    SET genero = wgenero,
        fecha_nacimiento = wfecha_nacimiento,
        tipo_sangre = wtipo_sangre,
        estado_civil = westado_civil,
        direccion_casa = wdireccion_casa,
        numero_celular = wnumero_celular,
        foto_perfil = wfoto_perfil
    WHERE id_usuario = wid_usuario
      AND fec_delete IS NULL;

    IF NOT EXISTS (
        SELECT 1 FROM t_empleados
        WHERE id_usuario = wid_usuario
          AND fec_delete IS NULL
    ) THEN
        RETURN 'No se encontró tu ficha de empleado activa.';
    END IF;

    RETURN 'Tu información personal fue actualizada correctamente.';
END //

DELIMITER ;
