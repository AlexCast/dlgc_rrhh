CREATE OR REPLACE FUNCTION fun_insert_permisos_evidencias(
    wid_permiso t_permisos_evidencias.id_permiso%TYPE,
    wproveedor t_permisos_evidencias.proveedor%TYPE,
    wstorage_key t_permisos_evidencias.storage_key%TYPE,
    wurl_publica t_permisos_evidencias.url_publica%TYPE,
    wnombre_original t_permisos_evidencias.nombre_original%TYPE,
    wmime_type t_permisos_evidencias.mime_type%TYPE,
    wtamano_bytes t_permisos_evidencias.tamano_bytes%TYPE
) RETURNS VARCHAR AS
$$
BEGIN
    IF wid_permiso IS NULL OR wid_permiso <= 0 THEN
        RETURN 'El ID del permiso no es válido.';
    END IF;

    IF wstorage_key IS NULL OR TRIM(wstorage_key) = '' THEN
        RETURN 'La referencia del archivo (storage_key) es obligatoria.';
    END IF;

    INSERT INTO t_permisos_evidencias (
        id_permiso, proveedor, storage_key, url_publica, nombre_original,
        mime_type, tamano_bytes, usr_insert, fec_insert
    )
    VALUES (
        wid_permiso, COALESCE(UPPER(TRIM(wproveedor)), 'BUNNY'), TRIM(wstorage_key), wurl_publica,
        wnombre_original, wmime_type, wtamano_bytes,
        COALESCE(NULLIF(current_setting('app.current_user', true), ''), CURRENT_USER),
        CURRENT_TIMESTAMP
    );

    RETURN 'Evidencia registrada correctamente.';

EXCEPTION
    WHEN SQLSTATE '23503' THEN
        RETURN 'No existe la solicitud de permiso indicada.';
    WHEN SQLSTATE '23514' THEN
        RETURN 'El tipo de archivo o tamaño no cumple las reglas permitidas.';
    WHEN OTHERS THEN
        RAISE NOTICE 'Error: %', SQLERRM;
        RETURN 'Ocurrió un error al registrar la evidencia.';
END;
$$
LANGUAGE PLPGSQL;
