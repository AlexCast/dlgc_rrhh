DELIMITER //

CREATE OR REPLACE FUNCTION fun_insert_permisos_evidencias(
    wid_permiso INT,
    wproveedor VARCHAR(20),
    wstorage_key VARCHAR(255),
    wurl_publica VARCHAR(500),
    wnombre_original VARCHAR(255),
    wmime_type VARCHAR(100),
    wtamano_bytes INT
) RETURNS VARCHAR(255)
BEGIN
    -- En MariaDB, SQLSTATE '23000' captura las violaciones de llave foránea (23503 en PG)
    -- y SQLEXCEPTION atrapará las fallas del CHECK de tamaño/mime_type (23514 en PG)
    DECLARE EXIT HANDLER FOR SQLSTATE '23000' RETURN 'No existe la solicitud de permiso indicada.';
    DECLARE EXIT HANDLER FOR SQLEXCEPTION RETURN 'El tipo de archivo o tamaño no cumple las reglas permitidas, u ocurrió un error.';

    IF wid_permiso IS NULL OR wid_permiso <= 0 THEN
        RETURN 'El ID del permiso no es válido.';
    END IF;

    IF wstorage_key IS NULL OR TRIM(wstorage_key) = '' THEN
        RETURN 'La referencia del archivo (storage_key) es obligatoria.';
    END IF;

    INSERT INTO t_permisos_evidencias (
        id_permiso, 
        proveedor, 
        storage_key, 
        url_publica, 
        nombre_original,
        mime_type, 
        tamano_bytes, 
        usr_insert, 
        fec_insert
    )
    VALUES (
        wid_permiso, 
        COALESCE(UPPER(TRIM(wproveedor)), 'BUNNY'), 
        TRIM(wstorage_key), 
        wurl_publica,
        wnombre_original, 
        wmime_type, 
        wtamano_bytes,
        COALESCE(@app_current_user, CURRENT_USER()),
        CURRENT_TIMESTAMP
    );

    RETURN 'Evidencia registrada correctamente.';
END //

DELIMITER ;