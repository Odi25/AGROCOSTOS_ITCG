create database if not exists agrocostos_db
character set utf8mb4
collate utf8mb4_unicode_ci;

use agrocostos_db;

create table if not exists roles(
id_rol int auto_increment primary key,
nombre_rol varchar(50) not null unique,
descripcion varchar(255)
) engine=InnoDB;

create table if not exists usuarios(
id_usuario int auto_increment primary key,
id_rol int not null,
nombre_completo varchar(120) not null,
correo_electronico varchar(100) not null unique,
nombre_usuario varchar(50) not null unique,
password_hash varchar(255) not null,
activo tinyint(1) default 1,
fecha_creacion timestamp default current_timestamp,
foreign key (id_rol) references roles(id_rol) on delete restrict on update cascade
) engine=InnoDB;

create table if not exists cultivos(
id_cultivo int auto_increment primary key,
nombre_cultivo varchar(100) not null,
variedad varchar(100),
ciclo_vegetativo_dias int,
estimacion_rendimiento_ha decimal(10,2)
) engine=InnoDB;

create table if not exists parcelas(
id_parcela int auto_increment primary key,
id_cultivo int,
nombre_parcela varchar(100) not null,
superficie_ha decimal(8,2) not null,
tipo_riego varchar(50),
ubicacion_geo varchar(255),
foreign key (id_cultivo) references cultivos(id_cultivo) on delete set null on update cascade
) engine=InnoDB;

create table if not exists insumos_cat(
id_insumo int auto_increment primary key,
tipo_insumo enum('Fertilizante', 'Semilla', 'Agroquimico', 'Otro') not null,
nombre_comercial varchar(100) not null,
unidad_medida varchar(20) not null,
costo_unitario decimal(10,2) not null
) engine=InnoDB;

create table if not exists aplicaciones_insumo(
id_aplicacion int auto_increment primary key,
id_parcela int not null,
id_insumo int not null,
id_usuario int,
cantidad_aplicada decimal(10,2) not null,
costo_total decimal(10,2) not null,
fecha_aplicacion date not null,
foreign key (id_parcela) references parcelas(id_parcela) on delete cascade on update cascade,
foreign key (id_insumo) references insumos_cat(id_insumo) on delete restrict on update cascade,
foreign key (id_usuario) references usuarios(id_usuario) on delete set null on update cascade
) engine=InnoDB;

create table if not exists jornales(
id_jornal int auto_increment primary key,
id_parcela int not null,
id_usuario int,
tarea_realizada varchar(150) not null,
fecha_trabajo date not null,
horas_trabajadas decimal(5,2) not null,
tarifa_hora decimal(8,2) not null,
costo_total decimal(10,2) not null,
foreign key (id_parcela) references parcelas(id_parcela) on delete cascade on update cascade,
foreign key (id_usuario) references usuarios(id_usuario) on delete set null on update cascade
) engine=InnoDB;

create table if not exists costos_indirectos(
id_costo_indirecto int auto_increment primary key,
id_parcela int not null,
descripcion_costo varchar(200) not null,
monto_costo decimal (10,2) not null,
fecha_costo date not null,
foreign key(id_parcela) references parcelas(id_parcela) on delete cascade on update cascade
) engine=InnoDB;

insert into roles (id_rol, nombre_rol, descripcion) values
(1, 'Administrador', 'Permisos completos: administración de usuarios, respaldos y supervisión general'),
(2, 'Usuario del Sistema', 'Captura de datos operativos y consultas intermedias'),
(3, 'admin.reportes', 'Usuario de consulta: visualización de reportes, proyecciones e impresión');

insert into usuarios (id_rol, nombre_completo, email, nombre_usuario, password_hash, activo) values
(1, 'Administrador AGROCOSTOS', 'admin@agrocostos.tecnm.mx', 'admin', 'admin123', 1);



-- 1. Consultar todos los datos y columnas de una tabla
SELECT * FROM usuarios;
SELECT u.id_usuario, u.nombre_usuario, u.email, r.nombre_rol, u.activo 
FROM usuarios u
INNER JOIN roles r ON u.id_rol = r.id_rol;
-- 2. Consultar solo columnas específicas
SELECT nombre_completo, email FROM usuarios;

-- 3. Consultar con un filtro específico (ejemplo: solo usuarios activos)
SELECT * FROM usuarios WHERE activo = 1;

-- 4. Consultar ordenando los resultados (ejemplo: por fecha de creación)
SELECT * FROM usuarios ORDER BY fecha_creacion DESC;
