/*------------------------------------------------------------*/
/* GRUPO 3 - BORRADO FÍSICO                                   */
/* Elimina físicamente registros temporales una vez que son   */
/* usados o marcados como eliminados (no son soft delete).    */
/*                                                            */
/* Adaptación MariaDB:                                        */
/*  - MariaDB no permite que un trigger modifique (DELETE)     */
/*    la misma tabla sobre la que está definido (error 1442).  */
/*    Por eso los triggers que borraban su propia fila         */
/*    (tri_borrar_codigo_registro, tri_borrar_token_* ) no     */
/*    pueden replicarse: esas filas quedan marcadas            */
/*    (usado/fecha_uso/fec_delete) y se excluyen de las        */
/*    consultas mediante filtros.                              */
/*  - El borrado de t_registros_pendientes se mantiene         */
/*    eliminando la fila padre; requiere ON DELETE CASCADE     */
/*    en t_verificacion_correo.id_registro_pendiente.          */
/*------------------------------------------------------------*/

DELIMITER //

-- Limpia el registro pendiente cuando su token se marca como usado o eliminado.
CREATE OR REPLACE TRIGGER tri_limpiar_registros_pendientes
AFTER UPDATE ON t_verificacion_correo
FOR EACH ROW
BEGIN
    IF NEW.fecha_uso IS NOT NULL OR NEW.fec_delete IS NOT NULL THEN
        DELETE FROM t_registros_pendientes
        WHERE id_registro_pendiente = OLD.id_registro_pendiente;
    END IF;
END//

-- Procedimiento opcional: limpiar registros pendientes expirados manualmente.
CREATE OR REPLACE PROCEDURE sp_limpiar_registros_pendientes_expirados()
BEGIN
    DELETE FROM t_registros_pendientes
    WHERE fecha_expiracion < CURRENT_TIMESTAMP
       OR fec_delete IS NOT NULL;
END//

DELIMITER ;

/*---------------------------------------------------------------------------*/
/* Triggers no migrables (limitación de MariaDB, error 1442):                */
/*                                                                           */
/* - tri_borrar_codigo_registro (AFTER UPDATE ON t_codigos_registro)         */
/*   En PostgreSQL borraba físicamente el código tras marcarlo usado.        */
/*   En MariaDB la fila queda con usado = TRUE y se excluye de las consultas */
/*   (filtros usado = FALSE).                                                */
/*                                                                           */
/* - tri_borrar_token_verificacion (AFTER UPDATE ON t_verificacion_correo)   */
/*   En PostgreSQL borraba el token usado. En MariaDB el token queda con     */
/*   fecha_uso seteada; la fila padre y sus tokens se eliminan mediante      */
/*   tri_limpiar_registros_pendientes + ON DELETE CASCADE.                   */
/*                                                                           */
/* - tri_borrar_token_recuperacion (AFTER UPDATE ON t_recuperacion_contrasena)*/
/*   En PostgreSQL borraba el token usado. En MariaDB el token queda con     */
/*   fecha_uso seteada y se excluye de las consultas                         */
/*   (filtro fecha_uso IS NULL).                                             */
/*---------------------------------------------------------------------------*/