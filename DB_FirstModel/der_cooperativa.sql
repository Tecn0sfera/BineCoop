
-- Tabla: Socio
CREATE TABLE Socio (
    id_socio SERIAL PRIMARY KEY,
    nombre VARCHAR(100),
    apellido VARCHAR(100),
    ci VARCHAR(20),
    email VARCHAR(32),
    fecha_ingreso DATE,
    fecha_registro DATE,
    estado VARCHAR(20)
);


-- Tabla: Aporte
CREATE TABLE Aporte (
    id_aporte SERIAL PRIMARY KEY,
    id_socio INTEGER REFERENCES Socio(id_socio),
    tipo_aporte VARCHAR(20),
    monto NUMERIC(10,2),
    fecha DATE
);

-- Tabla: Vivienda
CREATE TABLE Vivienda (
    id_vivienda SERIAL PRIMARY KEY,
    numero VARCHAR(10),
    tipo VARCHAR(50),
    metros_cuadrados NUMERIC(6,2),
    estado VARCHAR(20)
);

-- Tabla: DerechoUso
CREATE TABLE DerechoUso (
    id_derecho SERIAL PRIMARY KEY,
    id_socio INTEGER REFERENCES Socio(id_socio),
    id_vivienda INTEGER REFERENCES Vivienda(id_vivienda),
    fecha_adjudicacion DATE,
    transferible_a INTEGER REFERENCES MiembroFamiliar(id_miembro),
    estado VARCHAR(20)
);

-- Tabla: MiembroFamiliar
CREATE TABLE MiembroFamiliar (
    id_miembro SERIAL PRIMARY KEY,
    id_socio INTEGER REFERENCES Socio(id_socio),
    nombre VARCHAR(100),
    parentesco VARCHAR(50)
);

-- Tabla: Proyecto
CREATE TABLE Proyecto (
    id_proyecto SERIAL PRIMARY KEY,
    nombre VARCHAR(100),
    estado VARCHAR(20),
    fecha_inicio DATE,
    fecha_fin DATE
);

-- Tabla: ProyectoVivienda
CREATE TABLE ProyectoVivienda (
    id_proyecto INTEGER REFERENCES Proyecto(id_proyecto),
    id_vivienda INTEGER REFERENCES Vivienda(id_vivienda),
    PRIMARY KEY (id_proyecto, id_vivienda)
);

-- Tabla: InstitutoTecnico
CREATE TABLE InstitutoTecnico (
    id_instituto SERIAL PRIMARY KEY,
    nombre VARCHAR(100),
    tipo_asistencia VARCHAR(100)
);

-- Tabla: ProyectoInstituto
CREATE TABLE ProyectoInstituto (
    id_proyecto INTEGER REFERENCES Proyecto(id_proyecto),
    id_instituto INTEGER REFERENCES InstitutoTecnico(id_instituto),
    PRIMARY KEY (id_proyecto, id_instituto)
);

-- Tabla: OrganismoRegulador
CREATE TABLE OrganismoRegulador (
    id_organismo SERIAL PRIMARY KEY,
    nombre VARCHAR(100),
    tipo VARCHAR(50)
);

-- Tabla: ProyectoRegulador
CREATE TABLE ProyectoRegulador (
    id_proyecto INTEGER REFERENCES Proyecto(id_proyecto),
    id_organismo INTEGER REFERENCES OrganismoRegulador(id_organismo),
    PRIMARY KEY (id_proyecto, id_organismo)
);

-- Tabla: ComisionInterna
CREATE TABLE ComisionInterna (
    id_comision SERIAL PRIMARY KEY,
    tipo VARCHAR(50),
    funciones TEXT
);

-- Tabla: SocioComision
CREATE TABLE SocioComision (
    id_socio INTEGER REFERENCES Socio(id_socio),
    id_comision INTEGER REFERENCES ComisionInterna(id_comision),
    PRIMARY KEY (id_socio, id_comision)
);

-- Tabla: EventoComunitario
CREATE TABLE EventoComunitario (
    id_evento SERIAL PRIMARY KEY,
    nombre VARCHAR(100),
    fecha DATE,
    tipo VARCHAR(50)
);

-- Tabla: SocioEvento
CREATE TABLE SocioEvento (
    id_socio INTEGER REFERENCES Socio(id_socio),
    id_evento INTEGER REFERENCES EventoComunitario(id_evento),
    PRIMARY KEY (id_socio, id_evento)
);

-- Tabla: AreaComun
CREATE TABLE AreaComun (
    id_area SERIAL PRIMARY KEY,
    tipo VARCHAR(50),
    estado VARCHAR(20),
    responsable_fk INTEGER REFERENCES Socio(id_socio)
);

-- Tabla Visitante
CREATE TABLE Visitante (
    id_visitante SERIAL PRIMARY KEY,
    nombre VARCHAR(100),
    ci VARCHAR(20),
    email VARCHAR(100),
    fecha_registro DATE
);

-- Modificación en Socio para referenciar a Visitante
ALTER TABLE Socio
ADD COLUMN id_visitante INTEGER REFERENCES Visitante(id_visitante);

-- Tabla TipoPago
CREATE TABLE TipoPago (
    id_tipo_pago SERIAL PRIMARY KEY,
    descripcion VARCHAR(50)
);

-- Tabla Pago
CREATE TABLE Pago (
    id_pago SERIAL PRIMARY KEY,
    id_socio INTEGER REFERENCES Socio(id_socio),
    id_tipo_pago INTEGER REFERENCES TipoPago(id_tipo_pago),
    monto NUMERIC(10,2),
    fecha_pago DATE,
    observaciones TEXT
);

