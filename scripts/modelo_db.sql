DROP TABLE IF EXISTS t_sst_buzon_quejas;
DROP TABLE IF EXISTS t_sst_comite_miembros;
DROP TABLE IF EXISTS t_usuarios_operaciones;
DROP TABLE IF EXISTS t_roles_operaciones;
DROP TABLE IF EXISTS t_permisos_evidencias;
DROP TABLE IF EXISTS t_permisos_aprobaciones;
DROP TABLE IF EXISTS t_permisos_dias_aprobados;
DROP TABLE IF EXISTS t_vacaciones_ajustes;
DROP TABLE IF EXISTS t_solicitudes_permisos_motivos;
DROP TABLE IF EXISTS t_solicitudes_permisos;
DROP TABLE IF EXISTS t_tipos_permisos;
DROP TABLE IF EXISTS t_dias_festivos_excepciones;
DROP TABLE IF EXISTS t_dias_festivos;
DROP TABLE IF EXISTS t_afiliaciones_empleados;
DROP TABLE IF EXISTS t_nomina;
DROP TABLE IF EXISTS t_contratos_empleados;
DROP TABLE IF EXISTS t_empleados;
DROP TABLE IF EXISTS t_municipios;
DROP TABLE IF EXISTS t_departamentos;
DROP TABLE IF EXISTS t_operaciones;
DROP TABLE IF EXISTS t_usuarios;
DROP TABLE IF EXISTS t_roles;
DROP TABLE IF EXISTS t_modulos;
DROP TABLE IF EXISTS t_areas;
DROP TABLE IF EXISTS t_bancos;
DROP TABLE IF EXISTS t_eps;
DROP TABLE IF EXISTS t_arl;
DROP TABLE IF EXISTS t_caja_compensacion;
DROP TABLE IF EXISTS t_pension;
DROP TABLE IF EXISTS t_cesantias;
DROP TABLE IF EXISTS t_comunicados;
DROP TABLE IF EXISTS t_recuperacion_contrasena;
DROP TABLE IF EXISTS t_verificacion_correo;
DROP TABLE IF EXISTS t_intentos_correo;
DROP TABLE IF EXISTS t_codigos_registro;
DROP TABLE IF EXISTS t_registros_pendientes;


CREATE TABLE IF NOT EXISTS t_departamentos (
    id_departamento SERIAL,
    departamento VARCHAR(255) NOT NULL DEFAULT '',
    PRIMARY KEY (id_departamento)
);

CREATE TABLE IF NOT EXISTS t_modulos (
    id_modulo           INT NOT NULL,
    nombre_modulo       VARCHAR(30) NOT NULL,
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_modulo)
);

-- Esta tabla identifica las áreas de la empresa, y permite asociar a los empleados con un área específica. Esto es útil para la gestión de recursos humanos y para asignar responsabilidades dentro de la organización.
CREATE TABLE IF NOT EXISTS t_areas (
    id_area             SERIAL,
    nombre_area         VARCHAR(30) NOT NULL,
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_area)
);

CREATE TABLE IF NOT EXISTS t_bancos (
    id_banco              SERIAL,
    nombre_banco          VARCHAR(50) NOT NULL,
    usr_insert            VARCHAR NOT NULL,
    fec_insert            TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update            VARCHAR,
    fec_update            TIMESTAMP WITHOUT TIME ZONE,
    usr_delete            VARCHAR,
    fec_delete            TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_banco)
);

CREATE TABLE IF NOT EXISTS t_eps (
    id_eps                SERIAL,
    nombre_eps            VARCHAR(50) NOT NULL,
    usr_insert            VARCHAR NOT NULL,
    fec_insert            TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update            VARCHAR,
    fec_update            TIMESTAMP WITHOUT TIME ZONE,
    usr_delete            VARCHAR,
    fec_delete            TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_eps)
);

CREATE TABLE IF NOT EXISTS t_arl (
    id_arl                SERIAL,
    nombre_arl            VARCHAR(50) NOT NULL,
    usr_insert            VARCHAR NOT NULL,
    fec_insert            TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update            VARCHAR,
    fec_update            TIMESTAMP WITHOUT TIME ZONE,
    usr_delete            VARCHAR,
    fec_delete            TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_arl)
);

CREATE TABLE IF NOT EXISTS t_caja_compensacion (
    id_caja               SERIAL,
    nombre_caja           VARCHAR(50) NOT NULL,
    usr_insert            VARCHAR NOT NULL,
    fec_insert            TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update            VARCHAR,
    fec_update            TIMESTAMP WITHOUT TIME ZONE,
    usr_delete            VARCHAR,
    fec_delete            TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_caja)
);

CREATE TABLE IF NOT EXISTS t_pension (
    id_pension            SERIAL,
    nombre_pension        VARCHAR(50) NOT NULL,
    usr_insert            VARCHAR NOT NULL,
    fec_insert            TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update            VARCHAR,
    fec_update            TIMESTAMP WITHOUT TIME ZONE,
    usr_delete            VARCHAR,
    fec_delete            TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_pension)
);

CREATE TABLE IF NOT EXISTS t_cesantias (
    id_cesantia           SERIAL,
    nombre_cesantia       VARCHAR(50) NOT NULL,
    usr_insert            VARCHAR NOT NULL,
    fec_insert            TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update            VARCHAR,
    fec_update            TIMESTAMP WITHOUT TIME ZONE,
    usr_delete            VARCHAR,
    fec_delete            TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_cesantia)
);

CREATE TABLE IF NOT EXISTS t_operaciones (
    id_operacion          INT NOT NULL,
    id_modulo             INT NOT NULL,
    nombre_operacion      VARCHAR(50) NOT NULL,
    usr_insert            VARCHAR NOT NULL,
    fec_insert            TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update            VARCHAR,
    fec_update            TIMESTAMP WITHOUT TIME ZONE,
    usr_delete            VARCHAR,
    fec_delete            TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_operacion),
    FOREIGN KEY (id_modulo) REFERENCES t_modulos(id_modulo)
);

CREATE TABLE IF NOT EXISTS t_roles (
    id_rol                INT NOT NULL,
    nombre_rol            VARCHAR(50) NOT NULL,
    usr_insert            VARCHAR NOT NULL,
    fec_insert            TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update            VARCHAR,
    fec_update            TIMESTAMP WITHOUT TIME ZONE,
    usr_delete            VARCHAR,
    fec_delete            TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_rol)
);

CREATE TABLE IF NOT EXISTS t_roles_operaciones (
    id_rol                INT NOT NULL,
    id_operacion          INT NOT NULL,
    usr_insert            VARCHAR NOT NULL,
    fec_insert            TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update            VARCHAR,
    fec_update            TIMESTAMP WITHOUT TIME ZONE,
    usr_delete            VARCHAR,
    fec_delete            TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_rol, id_operacion),
    FOREIGN KEY (id_rol) REFERENCES t_roles(id_rol),
    FOREIGN KEY (id_operacion) REFERENCES t_operaciones(id_operacion)
);

CREATE TABLE IF NOT EXISTS t_usuarios (
    id_usuario       VARCHAR(20) NOT NULL,
    id_rol           INT NOT NULL,
    username         VARCHAR(30) NOT NULL UNIQUE,
    tipo_documento   VARCHAR(20) NOT NULL CHECK (tipo_documento IN ('CC', 'PPT', 'CE')),
    primer_nombre    VARCHAR(30) NOT NULL,
    segundo_nombre   VARCHAR(30),
    primer_apellido  VARCHAR(30) NOT NULL,
    segundo_apellido VARCHAR(30),
    correo           VARCHAR(40) NOT NULL UNIQUE,
    correo_verificado BOOLEAN NOT NULL DEFAULT FALSE,
    contrasena       VARCHAR(255) NOT NULL,
    usr_insert       VARCHAR NOT NULL,
    fec_insert       TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update       VARCHAR,
    fec_update       TIMESTAMP WITHOUT TIME ZONE,
    usr_delete       VARCHAR,
    fec_delete       TIMESTAMP WITHOUT TIME ZONE,
    CONSTRAINT chk_t_usuarios_documento_formato
        CHECK (
            (tipo_documento = 'CC' AND id_usuario ~ '^[0-9]{10}$') OR
            (tipo_documento = 'PPT' AND id_usuario ~ '^[A-Z-0-9-]{5,20}$') OR
            (tipo_documento = 'CE' AND id_usuario ~ '^[A-Z-0-9]{5,20}$')
        ),
    PRIMARY KEY (id_usuario),
    FOREIGN KEY (id_rol) REFERENCES t_roles(id_rol)
);

CREATE TABLE IF NOT EXISTS t_usuarios_operaciones (
    id_usuario        VARCHAR(20) NOT NULL,
    id_operacion      INT NOT NULL,
    usr_insert        VARCHAR NOT NULL,
    fec_insert        TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update        VARCHAR,
    fec_update        TIMESTAMP WITHOUT TIME ZONE,
    usr_delete        VARCHAR,
    fec_delete        TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_usuario, id_operacion),
    FOREIGN KEY (id_usuario) REFERENCES t_usuarios(id_usuario),
    FOREIGN KEY (id_operacion) REFERENCES t_operaciones(id_operacion)
);

CREATE TABLE IF NOT EXISTS t_municipios (
    id_municipio SERIAL,
    municipio VARCHAR(255) NOT NULL DEFAULT '',
    estado SMALLINT NOT NULL,
    departamento_id INT NOT NULL,
    PRIMARY KEY (id_municipio),
    CONSTRAINT fk_t_municipios_t_departamentos
    FOREIGN KEY (departamento_id) REFERENCES t_departamentos(id_departamento)
);

CREATE TABLE IF NOT EXISTS t_empleados (
    id_usuario        VARCHAR(20) NOT NULL,
    id_jefe           VARCHAR(20) CHECK (id_jefe IS NULL OR id_jefe <> id_usuario),
    id_municipio      INT NOT NULL,
    fecha_ingreso     DATE NOT NULL,
    fecha_egreso      DATE,
    genero            VARCHAR(10) NOT NULL CHECK (genero IN ('MASCULINO', 'FEMENINO', 'OTRO')),
    fecha_nacimiento  DATE NOT NULL,
    tipo_sangre       VARCHAR(5) NOT NULL CHECK (tipo_sangre IN ('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-')),
    estado_civil      VARCHAR(20) NOT NULL CHECK (estado_civil IN ('SOLTERO(A)', 'CASADO(A)', 'DIVORCIADO(A)', 'VIUDO(A)', 'UNION LIBRE', 'SEPARADO(A)')),
    direccion_casa    VARCHAR(100) NOT NULL,
    numero_celular    VARCHAR(10) NOT NULL,
    foto_perfil       VARCHAR(255),
    usr_insert        VARCHAR NOT NULL,
    fec_insert        TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update        VARCHAR,
    fec_update        TIMESTAMP WITHOUT TIME ZONE,
    usr_delete        VARCHAR,
    fec_delete        TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_usuario),
    FOREIGN KEY (id_usuario) REFERENCES t_usuarios(id_usuario),
    FOREIGN KEY (id_jefe) REFERENCES t_empleados(id_usuario),
    FOREIGN KEY (id_municipio) REFERENCES t_municipios(id_municipio)
);

CREATE INDEX idx_t_empleados_id_jefe ON t_empleados(id_jefe);

CREATE TABLE IF NOT EXISTS t_contratos_empleados (
    id_contrato        SERIAL,
    id_usuario         VARCHAR(20) NOT NULL,
    id_area            INT NOT NULL,
    puesto             VARCHAR(50) NOT NULL,
    fecha_inicio_puesto  DATE NOT NULL, 
    fecha_fin_puesto     DATE, -- Si es NULL, es su puesto actual. Si tiene fecha, ya no está en este puesto.
    tipo_contrato      VARCHAR(20) NOT NULL CHECK(tipo_contrato = 'FIJO' OR tipo_contrato = 'INDEFINIDO' OR tipo_contrato = 'PRACTICAS'),
    direccion_oficina  VARCHAR(100) NOT NULL,
    usr_insert         VARCHAR NOT NULL,
    fec_insert         TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update         VARCHAR,
    fec_update         TIMESTAMP WITHOUT TIME ZONE,
    usr_delete         VARCHAR,
    fec_delete         TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_contrato),
    FOREIGN KEY (id_usuario) REFERENCES t_empleados(id_usuario),
    FOREIGN KEY (id_area) REFERENCES t_areas(id_area)
);

CREATE TABLE IF NOT EXISTS t_nomina (
    id_nomina             SERIAL,
    id_usuario           VARCHAR(20) NOT NULL,
    id_banco              INT NOT NULL,
    num_cuenta            VARCHAR(20) NOT NULL,
    salario               NUMERIC(10,2) NOT NULL,
    usr_insert            VARCHAR NOT NULL,
    fec_insert            TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update            VARCHAR,
    fec_update            TIMESTAMP WITHOUT TIME ZONE,
    usr_delete            VARCHAR,
    fec_delete            TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_nomina),
    FOREIGN KEY (id_usuario) REFERENCES t_empleados(id_usuario),
    FOREIGN KEY (id_banco) REFERENCES t_bancos(id_banco)
);

CREATE TABLE IF NOT EXISTS t_afiliaciones_empleados (
    id_usuario           VARCHAR(20) NOT NULL,
    id_eps               INT NOT NULL,
    id_arl               INT NOT NULL,
    id_caja              INT NOT NULL,
    id_pension           INT NOT NULL,
    id_cesantia          INT NOT NULL,
    usr_insert           VARCHAR NOT NULL,
    fec_insert           TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update           VARCHAR,
    fec_update           TIMESTAMP WITHOUT TIME ZONE,
    usr_delete           VARCHAR,
    fec_delete           TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_usuario),
    FOREIGN KEY (id_usuario) REFERENCES t_empleados(id_usuario),
    FOREIGN KEY (id_eps) REFERENCES t_eps(id_eps),
    FOREIGN KEY (id_arl) REFERENCES t_arl(id_arl),
    FOREIGN KEY (id_caja) REFERENCES t_caja_compensacion(id_caja),
    FOREIGN KEY (id_pension) REFERENCES t_pension(id_pension),
    FOREIGN KEY (id_cesantia) REFERENCES t_cesantias(id_cesantia)
);

-- Tabla de configuración para los días festivos
-- tipo_festivo distingue festivos oficiales (NACIONAL) de los declarados por la empresa (EMPRESA).
-- descuenta_salario indica si, por defecto, ese día se descuenta del salario de todos los empleados.
CREATE TABLE IF NOT EXISTS t_dias_festivos (
    id_festivo          SERIAL,
    fecha               DATE NOT NULL UNIQUE,
    descripcion         VARCHAR(100) NOT NULL,
    tipo_festivo        VARCHAR(20) NOT NULL DEFAULT 'NACIONAL' CHECK (tipo_festivo IN ('NACIONAL', 'EMPRESA')),
    descuenta_salario   BOOLEAN NOT NULL DEFAULT TRUE,
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_festivo)
);

-- Excepciones puntuales por empleado a la regla general de un festivo (caso excepcional: sí/no descontar).
CREATE TABLE IF NOT EXISTS t_dias_festivos_excepciones (
    id_excepcion        SERIAL,
    id_festivo          INT NOT NULL,
    id_empleado         VARCHAR(20) NOT NULL,
    descuenta_salario   BOOLEAN NOT NULL,
    motivo              VARCHAR(255),
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_excepcion),
    CONSTRAINT uq_festivo_excepcion_empleado UNIQUE (id_festivo, id_empleado),
    FOREIGN KEY (id_festivo) REFERENCES t_dias_festivos(id_festivo),
    FOREIGN KEY (id_empleado) REFERENCES t_empleados(id_usuario)
);

-- Tabla maestra para administrar las categorías de permisos.
-- requiere_evidencia habilita/deshabilita el campo de adjuntos en el formulario para ese tipo.
-- porcentaje_descuento solo aplica cuando descuenta_tiempo = TRUE (flexibilidad pedida por RRHH).
CREATE TABLE IF NOT EXISTS t_tipos_permisos (
    id_tipo_permiso      SERIAL,
    nombre_tipo          VARCHAR(50) NOT NULL,
    descuenta_tiempo     BOOLEAN NOT NULL DEFAULT FALSE,
    porcentaje_descuento NUMERIC(5,2) CHECK (porcentaje_descuento IS NULL OR (porcentaje_descuento >= 0 AND porcentaje_descuento <= 100)),
    requiere_evidencia   BOOLEAN NOT NULL DEFAULT FALSE,
    usr_insert           VARCHAR NOT NULL,
    fec_insert           TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update           VARCHAR,
    fec_update           TIMESTAMP WITHOUT TIME ZONE,
    usr_delete           VARCHAR,
    fec_delete           TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_tipo_permiso),
    CONSTRAINT chk_tipos_permisos_descuento CHECK (
        (descuenta_tiempo = FALSE AND porcentaje_descuento IS NULL) OR
        (descuenta_tiempo = TRUE AND porcentaje_descuento IS NOT NULL)
    )
);


-- Tabla principal transaccional para las solicitudes
-- Nota: el motivo (o motivos) de la solicitud NO vive aquí; una solicitud puede tener
-- varios motivos a la vez, por lo que se modela en la tabla puente
-- t_solicitudes_permisos_motivos (relación N:M con t_tipos_permisos).
-- id_jefe_responsable es una "foto" del jefe directo (t_empleados.id_jefe) al momento de radicar
-- la solicitud: si el empleado cambia de jefe después, el histórico de bandejas no se altera.
-- La aprobación en sí (jefe/RRHH) vive en t_permisos_aprobaciones; estado aquí es el resultado agregado.
CREATE TABLE IF NOT EXISTS t_solicitudes_permisos (
    id_permiso              SERIAL,
    id_empleado             VARCHAR(20) NOT NULL,
    id_jefe_responsable     VARCHAR(20) NOT NULL,
    es_por_horas            BOOLEAN NOT NULL DEFAULT FALSE, -- Manejo del tiempo
    fecha_inicio            DATE NOT NULL,
    fecha_fin               DATE NOT NULL CHECK (fecha_fin >= fecha_inicio),
    hora_inicio             TIME,
    hora_fin                TIME,
    estado                  VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE' CHECK(estado IN ('PENDIENTE', 'EN_REVISION', 'APROBADO', 'RECHAZADO', 'CANCELADO')),     -- Resultado agregado del flujo de doble aprobación
    metodo_descuento        VARCHAR(20) CHECK(metodo_descuento IN ('N/A', 'VACACIONES', 'DINERO')),     -- Reglas de nómina y descuentos
    usr_insert              VARCHAR NOT NULL,
    fec_insert              TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update              VARCHAR,
    fec_update              TIMESTAMP WITHOUT TIME ZONE,
    usr_delete              VARCHAR,
    fec_delete              TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_permiso),
    FOREIGN KEY (id_empleado) REFERENCES t_empleados(id_usuario),
    FOREIGN KEY (id_jefe_responsable) REFERENCES t_usuarios(id_usuario),
    -- Si es_por_horas=TRUE, hora_inicio/hora_fin son obligatorias y coherentes;
    -- si es_por_horas=FALSE (permiso por días), no deben diligenciarse horas.
    CONSTRAINT chk_t_solicitudes_permisos_horas CHECK (
        (es_por_horas = TRUE AND hora_inicio IS NOT NULL AND hora_fin IS NOT NULL AND hora_fin > hora_inicio)
        OR
        (es_por_horas = FALSE AND hora_inicio IS NULL AND hora_fin IS NULL)
    )
);

-- Tabla puente: motivos múltiples por solicitud (N:M entre solicitudes y tipos de permiso).
-- detalle_motivo se usa únicamente cuando el tipo de permiso corresponde a "Otro Motivo".
CREATE TABLE IF NOT EXISTS t_solicitudes_permisos_motivos (
    id_permiso              INT NOT NULL,
    id_tipo_permiso         INT NOT NULL,
    detalle_motivo          VARCHAR(255),
    usr_insert              VARCHAR NOT NULL,
    fec_insert              TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update              VARCHAR,
    fec_update              TIMESTAMP WITHOUT TIME ZONE,
    usr_delete              VARCHAR,
    fec_delete              TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_permiso, id_tipo_permiso),
    FOREIGN KEY (id_permiso) REFERENCES t_solicitudes_permisos(id_permiso),
    FOREIGN KEY (id_tipo_permiso) REFERENCES t_tipos_permisos(id_tipo_permiso)
);

-- Doble aprobación por solicitud: exactamente 2 filas por permiso (JEFE y RRHH), creadas en PENDIENTE
-- al radicar la solicitud. El estado agregado en t_solicitudes_permisos se deriva de estas dos filas.
-- id_aprobador es nullable: el nivel JEFE se fija al crear (id_jefe_responsable), pero el nivel
-- RRHH no tiene un usuario fijo (cualquiera con permiso del módulo 27 puede resolverlo) y solo
-- queda registrado el que efectivamente aprobó/rechazó.
CREATE TABLE IF NOT EXISTS t_permisos_aprobaciones (
    id_aprobacion           SERIAL,
    id_permiso              INT NOT NULL,
    nivel_aprobacion        VARCHAR(10) NOT NULL CHECK (nivel_aprobacion IN ('JEFE', 'RRHH')),
    id_aprobador            VARCHAR(20),
    estado                  VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE' CHECK (estado IN ('PENDIENTE', 'APROBADO', 'RECHAZADO')),
    observacion             VARCHAR(255),
    fec_resolucion          TIMESTAMP WITHOUT TIME ZONE,
    usr_insert              VARCHAR NOT NULL,
    fec_insert              TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update              VARCHAR,
    fec_update              TIMESTAMP WITHOUT TIME ZONE,
    usr_delete              VARCHAR,
    fec_delete              TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_aprobacion),
    CONSTRAINT uq_permiso_nivel_aprobacion UNIQUE (id_permiso, nivel_aprobacion),
    FOREIGN KEY (id_permiso) REFERENCES t_solicitudes_permisos(id_permiso),
    FOREIGN KEY (id_aprobador) REFERENCES t_usuarios(id_usuario)
);

CREATE INDEX idx_permisos_aprobaciones_aprobador ON t_permisos_aprobaciones(id_aprobador, estado);

-- Evidencias adjuntas a la solicitud (0..N archivos). El archivo en sí NUNCA se guarda en la DB ni en el
-- proyecto: solo se persiste la referencia (proveedor + storage_key + url_publica) al servicio externo
-- (Bunny.net / Cloudflare / B2, ver app/helpers/FileStorage).
CREATE TABLE IF NOT EXISTS t_permisos_evidencias (
    id_evidencia            SERIAL,
    id_permiso              INT NOT NULL,
    proveedor               VARCHAR(20) NOT NULL DEFAULT 'BUNNY' CHECK (proveedor IN ('BUNNY', 'R2', 'B2', 'CLOUDFLARE_IMAGES')),
    storage_key             VARCHAR(255) NOT NULL,
    url_publica             VARCHAR(500),
    nombre_original         VARCHAR(255) NOT NULL,
    mime_type               VARCHAR(100) NOT NULL CHECK (mime_type IN ('image/jpeg', 'image/png', 'application/pdf')),
    tamano_bytes            INT NOT NULL CHECK (tamano_bytes > 0 AND tamano_bytes <= 10485760),
    usr_insert              VARCHAR NOT NULL,
    fec_insert              TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update              VARCHAR,
    fec_update              TIMESTAMP WITHOUT TIME ZONE,
    usr_delete              VARCHAR,
    fec_delete              TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_evidencia),
    FOREIGN KEY (id_permiso) REFERENCES t_solicitudes_permisos(id_permiso)
);

CREATE INDEX idx_permisos_evidencias_permiso ON t_permisos_evidencias(id_permiso);

-- Tabla de expansión para pintar el calendario de disponibilidad
CREATE TABLE IF NOT EXISTS t_permisos_dias_aprobados (
    id_dia_permiso          SERIAL,
    id_permiso              INT NOT NULL,
    id_empleado             VARCHAR(20) NOT NULL,
    fecha                   DATE NOT NULL,
    horas_aprobadas         NUMERIC(4,2) NOT NULL,
    usr_insert              VARCHAR NOT NULL,
    fec_insert              TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update              VARCHAR,
    fec_update              TIMESTAMP WITHOUT TIME ZONE,
    usr_delete              VARCHAR,
    fec_delete              TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_dia_permiso),
    FOREIGN KEY (id_permiso) REFERENCES t_solicitudes_permisos(id_permiso),
    FOREIGN KEY (id_empleado) REFERENCES t_empleados(id_usuario)
);

CREATE TABLE IF NOT EXISTS t_comunicados (
    id_comunicado        SERIAL,
    titulo               VARCHAR(100) NOT NULL,
    contenido            TEXT NOT NULL,
    categoria            VARCHAR(30) NOT NULL CHECK(categoria IN('GENERAL', 'URGENTE', 'EVENTO', 'INFORMACION', 'INSTITUCIONAL')),
    usr_insert           VARCHAR NOT NULL,
    fec_insert           TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update           VARCHAR,
    fec_update           TIMESTAMP WITHOUT TIME ZONE,
    usr_delete           VARCHAR,
    fec_delete           TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_comunicado)
);

-- Tabla auxiliar para tracking de comunicados vistos por usuario
CREATE TABLE IF NOT EXISTS t_comunicados_vistos (
    id_visto        SERIAL,
    id_comunicado   INTEGER NOT NULL,
    id_usuario      VARCHAR(20) NOT NULL,
    fec_visto       TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_insert      VARCHAR NOT NULL DEFAULT CURRENT_USER,
    fec_insert      TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_visto),
    FOREIGN KEY (id_comunicado) REFERENCES t_comunicados(id_comunicado),
    FOREIGN KEY (id_usuario) REFERENCES t_usuarios(id_usuario),
    CONSTRAINT uq_comunicado_usuario UNIQUE (id_comunicado, id_usuario)
);

CREATE INDEX IF NOT EXISTS idx_comunicados_vistos_usuario
    ON t_comunicados_vistos (id_usuario);

CREATE INDEX IF NOT EXISTS idx_comunicados_vistos_comunicado
    ON t_comunicados_vistos (id_comunicado);

-- Tabla de recuperación de contraseñas
CREATE TABLE IF NOT EXISTS t_recuperacion_contrasena (
    id_recuperacion SERIAL PRIMARY KEY,
    id_usuario VARCHAR(20) NOT NULL REFERENCES t_usuarios(id_usuario),
    token_hash VARCHAR(255) NOT NULL,
    fecha_expiracion TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    fecha_uso TIMESTAMP WITHOUT TIME ZONE,
    usr_insert VARCHAR NOT NULL,
    fec_insert TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_update VARCHAR,
    fec_update TIMESTAMP WITHOUT TIME ZONE,
    usr_delete VARCHAR,
    fec_delete TIMESTAMP WITHOUT TIME ZONE
);

-- Índices de rendimiento para consultas frecuentes
-- Ledger de ajustes manuales de saldo de vacaciones (saldo inicial al migrar a este sistema,
-- correcciones puntuales). Append-only: dias_ajuste positivo = crédito, negativo = débito
-- (p. ej. días ya disfrutados antes de existir este módulo). motivo es obligatorio para auditoría.
-- fun_calcular_saldo_vacaciones() suma estos ajustes al saldo calculado dinámicamente.
CREATE TABLE IF NOT EXISTS t_vacaciones_ajustes (
    id_ajuste           SERIAL,
    id_empleado         VARCHAR(20) NOT NULL,
    dias_ajuste         NUMERIC(5,2) NOT NULL CHECK (dias_ajuste <> 0),
    motivo              VARCHAR(255) NOT NULL,
    -- Ciclo aniversario (no calendario) al que pertenece el ajuste; deja de contar
    -- automáticamente cuando ese ciclo se reinicia (vacaciones no acumulables).
    fecha_ajuste        DATE NOT NULL DEFAULT CURRENT_DATE,
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_ajuste),
    FOREIGN KEY (id_empleado) REFERENCES t_empleados(id_usuario)
);

CREATE INDEX idx_vacaciones_ajustes_empleado ON t_vacaciones_ajustes (id_empleado);

CREATE INDEX idx_permisos_empleado ON t_solicitudes_permisos (id_empleado);
CREATE INDEX idx_permisos_dias_calendario ON t_permisos_dias_aprobados (id_empleado, fecha);


-- Índice de rendimiento
CREATE INDEX idx_t_municipios_departamento_id ON t_municipios (departamento_id);

-- Registros pendientes de verificación de correo (antes de crear usuario real)
CREATE TABLE IF NOT EXISTS t_registros_pendientes (
    id_registro_pendiente SERIAL PRIMARY KEY,
    id_usuario VARCHAR(20) NOT NULL UNIQUE,
    id_rol INT NOT NULL,
    username VARCHAR(30) NOT NULL UNIQUE,
    tipo_documento VARCHAR(20) NOT NULL CHECK (tipo_documento IN ('CC', 'PPT', 'CE')),
    primer_nombre VARCHAR(30) NOT NULL,
    segundo_nombre VARCHAR(30),
    primer_apellido VARCHAR(30) NOT NULL,
    segundo_apellido VARCHAR(30),
    correo VARCHAR(40) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    fecha_expiracion TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_insert VARCHAR NOT NULL,
    fec_insert TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_update VARCHAR,
    fec_update TIMESTAMP WITHOUT TIME ZONE,
    usr_delete VARCHAR,
    fec_delete TIMESTAMP WITHOUT TIME ZONE
);

CREATE INDEX idx_registros_pendientes_expiracion ON t_registros_pendientes(fecha_expiracion);

-- Tabla de verificación de correo electrónico
CREATE TABLE IF NOT EXISTS t_verificacion_correo (
    id_verificacion SERIAL PRIMARY KEY,
    id_registro_pendiente INT NOT NULL REFERENCES t_registros_pendientes(id_registro_pendiente),
    token_hash VARCHAR(255) NOT NULL,
    fecha_expiracion TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    fecha_uso TIMESTAMP WITHOUT TIME ZONE,
    usr_insert VARCHAR NOT NULL,
    fec_insert TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_update VARCHAR,
    fec_update TIMESTAMP WITHOUT TIME ZONE,
    usr_delete VARCHAR,
    fec_delete TIMESTAMP WITHOUT TIME ZONE
);

CREATE INDEX idx_recuperacion_token ON t_recuperacion_contrasena(token_hash);
CREATE INDEX idx_recuperacion_usuario ON t_recuperacion_contrasena(id_usuario);
CREATE INDEX idx_verificacion_token ON t_verificacion_correo(token_hash);
CREATE INDEX idx_verificacion_registro ON t_verificacion_correo(id_registro_pendiente);

-- Tabla de rate limiting para envío de correos (verificación y recuperación)
CREATE TABLE IF NOT EXISTS t_intentos_correo (
    id_intento SERIAL PRIMARY KEY,
    id_usuario VARCHAR(20) NOT NULL,
    tipo VARCHAR(30) NOT NULL CHECK (tipo IN ('verificacion', 'recuperacion')),
    contador INT NOT NULL DEFAULT 1,
    ultimo_intento TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_insert VARCHAR NOT NULL DEFAULT 'sistema',
    fec_insert TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_update VARCHAR,
    fec_update TIMESTAMP WITHOUT TIME ZONE,
    CONSTRAINT uq_intentos_usuario_tipo UNIQUE (id_usuario, tipo)
);

CREATE INDEX idx_intentos_usuario_tipo ON t_intentos_correo(id_usuario, tipo);
CREATE INDEX idx_intentos_ultimo ON t_intentos_correo(ultimo_intento);

-- Tabla de tokens persistentes para "Recordarme".
-- Diseño seguro: selector plano + hash SHA-256 del validator. NUNCA se guarda el validator en claro.
CREATE TABLE IF NOT EXISTS t_remember_tokens (
    id SERIAL PRIMARY KEY,
    selector VARCHAR(12) NOT NULL UNIQUE,
    token_hash CHAR(64) NOT NULL,
    id_usuario VARCHAR(20) NOT NULL REFERENCES t_usuarios(id_usuario),
    fec_expiracion TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_insert VARCHAR,
    fec_insert TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_update VARCHAR,
    fec_update TIMESTAMP WITHOUT TIME ZONE,
    usr_delete VARCHAR,
    fec_delete TIMESTAMP WITHOUT TIME ZONE
);

CREATE INDEX idx_remember_tokens_usuario ON t_remember_tokens(id_usuario) WHERE fec_delete IS NULL;

-- Tabla de control de códigos de acceso al registro de cuentas
CREATE TABLE IF NOT EXISTS t_codigos_registro (
    id_codigo          SERIAL PRIMARY KEY,
    codigo             VARCHAR(4) NOT NULL UNIQUE,
    tipo               VARCHAR(20) NOT NULL CHECK (tipo IN ('TEMPORAL', 'UNICO_USO')),
    fecha_expiracion   TIMESTAMP WITHOUT TIME ZONE,
    usado              BOOLEAN NOT NULL DEFAULT FALSE,
    usr_insert         VARCHAR NOT NULL,
    fec_insert         TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usr_update         VARCHAR,
    fec_update         TIMESTAMP WITHOUT TIME ZONE,
    usr_delete         VARCHAR,
    fec_delete         TIMESTAMP WITHOUT TIME ZONE
);

CREATE INDEX idx_codigos_registro_codigo ON t_codigos_registro(codigo);
CREATE INDEX idx_codigos_registro_tipo ON t_codigos_registro(tipo);
CREATE INDEX idx_codigos_registro_activo ON t_codigos_registro(tipo, usado, fec_delete);

CREATE TABLE IF NOT EXISTS t_sst_comite_miembros (
    id_miembro          SERIAL,
    nombre_completo     VARCHAR(100) NOT NULL,
    cargo               VARCHAR(50) NOT NULL,
    tipo_comite         VARCHAR(30) NOT NULL CHECK(tipo_comite IN ('COPASST', 'COMITE_CONVIVENCIA')),
    correo              VARCHAR(40),
    telefono            VARCHAR(15),
    orden_visualizacion INT NOT NULL DEFAULT 0,
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_miembro)
);

CREATE INDEX IF NOT EXISTS idx_sst_comite_tipo_orden
    ON t_sst_comite_miembros(tipo_comite, orden_visualizacion);

CREATE TABLE IF NOT EXISTS t_sst_buzon_quejas (
    id_queja            SERIAL,
    id_usuario          VARCHAR(20) NOT NULL,
    tipo_peticion       VARCHAR(30) NOT NULL CHECK(tipo_peticion IN ('QUEJA', 'SUGERENCIA', 'RECLAMO', 'DENUNCIA')),
    asunto              VARCHAR(150) NOT NULL,
    descripcion         TEXT NOT NULL,
    estado              VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE' CHECK(estado IN ('PENDIENTE', 'EN PROCESO', 'RESUELTO', 'CANCELADO_USUARIO', 'CANCELADO_ENCARGADO')),
    respuesta           TEXT,
    id_encargado        VARCHAR(20),
    fec_respuesta       TIMESTAMP WITHOUT TIME ZONE,
    cancelado_por       VARCHAR(20),
    fec_cancelacion     TIMESTAMP WITHOUT TIME ZONE,
    usr_insert          VARCHAR NOT NULL,
    fec_insert          TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    usr_update          VARCHAR,
    fec_update          TIMESTAMP WITHOUT TIME ZONE,
    usr_delete          VARCHAR,
    fec_delete          TIMESTAMP WITHOUT TIME ZONE,
    PRIMARY KEY (id_queja),
    FOREIGN KEY (id_usuario) REFERENCES t_usuarios(id_usuario),
    FOREIGN KEY (id_encargado) REFERENCES t_usuarios(id_usuario),
    FOREIGN KEY (cancelado_por) REFERENCES t_usuarios(id_usuario)
);

CREATE INDEX IF NOT EXISTS idx_sst_quejas_usuario
    ON t_sst_buzon_quejas(id_usuario, fec_insert DESC);

CREATE INDEX IF NOT EXISTS idx_sst_quejas_estado
    ON t_sst_buzon_quejas(estado, fec_insert DESC);

-- Auditoría SST

