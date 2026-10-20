DELIMITER //

CREATE OR REPLACE PROCEDURE proc_login_usuarios(
    IN widentificador VARCHAR(40)
)
BEGIN
    SELECT u.id_usuario, 
           u.username, 
           u.id_rol, 
           u.primer_nombre, 
           u.primer_apellido, 
           u.correo, 
           u.correo_verificado, 
           u.contrasena
    FROM t_usuarios u
    WHERE (u.username = widentificador OR u.correo = widentificador)
      AND u.fec_delete IS NULL;
END //

DELIMITER ;