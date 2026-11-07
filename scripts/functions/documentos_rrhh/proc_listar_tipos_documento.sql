DELIMITER //

CREATE OR REPLACE PROCEDURE proc_listar_tipos_documento()
BEGIN
    SELECT id_tipo_documento, codigo, nombre
    FROM t_tipos_documento_rrhh
    WHERE fec_delete IS NULL
    ORDER BY nombre;
END //

DELIMITER ;
