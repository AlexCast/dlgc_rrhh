DELIMITER //

-- Procedimiento que calcula los 18 festivos civiles colombianos de un año y los deja en una tabla temporal.
CREATE OR REPLACE PROCEDURE proc_calcular_festivos_colombia(IN wanio INT)
BEGIN
    DECLARE vpascua DATE;
    SET vpascua = fun_calcular_pascua(wanio);

    CREATE TEMPORARY TABLE IF NOT EXISTS tmp_festivos (
        fecha DATE,
        descripcion VARCHAR(100)
    );
    DELETE FROM tmp_festivos;

    INSERT INTO tmp_festivos (fecha, descripcion) VALUES
        -- Fijos
        (CAST(CONCAT(wanio, '-01-01') AS DATE), 'Año Nuevo'),
        (CAST(CONCAT(wanio, '-05-01') AS DATE), 'Día del Trabajo'),
        (CAST(CONCAT(wanio, '-07-20') AS DATE), 'Día de la Independencia'),
        (CAST(CONCAT(wanio, '-08-07') AS DATE), 'Batalla de Boyacá'),
        (CAST(CONCAT(wanio, '-12-08') AS DATE), 'Inmaculada Concepción'),
        (CAST(CONCAT(wanio, '-12-25') AS DATE), 'Navidad'),

        -- Basados en Pascua, NO se trasladan de día
        (DATE_SUB(vpascua, INTERVAL 3 DAY), 'Jueves Santo'),
        (DATE_SUB(vpascua, INTERVAL 2 DAY), 'Viernes Santo'),

        -- Ley Emiliani: fijos que se trasladan al lunes siguiente
        (fun_siguiente_lunes(CAST(CONCAT(wanio, '-01-06') AS DATE)), 'Reyes Magos'),
        (fun_siguiente_lunes(CAST(CONCAT(wanio, '-03-19') AS DATE)), 'San José'),
        (fun_siguiente_lunes(CAST(CONCAT(wanio, '-06-29') AS DATE)), 'San Pedro y San Pablo'),
        (fun_siguiente_lunes(CAST(CONCAT(wanio, '-08-15') AS DATE)), 'Asunción de la Virgen'),
        (fun_siguiente_lunes(CAST(CONCAT(wanio, '-10-12') AS DATE)), 'Día de la Raza'),
        (fun_siguiente_lunes(CAST(CONCAT(wanio, '-11-01') AS DATE)), 'Todos los Santos'),
        (fun_siguiente_lunes(CAST(CONCAT(wanio, '-11-11') AS DATE)), 'Independencia de Cartagena'),

        -- Ley Emiliani: basados en Pascua y luego trasladados
        (fun_siguiente_lunes(DATE_ADD(vpascua, INTERVAL 39 DAY)), 'Ascensión del Señor'),
        (fun_siguiente_lunes(DATE_ADD(vpascua, INTERVAL 60 DAY)), 'Corpus Christi'),
        (fun_siguiente_lunes(DATE_ADD(vpascua, INTERVAL 68 DAY)), 'Sagrado Corazón');
END //

DELIMITER ;