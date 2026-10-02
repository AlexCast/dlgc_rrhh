-- ============================================================
-- Seeds de instituciones Colombia 2026
-- EPS, Pensiones, Cajas de Compensacion, Bancos, ARL y Cesantias
-- ============================================================

BEGIN;

SELECT set_config('app.current_user', 'seed_institutions_2026', false);

-- ------------------------------------------------------------
-- EPS vigentes
-- ------------------------------------------------------------
SELECT fun_insert_eps(v.nombre)
FROM (
    VALUES
        ('ALIANSALUD EPS'),
        ('ANAS WAYUU EPSI'),
        ('ASMET SALUD EPS'),
        ('CAJACOPI ATLANTICO EPS'),
        ('CAPITAL SALUD EPS-S'),
        ('CAPRESOCA EPS'),
        ('COMFACHOCO EPS'),
        ('COMFAGUAJIRA EPS'),
        ('COMFAORIENTE EPS'),
        ('COMFAMILIAR CARTAGENA EPS'),
        ('COMFAMILIAR HUILA EPS'),
        ('COMFAMILIAR NARINO EPS'),
        ('COMFANORTE EPS'),
        ('COMPENSAR EPS'),
        ('COOSALUD EPS'),
        ('DUSAKAWI EPSI'),
        ('EMSSANAR EPS'),
        ('EPS FAMILIAR DE COLOMBIA'),
        ('EPS MALLAMAS'),
        ('EPS SANITAS'),
        ('EPS S.O.S'),
        ('FAMISANAR EPS'),
        ('MUTUAL SER EPS'),
        ('NUEVA EPS'),
        ('PIJAOS SALUD EPSI'),
        ('SALUD BOLIVAR EPS'),
        ('SALUD MIA EPS'),
        ('SALUD TOTAL EPS'),
        ('SAVIA SALUD EPS')
) AS v(nombre);

-- ------------------------------------------------------------
-- Fondos de pension vigentes
-- ------------------------------------------------------------
SELECT fun_insert_pension(v.nombre)
FROM (
    VALUES
        ('COLPENSIONES'),
        ('COLFONDOS'),
        ('PORVENIR'),
        ('PROTECCION'),
        ('SKANDIA')
) AS v(nombre);

-- ------------------------------------------------------------
-- Cajas de compensacion familiar vigentes
-- ------------------------------------------------------------
SELECT fun_insert_cajacompensacion(v.nombre)
FROM (
    VALUES
        ('CAFAM'),
        ('CAJACOPI ATLANTICO'),
        ('CAJASAN'),
        ('COLSUBSIDIO'),
        ('COMFABOY'),
        ('COMFACASANARE'),
        ('COMFACESAR'),
        ('COMFACHOCO'),
        ('COMFACUNDI'),
        ('COMFAGUAJIRA'),
        ('COMFAMA'),
        ('COMFAMILIAR ATLANTICO'),
        ('COMFAMILIAR CAMACOL'),
        ('COMFAMILIAR CARTAGENA'),
        ('COMFAMILIAR HUILA'),
        ('COMFAMILIAR NARINO'),
        ('COMFAMILIAR RISARALDA'),
        ('COMFANORTE'),
        ('COMFAORIENTE'),
        ('COMFASUCRE'),
        ('COMFENALCO ANTIOQUIA'),
        ('COMFENALCO CARTAGENA'),
        ('COMFENALCO QUINDIO'),
        ('COMFENALCO SANTANDER'),
        ('COMFENALCO TOLIMA'),
        ('COMFENALCO VALLE DELAGENTE'),
        ('COMFANDI'),
        ('COMPENSAR'),
        ('CONFAMILIARES CALDAS'),
        ('COFREM')
) AS v(nombre);

-- ------------------------------------------------------------
-- Bancos vigentes
-- ------------------------------------------------------------
SELECT fun_insert_banco(v.nombre)
FROM (
    VALUES
        ('BAN100'),
        ('BANCAMIA'),
        ('BANCO AGRARIO DE COLOMBIA'),
        ('BANCO AV VILLAS'),
        ('BANCO BBVA COLOMBIA'),
        ('BANCO CAJA SOCIAL'),
        ('BANCO COOPERATIVO COOPCENTRAL'),
        ('BANCO DAVIVIENDA'),
        ('BANCO DE BOGOTA'),
        ('BANCO DE OCCIDENTE'),
        ('BANCO FALABELLA'),
        ('BANCO FINANDINA'),
        ('BANCO GNB SUDAMERIS'),
        ('BANCO ITAU COLOMBIA'),
        ('BANCO MUNDO MUJER'),
        ('BANCO PICHINCHA'),
        ('BANCO POPULAR'),
        ('BANCO SANTANDER COLOMBIA'),
        ('BANCO SERFINANZA'),
        ('BANCO W'),
        ('BANCOLOMBIA'),
        ('CITIBANK COLOMBIA'),
        ('LULO BANK'),
        ('MIBANCO'),
        ('SCOTIABANK COLPATRIA')
) AS v(nombre);

-- ------------------------------------------------------------
-- ARL vigentes
-- ------------------------------------------------------------
SELECT fun_insert_arl(v.nombre)
FROM (
    VALUES
        ('ARL AXA COLPATRIA'),
        ('ARL BOLIVAR'),
        ('ARL COLMENA'),
        ('ARL EQUIDAD SEGUROS'),
        ('ARL MAPFRE'),
        ('ARL POSITIVA'),
        ('ARL SURA')
) AS v(nombre);

-- ------------------------------------------------------------
-- Fondos de cesantias vigentes
-- ------------------------------------------------------------
SELECT fun_insert_cesantias(v.nombre)
FROM (
    VALUES
        ('COLFONDOS'),
        ('FONDO NACIONAL DEL AHORRO'),
        ('PORVENIR'),
        ('PROTECCION'),
        ('SKANDIA')
) AS v(nombre);

COMMIT;
