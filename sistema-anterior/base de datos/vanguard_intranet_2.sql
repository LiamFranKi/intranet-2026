-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 05-01-2026 a las 23:50:14
-- Versión del servidor: 11.8.5-MariaDB-ubu2404
-- Versión de PHP: 8.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `vanguard_intranet`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades`
--

CREATE TABLE `actividades` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `lugar` varchar(500) NOT NULL,
  `detalles` text NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades_personal`
--

CREATE TABLE `actividades_personal` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `lugar` varchar(500) NOT NULL,
  `detalles` text NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `permisos` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id` int(11) NOT NULL,
  `nombres` varchar(500) NOT NULL,
  `apellidos` varchar(500) NOT NULL,
  `dni` varchar(10) NOT NULL,
  `email` varchar(300) NOT NULL,
  `usuario` varchar(30) NOT NULL,
  `password` varchar(40) NOT NULL,
  `tipo` int(11) NOT NULL,
  `estado` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agendas`
--

CREATE TABLE `agendas` (
  `id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `fecha_registro` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alertas`
--

CREATE TABLE `alertas` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `contenido` text NOT NULL,
  `tipo` enum('PENSION','PRESENTE','TARDANZA','FALTA','MERITO','DEMERITO') NOT NULL,
  `dias` int(11) NOT NULL,
  `position` enum('ANTES','DESPUES') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `senders` text NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') NOT NULL,
  `asunto` varchar(500) NOT NULL,
  `email_remitente` varchar(500) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alquileres_cancha`
--

CREATE TABLE `alquileres_cancha` (
  `id` int(11) NOT NULL,
  `cancha_id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `observaciones` text NOT NULL,
  `inicio` datetime NOT NULL,
  `fin` datetime NOT NULL,
  `personal_id` int(11) NOT NULL,
  `fecha_registro` date NOT NULL,
  `precio` float(8,2) NOT NULL,
  `colegio_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alquileres_cancha_clientes`
--

CREATE TABLE `alquileres_cancha_clientes` (
  `id` int(11) NOT NULL,
  `nombres` varchar(500) NOT NULL,
  `email` varchar(500) NOT NULL,
  `telefono` varchar(500) NOT NULL,
  `direccion` varchar(500) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumnos`
--

CREATE TABLE `alumnos` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `apellido_paterno` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `apellido_materno` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `nombres` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `estado_civil` int(11) NOT NULL,
  `tipo_documento` int(11) NOT NULL,
  `nro_documento` varchar(20) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `pais_nacimiento_id` int(11) NOT NULL,
  `departamento_nacimiento_id` int(11) NOT NULL,
  `provincia_nacimiento_id` int(11) NOT NULL,
  `distrito_nacimiento_id` int(11) NOT NULL,
  `sexo` int(11) NOT NULL,
  `email` varchar(200) NOT NULL,
  `foto` varchar(500) NOT NULL,
  `fecha_inscripcion` date NOT NULL,
  `observaciones` text NOT NULL,
  `nro_hermanos` int(11) NOT NULL,
  `lugar_hermanos` int(11) NOT NULL,
  `religion` int(11) NOT NULL,
  `lengua_materna` int(11) NOT NULL,
  `segunda_lengua` int(11) NOT NULL,
  `codigo` varchar(100) NOT NULL,
  `discapacidad` varchar(10) NOT NULL,
  `domicilio` text NOT NULL,
  `estado_nacimiento` int(11) NOT NULL,
  `observaciones_nacimiento` text NOT NULL,
  `actividades_nacimiento` text NOT NULL,
  `controles_peso_talla` text NOT NULL,
  `otros_controles` text NOT NULL,
  `alergias` text NOT NULL,
  `experiencias_traumaticas` text NOT NULL,
  `tipo_sangre` int(11) NOT NULL,
  `enfermedades_sufridas` text NOT NULL,
  `vacunas` text NOT NULL,
  `trabajos` text NOT NULL,
  `foto_dni` varchar(100) NOT NULL,
  `registro_desde` enum('SISTEMA','ONLINE') NOT NULL,
  `seguro_id` bigint(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumnos_documentos`
--

CREATE TABLE `alumnos_documentos` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `archivo` varchar(500) NOT NULL,
  `alumno_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `apoderados`
--

CREATE TABLE `apoderados` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `nombres` varchar(500) NOT NULL,
  `apellido_paterno` varchar(500) NOT NULL,
  `apellido_materno` varchar(500) NOT NULL,
  `vive` enum('SI','NO') NOT NULL,
  `tipo_documento` int(11) NOT NULL,
  `nro_documento` varchar(20) NOT NULL,
  `estado_civil` int(11) NOT NULL,
  `telefono_fijo` varchar(100) NOT NULL,
  `telefono_celular` varchar(50) NOT NULL,
  `direccion` varchar(500) NOT NULL,
  `centro_trabajo_direccion` varchar(500) NOT NULL,
  `grado_instruccion` int(11) NOT NULL,
  `ocupacion` varchar(500) NOT NULL,
  `parentesco` int(11) NOT NULL,
  `vive_con_estudiante` enum('SI','NO') NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `pais_nacimiento_id` int(11) NOT NULL,
  `email` varchar(500) NOT NULL,
  `firma_digital` longtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `areas`
--

CREATE TABLE `areas` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `nivel_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `areas_cursos`
--

CREATE TABLE `areas_cursos` (
  `id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ar_internal_metadata`
--

CREATE TABLE `ar_internal_metadata` (
  `key` varchar(255) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas`
--

CREATE TABLE `asignaturas` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `aula_virtual` varchar(1000) NOT NULL,
  `habilitar_aula` enum('SI','NO') NOT NULL,
  `link_libro` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_actividades`
--

CREATE TABLE `asignaturas_actividades` (
  `id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `detalles` text NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_archivos`
--

CREATE TABLE `asignaturas_archivos` (
  `id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `nombre` varchar(300) NOT NULL,
  `archivo` varchar(300) NOT NULL,
  `visto` longtext NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `ciclo` int(11) NOT NULL,
  `enlace` text NOT NULL,
  `orden` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_asistencias`
--

CREATE TABLE `asignaturas_asistencias` (
  `id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo` enum('PRESENTE','TARDANZA','FALTA') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_criterios`
--

CREATE TABLE `asignaturas_criterios` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `abreviatura` varchar(30) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `ciclo` int(11) NOT NULL,
  `orden` int(11) NOT NULL,
  `peso` float(8,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_enlaces`
--

CREATE TABLE `asignaturas_enlaces` (
  `id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `enlace` varchar(1000) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `ciclo` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_examenes`
--

CREATE TABLE `asignaturas_examenes` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `titulo` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tipo_puntaje` enum('INDIVIDUAL','GENERAL') NOT NULL,
  `puntos_correcta` float(8,2) NOT NULL,
  `penalizar_incorrecta` enum('NO','SI') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `penalizacion_incorrecta` float(8,2) NOT NULL,
  `tiempo` int(11) NOT NULL,
  `intentos` int(11) NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') NOT NULL,
  `orden_preguntas` enum('PREDETERMINADO','ALEATORIO') NOT NULL,
  `fecha_desde` date NOT NULL,
  `fecha_hasta` date NOT NULL,
  `hora_desde` time NOT NULL,
  `hora_hasta` time NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `ciclo` int(11) NOT NULL,
  `preguntas_max` int(11) NOT NULL,
  `tipo` enum('VIRTUAL','PDF') NOT NULL,
  `archivo_pdf` varchar(500) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_examenes_preguntas`
--

CREATE TABLE `asignaturas_examenes_preguntas` (
  `id` int(11) NOT NULL,
  `examen_id` int(11) NOT NULL,
  `descripcion` longtext NOT NULL,
  `puntos` float(8,2) NOT NULL,
  `orden` int(11) NOT NULL,
  `tipo` enum('ALTERNATIVAS','COMPLETAR') NOT NULL,
  `imagen_puzzle` varchar(500) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_examenes_preguntas_alternativas`
--

CREATE TABLE `asignaturas_examenes_preguntas_alternativas` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(1000) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `correcta` enum('SI','NO') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_examenes_pruebas`
--

CREATE TABLE `asignaturas_examenes_pruebas` (
  `id` int(11) NOT NULL,
  `examen_id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `puntaje` float(8,2) NOT NULL,
  `preguntas` text NOT NULL,
  `respuestas` text NOT NULL,
  `correctas` int(11) NOT NULL,
  `incorrectas` int(11) NOT NULL,
  `expiracion` datetime NOT NULL,
  `estado` enum('ACTIVO','FINALIZADA') NOT NULL,
  `token` varchar(40) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_indicadores`
--

CREATE TABLE `asignaturas_indicadores` (
  `id` int(11) NOT NULL,
  `criterio_id` int(11) NOT NULL COMMENT 'Criterio de Asignatura',
  `descripcion` varchar(500) NOT NULL,
  `cuadros` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_notificaciones`
--

CREATE TABLE `asignaturas_notificaciones` (
  `id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `tipo` enum('PUBLICACION','TAREA','EXAMEN','ENLACE','ARCHIVO','VIDEO') NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `item_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_notificaciones_vistos`
--

CREATE TABLE `asignaturas_notificaciones_vistos` (
  `id` int(11) NOT NULL,
  `notificacion_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_participaciones`
--

CREATE TABLE `asignaturas_participaciones` (
  `asignatura_id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `fecha_hora` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_publicaciones`
--

CREATE TABLE `asignaturas_publicaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `contenido` text NOT NULL,
  `tipo_video` enum('YOUTUBE') NOT NULL,
  `video_id` varchar(500) NOT NULL,
  `images` text NOT NULL,
  `archivos` text NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `tipo` enum('ALUMNO','DOCENTE') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_publicaciones_comentarios`
--

CREATE TABLE `asignaturas_publicaciones_comentarios` (
  `id` int(11) NOT NULL,
  `publicacion_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `fecha_hora` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_sesiones`
--

CREATE TABLE `asignaturas_sesiones` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(1000) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `ciclo` int(11) NOT NULL,
  `live_url` varchar(1000) NOT NULL,
  `fecha_registro` datetime NOT NULL,
  `estado` enum('INACTIVA','ACTIVA') NOT NULL,
  `tipo` enum('ZOOM','HANGOUTS') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_tareas`
--

CREATE TABLE `asignaturas_tareas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(500) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `fecha_entrega` date NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `entregas` text NOT NULL,
  `visto` text NOT NULL COMMENT 'serialized',
  `archivos` text NOT NULL,
  `ciclo` int(11) NOT NULL,
  `enlace` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_tareas_archivos`
--

CREATE TABLE `asignaturas_tareas_archivos` (
  `id` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `nombre` varchar(300) NOT NULL,
  `archivo` varchar(300) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_tareas_entregas`
--

CREATE TABLE `asignaturas_tareas_entregas` (
  `id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `archivo` varchar(100) NOT NULL,
  `url` text NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `tipo` enum('ALUMNO','DOCENTE') NOT NULL,
  `mensaje` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_tareas_notas`
--

CREATE TABLE `asignaturas_tareas_notas` (
  `tarea_id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `nota` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_temas`
--

CREATE TABLE `asignaturas_temas` (
  `id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tema` varchar(1000) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas_videos`
--

CREATE TABLE `asignaturas_videos` (
  `id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `enlace` varchar(1000) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `ciclo` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `avatar_shop_items`
--

CREATE TABLE `avatar_shop_items` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `level` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `price` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `image` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `avatar_shop_sales`
--

CREATE TABLE `avatar_shop_sales` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `student_id` bigint(20) NOT NULL DEFAULT 0,
  `item_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `banco_temas`
--

CREATE TABLE `banco_temas` (
  `id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `nombre` varchar(1000) NOT NULL,
  `nivel_id` int(11) NOT NULL,
  `grado` int(11) NOT NULL,
  `detalles` longtext NOT NULL,
  `archivo` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bloques`
--

CREATE TABLE `bloques` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `nivel_id` int(11) NOT NULL,
  `total_notas` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bloques_cursos`
--

CREATE TABLE `bloques_cursos` (
  `id` int(11) NOT NULL,
  `bloque_id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boletas`
--

CREATE TABLE `boletas` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `sede_id` int(11) NOT NULL,
  `tipo_documento` int(11) NOT NULL,
  `dni` varchar(100) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `fecha` date NOT NULL,
  `serie` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `estado` enum('ACTIVO','ANULADO') NOT NULL,
  `fecha_anulado` date NOT NULL,
  `numero_anulado` int(11) NOT NULL,
  `tipo` enum('ALUMNO','DOCENTE','EXTERNO') NOT NULL,
  `estado_pago` enum('CANCELADO','PENDIENTE') NOT NULL,
  `fecha_pago` date NOT NULL,
  `impreso` enum('NO','SI') NOT NULL,
  `transferencia_gratuita` enum('NO','SI') NOT NULL,
  `tipo_pago` enum('EFECTIVO','TARJETA','YAPE','TRANSFERENCIA') NOT NULL,
  `tipo_tarjeta` enum('CREDITO','DEBITO') NOT NULL,
  `comision_tarjeta` float(8,2) NOT NULL,
  `comision_serie` int(11) NOT NULL,
  `comision_numero` int(11) NOT NULL,
  `json_generado` enum('NO','SI') NOT NULL,
  `entry` enum('NINGUNO','BCP RECAUDADORA','BCP IEP','BCP MARTHA','BCP YAPE','YAPE NUMERAL','EFECTIVO','TRANSFERENCIA') NOT NULL DEFAULT 'NINGUNO'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boletas_categorias`
--

CREATE TABLE `boletas_categorias` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `descripcion` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boletas_conceptos`
--

CREATE TABLE `boletas_conceptos` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `subcategoria_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `descripcion_proveedor` varchar(500) NOT NULL,
  `controlar_stock` enum('NO','SI') NOT NULL,
  `stock` int(11) NOT NULL,
  `stock_inicial` int(11) NOT NULL,
  `precio_inicial` float(8,2) NOT NULL,
  `ocultar` enum('SI','NO') NOT NULL,
  `codigo_existencia` varchar(50) NOT NULL,
  `tipo_existencia` varchar(50) NOT NULL,
  `codigo_unidad_medida` varchar(50) NOT NULL,
  `costo_unitario` float(8,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boletas_configuracion`
--

CREATE TABLE `boletas_configuracion` (
  `id` int(11) NOT NULL,
  `sede_id` int(11) NOT NULL,
  `serie` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `serie_mora` int(11) NOT NULL,
  `numero_mora` int(11) NOT NULL,
  `serie_2` int(11) NOT NULL,
  `numero_2` int(11) NOT NULL,
  `serie_3` int(11) NOT NULL,
  `numero_3` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boletas_detalles`
--

CREATE TABLE `boletas_detalles` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `boleta_id` int(11) NOT NULL,
  `concepto_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` float(8,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boletas_ingresos`
--

CREATE TABLE `boletas_ingresos` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `fecha` date NOT NULL,
  `estado` enum('ACTIVO','ANULADO') NOT NULL,
  `tipo` enum('BOLETA DE VENTA','FACTURA') NOT NULL,
  `serie` varchar(100) NOT NULL,
  `numero` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boletas_ingresos_detalles`
--

CREATE TABLE `boletas_ingresos_detalles` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `boleta_ingreso_id` int(11) NOT NULL,
  `concepto_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` float(8,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boletas_subcategorias`
--

CREATE TABLE `boletas_subcategorias` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `descripcion` text NOT NULL,
  `concar_igv` enum('SI','NO') NOT NULL,
  `concar_cuenta` varchar(500) NOT NULL,
  `starsoft_cuenta` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_categorias`
--

CREATE TABLE `caja_categorias` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `descripcion` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_conceptos`
--

CREATE TABLE `caja_conceptos` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `descripcion` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_registros`
--

CREATE TABLE `caja_registros` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `concepto_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `cantidad` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `nro_boleta` varchar(50) NOT NULL,
  `monto_total` float(8,2) NOT NULL,
  `recibo` enum('FACTURA','BOLETA VENTA','CAJA') NOT NULL,
  `nro_recibo` varchar(100) NOT NULL,
  `ruc` varchar(100) NOT NULL,
  `razon_social` varchar(500) NOT NULL,
  `tipo_pago` enum('CHEQUE','EFECTIVO','TARJETA') NOT NULL,
  `tipo` enum('INGRESO','EGRESO') NOT NULL,
  `responsable` varchar(500) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_saldos`
--

CREATE TABLE `caja_saldos` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `monto` float(8,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `canchas`
--

CREATE TABLE `canchas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `descripcion` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carpetas`
--

CREATE TABLE `carpetas` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `permisos` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cash_accounts`
--

CREATE TABLE `cash_accounts` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `cash_currency_id` bigint(20) NOT NULL,
  `cash_account_type_id` bigint(20) NOT NULL,
  `privacy` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cash_account_flows`
--

CREATE TABLE `cash_account_flows` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `description` text NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `type` int(11) NOT NULL DEFAULT 1,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cash_account_id` bigint(20) NOT NULL,
  `entry` enum('NINGUNO','BCP RECAUDADORA','BCP IEP','BCP MARTHA','BCP YAPE','YAPE NUMERAL','EFECTIVO','TRANSFERENCIA') NOT NULL DEFAULT 'NINGUNO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cash_account_types`
--

CREATE TABLE `cash_account_types` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cash_currencies`
--

CREATE TABLE `cash_currencies` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chats`
--

CREATE TABLE `chats` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `privacy` int(11) NOT NULL DEFAULT 1,
  `group_id` bigint(20) NOT NULL DEFAULT 0,
  `user1_id` bigint(20) NOT NULL DEFAULT 0,
  `user2_id` bigint(20) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `chat_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL DEFAULT 0,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colegios`
--

CREATE TABLE `colegios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `alias` varchar(100) NOT NULL,
  `estado` enum('ACTIVO','SUSPENDIDO') NOT NULL,
  `fecha_registro` date NOT NULL,
  `pais_id` int(11) NOT NULL,
  `departamento_id` int(11) NOT NULL,
  `provincia_id` int(11) NOT NULL,
  `distrito_id` int(11) NOT NULL,
  `centro_poblado` varchar(500) NOT NULL,
  `titulo_intranet` varchar(500) NOT NULL,
  `login_background` varchar(100) NOT NULL,
  `login_insignia` varchar(100) NOT NULL,
  `login_insignia_style` varchar(1000) NOT NULL,
  `anio_activo` int(11) NOT NULL,
  `codigo_modular` varchar(100) NOT NULL,
  `resolucion_creacion` varchar(100) NOT NULL,
  `ugel_codigo` varchar(100) NOT NULL,
  `ugel_nombre` varchar(200) NOT NULL,
  `ciclo_pensiones` int(11) NOT NULL,
  `inicio_pensiones` int(11) NOT NULL,
  `total_pensiones` int(11) NOT NULL,
  `moneda` varchar(20) NOT NULL,
  `ciclo_notas` int(11) NOT NULL,
  `inicio_notas` int(11) NOT NULL,
  `total_notas` int(11) NOT NULL,
  `integrar_office` enum('NO','SI') NOT NULL,
  `oauth_client_id` varchar(1000) NOT NULL,
  `oauth_secret` varchar(1000) NOT NULL,
  `oauth_redirect` varchar(1000) NOT NULL,
  `dominio_office` varchar(500) NOT NULL,
  `direccion_calendario` text NOT NULL,
  `rangos_ciclos_notas` text NOT NULL,
  `monto_adicional` float(8,2) NOT NULL,
  `descuento_minuto` float(8,2) NOT NULL COMMENT 'Descuento por entrada o salida',
  `impresiones_espaciado` int(11) NOT NULL,
  `emails_sugerencias` text NOT NULL,
  `rangos_mensajes` text NOT NULL,
  `descuento_alquiler_cancha` float(8,2) NOT NULL,
  `impresion_notas_debito` text NOT NULL,
  `pensiones_vencimiento` text NOT NULL,
  `videos_usuarios` text NOT NULL,
  `archivos_usuarios` text NOT NULL,
  `rangos_letras_primaria` text NOT NULL,
  `pago_comedor` float(8,2) NOT NULL,
  `fecha_comedor` date NOT NULL,
  `contactos_respuesta` longtext NOT NULL,
  `contactos_receptores` text NOT NULL,
  `contactos_trabajos_respuesta` longtext NOT NULL,
  `contactos_trabajos_receptores` text NOT NULL,
  `comision_tarjeta_debito` float(8,2) NOT NULL,
  `comision_tarjeta_credito` float(8,2) NOT NULL,
  `impresion_boletas` longtext NOT NULL,
  `clave_bloques` varchar(500) NOT NULL,
  `dias_tolerancia` int(11) NOT NULL,
  `fecha_verificacion_saldos` date NOT NULL,
  `receptores_verificacion_saldos` text NOT NULL,
  `ruc` varchar(11) NOT NULL,
  `razon_social` varchar(100) NOT NULL,
  `direccion` text NOT NULL,
  `bloquear_deudores` enum('NO','SI') NOT NULL,
  `anio_matriculas` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compendios`
--

CREATE TABLE `compendios` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `titulo` varchar(500) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `nivel_id` int(11) NOT NULL,
  `grado` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') NOT NULL,
  `personal_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compendios_paginas`
--

CREATE TABLE `compendios_paginas` (
  `id` int(11) NOT NULL,
  `compendio_id` int(11) NOT NULL,
  `pagina` int(11) NOT NULL,
  `borrable` enum('SI','NO') NOT NULL,
  `descripcion` varchar(1000) NOT NULL,
  `agregar_indice` enum('NO','SI') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compendios_paginas_bloques`
--

CREATE TABLE `compendios_paginas_bloques` (
  `id` int(11) NOT NULL,
  `pagina_id` int(11) NOT NULL,
  `tipo` enum('CONTENIDO','CUESTIONARIO','ARCHIVO') NOT NULL,
  `contenido` longtext NOT NULL COMMENT 'Sólo si el tipo es contenido',
  `titulo` varchar(1000) NOT NULL,
  `descripcion` text NOT NULL,
  `orden` int(11) NOT NULL,
  `archivo` varchar(500) NOT NULL,
  `ancho` varchar(100) NOT NULL,
  `alto` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compendios_paginas_bloques_preguntas`
--

CREATE TABLE `compendios_paginas_bloques_preguntas` (
  `id` int(11) NOT NULL,
  `bloque_id` int(11) NOT NULL,
  `descripcion` longtext NOT NULL,
  `orden` int(11) NOT NULL,
  `tipo_respuesta` enum('ALTERNATIVAS','TEXTO','COMPLETAR') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compendios_paginas_bloques_preguntas_alternativas`
--

CREATE TABLE `compendios_paginas_bloques_preguntas_alternativas` (
  `id` int(11) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `descripcion` varchar(1000) NOT NULL,
  `correcta` enum('SI','NO') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compendios_paginas_bloques_preguntas_respuestas`
--

CREATE TABLE `compendios_paginas_bloques_preguntas_respuestas` (
  `id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `alternativa_id` int(11) NOT NULL COMMENT 'La alternativa marcada',
  `respuesta` text NOT NULL COMMENT 'Si es texto'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunicados`
--

CREATE TABLE `comunicados` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `contenido` text NOT NULL,
  `archivo` text NOT NULL,
  `privacidad` text NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `tipo` enum('TEXTO','ARCHIVO') NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') NOT NULL,
  `show_in_home` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `concursos`
--

CREATE TABLE `concursos` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `anio` int(11) NOT NULL,
  `cursos` text NOT NULL,
  `puntos_correcta` float(8,2) NOT NULL,
  `puntos_blanco` float(8,2) NOT NULL,
  `puntos_incorrecta` float(8,2) NOT NULL,
  `respuestas` text NOT NULL,
  `puntajes` text NOT NULL,
  `estado` enum('INACTIVO','ACTIVO') NOT NULL,
  `tiempo` int(11) NOT NULL,
  `preguntas_max` int(11) NOT NULL,
  `expiracion` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `concursos_preguntas`
--

CREATE TABLE `concursos_preguntas` (
  `id` int(11) NOT NULL,
  `concurso_id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `puntos` float(8,2) NOT NULL,
  `orden` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `concursos_preguntas_alternativas`
--

CREATE TABLE `concursos_preguntas_alternativas` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(1000) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `correcta` enum('SI','NO') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `concursos_pruebas`
--

CREATE TABLE `concursos_pruebas` (
  `id` int(11) NOT NULL,
  `concurso_id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `respuestas` text NOT NULL,
  `expiracion` datetime NOT NULL,
  `estado` enum('ACTIVO','FINALIZADA') NOT NULL,
  `token` varchar(40) NOT NULL,
  `resultados` text NOT NULL,
  `preguntas` longtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `config`
--

CREATE TABLE `config` (
  `id` int(11) NOT NULL,
  `clave` varchar(500) NOT NULL,
  `valor` varchar(1000) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactos`
--

CREATE TABLE `contactos` (
  `id` int(11) NOT NULL,
  `nombres_apoderado` varchar(1000) NOT NULL,
  `telefono_fijo` varchar(500) NOT NULL,
  `telefono_celular` varchar(500) NOT NULL,
  `email` varchar(500) NOT NULL,
  `nombres_alumno` varchar(1000) NOT NULL,
  `dni_alumno` varchar(50) NOT NULL,
  `colegio` varchar(1000) NOT NULL,
  `grado_actual` varchar(500) NOT NULL,
  `fecha_registro` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactos_trabajos`
--

CREATE TABLE `contactos_trabajos` (
  `id` int(11) NOT NULL,
  `dni` varchar(50) NOT NULL,
  `nombres` varchar(500) NOT NULL,
  `apellidos` varchar(500) NOT NULL,
  `telefono` varchar(50) NOT NULL,
  `email` varchar(500) NOT NULL,
  `especialidad` varchar(500) NOT NULL,
  `archivo` varchar(100) NOT NULL,
  `fecha_registro` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `costos`
--

CREATE TABLE `costos` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `matricula` float(8,2) NOT NULL,
  `pension` float(8,2) NOT NULL,
  `agenda` float(8,2) NOT NULL,
  `tipo` enum('GENERAL','PERSONAL') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `nivel_id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `abreviatura` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `orden` int(11) NOT NULL,
  `examen_mensual` enum('NO','SI') NOT NULL,
  `peso_examen_mensual` float(8,2) NOT NULL,
  `imagen` varchar(50) NOT NULL,
  `link_libro` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos_criterios`
--

CREATE TABLE `cursos_criterios` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `abreviatura` varchar(30) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `ciclo` int(11) NOT NULL,
  `orden` int(11) NOT NULL,
  `peso` float(8,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
--

CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `codigo` varchar(100) NOT NULL COMMENT 'Se incluye el código del pais'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `distritos`
--

CREATE TABLE `distritos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `codigo` varchar(100) NOT NULL COMMENT 'Se incluye el código del pais'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos_matriculas`
--

CREATE TABLE `documentos_matriculas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `archivo` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuestas`
--

CREATE TABLE `encuestas` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `titulo` varchar(500) NOT NULL,
  `estado` enum('INACTIVO','ACTIVO') NOT NULL,
  `tipo` enum('APODERADO','ALUMNO','PERSONAL') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuestas_compartidos`
--

CREATE TABLE `encuestas_compartidos` (
  `id` int(11) NOT NULL,
  `encuesta_id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `tipo` enum('ALUMNO','APODERADO','PERSONAL') NOT NULL,
  `apoderado_id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `orden_preguntas` enum('PREDETERMINADO','ALEATORIO') NOT NULL,
  `estado` enum('PENDIENTE','FINALIZADO') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuestas_preguntas`
--

CREATE TABLE `encuestas_preguntas` (
  `id` int(11) NOT NULL,
  `encuesta_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `orden` int(11) NOT NULL,
  `tipo_respuesta` enum('ALTERNATIVAS','TEXTO','TITULO') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuestas_preguntas_alternativas`
--

CREATE TABLE `encuestas_preguntas_alternativas` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(1000) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `correcta` enum('SI','NO') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuestas_pruebas`
--

CREATE TABLE `encuestas_pruebas` (
  `id` int(11) NOT NULL,
  `compartido_id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `puntaje` float(8,2) NOT NULL,
  `respuestas` text NOT NULL,
  `correctas` int(11) NOT NULL,
  `incorrectas` int(11) NOT NULL,
  `expiracion` datetime NOT NULL,
  `estado` enum('ACTIVO','FINALIZADA') NOT NULL,
  `token` varchar(40) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `enrollment_incidents`
--

CREATE TABLE `enrollment_incidents` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `description` varchar(500) NOT NULL DEFAULT '',
  `enrollment_id` bigint(20) NOT NULL DEFAULT 0,
  `assignment_id` bigint(20) NOT NULL DEFAULT 0,
  `worker_id` bigint(20) NOT NULL DEFAULT 0,
  `type` int(11) NOT NULL DEFAULT 1,
  `points` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examenes`
--

CREATE TABLE `examenes` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `titulo` varchar(500) NOT NULL,
  `tipo_puntaje` enum('INDIVIDUAL','GENERAL') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `puntos_correcta` float(8,2) NOT NULL,
  `penalizar_incorrecta` enum('NO','SI') NOT NULL,
  `penalizacion_incorrecta` float(8,2) NOT NULL,
  `tipo` enum('ALUMNOS','PERSONAL') NOT NULL,
  `categorias` longtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examenes_bloques`
--

CREATE TABLE `examenes_bloques` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `titulo` varchar(500) NOT NULL,
  `puntos_correcta` float(8,2) NOT NULL,
  `bloque_id` int(11) NOT NULL,
  `cursos` text NOT NULL,
  `grado` int(11) NOT NULL,
  `total_preguntas` int(11) NOT NULL,
  `preguntas` longtext NOT NULL,
  `archivo` varchar(100) NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') NOT NULL,
  `archivado` enum('NO','SI') NOT NULL,
  `preguntas_max` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examenes_bloques_compartidos`
--

CREATE TABLE `examenes_bloques_compartidos` (
  `id` int(11) NOT NULL,
  `examen_id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `tiempo` int(11) NOT NULL,
  `intentos` int(11) NOT NULL,
  `expiracion` datetime NOT NULL,
  `ciclo` int(11) NOT NULL,
  `nro` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examenes_bloques_preguntas`
--

CREATE TABLE `examenes_bloques_preguntas` (
  `id` int(11) NOT NULL,
  `examen_id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `puntos` float(8,2) NOT NULL,
  `orden` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examenes_bloques_preguntas_alternativas`
--

CREATE TABLE `examenes_bloques_preguntas_alternativas` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(1000) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `correcta` enum('SI','NO') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examenes_bloques_pruebas`
--

CREATE TABLE `examenes_bloques_pruebas` (
  `id` int(11) NOT NULL,
  `compartido_id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `respuestas` text NOT NULL,
  `expiracion` datetime NOT NULL,
  `estado` enum('ACTIVO','FINALIZADA') NOT NULL,
  `token` varchar(40) NOT NULL,
  `resultados` text NOT NULL,
  `preguntas` longtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examenes_compartidos`
--

CREATE TABLE `examenes_compartidos` (
  `id` int(11) NOT NULL,
  `examen_id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `tiempo` int(11) NOT NULL,
  `intentos` int(11) NOT NULL,
  `expiracion` datetime NOT NULL,
  `orden_preguntas` enum('PREDETERMINADO','ALEATORIO') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examenes_preguntas`
--

CREATE TABLE `examenes_preguntas` (
  `id` int(11) NOT NULL,
  `examen_id` int(11) NOT NULL,
  `descripcion` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `puntos` float(8,2) NOT NULL,
  `orden` int(11) NOT NULL,
  `categoria` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examenes_preguntas_alternativas`
--

CREATE TABLE `examenes_preguntas_alternativas` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(1000) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `correcta` enum('SI','NO') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examenes_pruebas`
--

CREATE TABLE `examenes_pruebas` (
  `id` int(11) NOT NULL,
  `compartido_id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `puntaje` float(8,2) NOT NULL,
  `respuestas` text NOT NULL,
  `correctas` int(11) NOT NULL,
  `incorrectas` int(11) NOT NULL,
  `expiracion` datetime NOT NULL,
  `estado` enum('ACTIVO','FINALIZADA') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `token` varchar(40) NOT NULL,
  `resultados` longtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `familias`
--

CREATE TABLE `familias` (
  `id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `apoderado_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos`
--

CREATE TABLE `grupos` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `sede_id` int(11) NOT NULL,
  `nivel_id` int(11) NOT NULL,
  `grado` int(11) NOT NULL,
  `seccion` varchar(50) NOT NULL,
  `anio` int(11) NOT NULL,
  `turno_id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `enlace_archivos` varchar(1000) NOT NULL,
  `registro_habilitado` enum('SI','NO') NOT NULL,
  `aula_virtual` varchar(1000) NOT NULL,
  `horario_virtual` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos_horarios`
--

CREATE TABLE `grupos_horarios` (
  `id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `dia` int(11) NOT NULL,
  `hora_inicio` varchar(500) NOT NULL,
  `hora_final` varchar(500) NOT NULL,
  `tipo` enum('GRUPO','DOCENTE') NOT NULL,
  `anio` year(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos_talleres`
--

CREATE TABLE `grupos_talleres` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `fecha_registro` date NOT NULL,
  `anio` year(4) NOT NULL,
  `vencimiento_pago` date NOT NULL,
  `bloqueo_pago` date NOT NULL,
  `concepto_pago` varchar(100) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `horario_desde` varchar(100) NOT NULL,
  `horario_hasta` varchar(100) NOT NULL,
  `archivado` enum('NO','SI') NOT NULL,
  `cuenta_contable` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos_talleres_matriculas`
--

CREATE TABLE `grupos_talleres_matriculas` (
  `id` int(11) NOT NULL,
  `categoria` enum('BASICO','INTERMEDIO','AVANZADO') NOT NULL,
  `taller_id` int(11) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `apellido_paterno` varchar(500) NOT NULL,
  `apellido_materno` varchar(500) NOT NULL,
  `nombres` varchar(500) NOT NULL,
  `telefono` varchar(100) NOT NULL,
  `email` varchar(500) NOT NULL,
  `fecha_registro` datetime NOT NULL,
  `inicio` date NOT NULL,
  `fin` date NOT NULL,
  `precio` float(10,2) NOT NULL,
  `frecuencia` longtext NOT NULL,
  `serie` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `json` enum('NO','SI') NOT NULL,
  `estado` enum('ACTIVO','ANULADO') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupos_talleres_matriculasx`
--

CREATE TABLE `grupos_talleres_matriculasx` (
  `id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `taller_id` int(11) NOT NULL,
  `fecha_registro` date NOT NULL,
  `precio` float NOT NULL,
  `estado_pago` enum('PENDIENTE','CANCELADO') NOT NULL,
  `estado_generado` enum('PENDIENTE','GENERADO') NOT NULL,
  `fecha_pago` date NOT NULL,
  `monto` float NOT NULL,
  `mora` float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `impresiones`
--

CREATE TABLE `impresiones` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `tipo` enum('BOLETA','PAGO','MORA','COMISION') NOT NULL,
  `tipo_documento` enum('BOLETA','NOTA') NOT NULL,
  `numero` varchar(100) NOT NULL,
  `serie` varchar(100) NOT NULL,
  `estado` enum('ACTIVO','ANULADO') NOT NULL,
  `impreso` enum('NO','SI') NOT NULL,
  `fecha_impresion` date NOT NULL,
  `hora_impresion` time NOT NULL,
  `pago_id` int(11) NOT NULL,
  `boleta_id` int(11) NOT NULL,
  `enviado` enum('NO','SI') NOT NULL,
  `verificado` enum('NO','SI') NOT NULL,
  `fecha_anulado` date NOT NULL,
  `numero_anulado` int(10) UNSIGNED NOT NULL,
  `json_generado` enum('NO','SI') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `infracciones`
--

CREATE TABLE `infracciones` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `tipo` enum('MERITO','DEMERITO') NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `sancion` float(8,2) NOT NULL,
  `categoria_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `infracciones_categorias`
--

CREATE TABLE `infracciones_categorias` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `abreviatura` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoice_payments`
--

CREATE TABLE `invoice_payments` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `comments` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matriculas`
--

CREATE TABLE `matriculas` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `fecha_registro` date NOT NULL,
  `personal_id` int(11) NOT NULL,
  `estado` int(11) NOT NULL,
  `costo_id` int(11) NOT NULL,
  `recomendaciones` text NOT NULL COMMENT 'serialized',
  `ocultar` enum('NO','SI') NOT NULL,
  `descontar` enum('NO','SI') NOT NULL,
  `modalidad` enum('PRESENCIAL','VIRTUAL','SEMIPRESENCIAL') NOT NULL,
  `registro_desde` enum('SISTEMA','ONLINE') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matriculas_asistencias`
--

CREATE TABLE `matriculas_asistencias` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo` enum('PRESENTE','TARDANZA','TARDANZA_JUSTIFICADA','FALTA','FALTA_JUSTIFICADA') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matriculas_sanciones`
--

CREATE TABLE `matriculas_sanciones` (
  `id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `tipo` enum('MERITO','DEMERITO') NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `sancion` float(8,2) NOT NULL,
  `ciclo` int(11) NOT NULL,
  `fecha` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `id` int(11) NOT NULL,
  `remitente_id` int(11) NOT NULL COMMENT 'Usuario ID',
  `destinatario_id` int(11) NOT NULL COMMENT 'Usuario ID',
  `asunto` varchar(300) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `estado` enum('LEIDO','NO_LEIDO') NOT NULL,
  `tipo` enum('RECIBIDO','ENVIADO') NOT NULL,
  `borrado` enum('NO','SI') NOT NULL,
  `favorito` enum('NO','SI') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes_archivos`
--

CREATE TABLE `mensajes_archivos` (
  `id` int(11) NOT NULL,
  `mensaje_id` int(11) NOT NULL,
  `nombre_archivo` varchar(500) NOT NULL,
  `archivo` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `niveles`
--

CREATE TABLE `niveles` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `abreviatura` char(5) NOT NULL,
  `definicion_grado` int(11) NOT NULL,
  `grado_minimo` int(11) NOT NULL,
  `grado_maximo` int(11) NOT NULL,
  `nota_aprobatoria` float(8,2) NOT NULL,
  `tipo_calificacion` int(11) NOT NULL,
  `tipo_calificacion_final` int(11) NOT NULL,
  `nota_maxima` float(8,2) NOT NULL,
  `nota_minima` float(8,2) NOT NULL,
  `codigo_modular` varchar(500) NOT NULL,
  `avanzada` enum('NO','SI') NOT NULL,
  `monto_adelanto_matricula` float(8,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas`
--

CREATE TABLE `notas` (
  `id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `criterio_id` int(11) NOT NULL,
  `ciclo` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `nota` varchar(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas_detalles`
--

CREATE TABLE `notas_detalles` (
  `id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `ciclo` int(11) NOT NULL,
  `data` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas_examen_mensual`
--

CREATE TABLE `notas_examen_mensual` (
  `id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `ciclo` int(11) NOT NULL,
  `nro` int(11) NOT NULL,
  `nota` float(8,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `para` enum('TODOS','USUARIO') NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `destinatario_id` int(11) NOT NULL,
  `asunto` varchar(1000) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `estado` enum('NO ENVIADO','ENVIADO') NOT NULL,
  `contenido` longtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `objetivos`
--

CREATE TABLE `objetivos` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `data` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `lugar_pago` enum('CAJA','BANCO') NOT NULL,
  `forma_pago` enum('EFECTIVO','TARJETA','YAPE','TRANSFERENCIA') NOT NULL,
  `tipo_tarjeta` enum('CREDITO','DEBITO') NOT NULL,
  `comision_tarjeta` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `nro_pago` int(11) NOT NULL,
  `monto` float(8,2) NOT NULL,
  `mora` float(8,2) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `tipo` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `observaciones` text NOT NULL,
  `personal_id` int(11) NOT NULL,
  `estado` enum('ACTIVO','ANULADO') NOT NULL,
  `fecha_anulado` date NOT NULL,
  `estado_pago` enum('CANCELADO','PENDIENTE') NOT NULL,
  `fecha_cancelado` date NOT NULL,
  `incluye_agenda` enum('NO','SI') NOT NULL,
  `nro_movimiento_banco` varchar(500) NOT NULL,
  `nro_movimiento_importado` varchar(500) NOT NULL,
  `banco` enum('BBVA','BCP') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_comedor`
--

CREATE TABLE `pagos_comedor` (
  `id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `descripcion` varchar(1000) NOT NULL,
  `monto` float(8,2) NOT NULL,
  `fecha_registro` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_comedor_fechas`
--

CREATE TABLE `pagos_comedor_fechas` (
  `id` int(11) NOT NULL,
  `pago_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `monto` float(8,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_historial`
--

CREATE TABLE `pagos_historial` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `archivo` varchar(500) NOT NULL,
  `fecha` date NOT NULL,
  `data` text NOT NULL,
  `impreso` enum('NO','SI') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paises`
--

CREATE TABLE `paises` (
  `id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `codigo` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal`
--

CREATE TABLE `personal` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `sexo` int(11) NOT NULL,
  `telefono_fijo` varchar(50) NOT NULL,
  `telefono_celular` varchar(50) NOT NULL,
  `linea_celular` int(11) NOT NULL,
  `direccion` varchar(500) NOT NULL,
  `foto` varchar(100) NOT NULL,
  `nombres` varchar(500) NOT NULL,
  `apellidos` varchar(500) NOT NULL,
  `tipo_documento` int(11) NOT NULL,
  `nro_documento` varchar(20) NOT NULL,
  `grado_instruccion` int(11) NOT NULL,
  `profesion` varchar(500) NOT NULL,
  `cargo` varchar(500) NOT NULL,
  `email` varchar(500) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `pais_nacimiento_id` int(11) NOT NULL,
  `estado_civil` int(11) NOT NULL,
  `tipo_contrato` int(11) NOT NULL,
  `observaciones` text NOT NULL,
  `departamento_nacimiento_id` int(11) NOT NULL,
  `provincia_nacimiento_id` int(11) NOT NULL,
  `distrito_nacimiento_id` int(11) NOT NULL,
  `domicilio_pais_id` int(11) NOT NULL,
  `domicilio_departamento_id` int(11) NOT NULL,
  `domicilio_provincia_id` int(11) NOT NULL,
  `domicilio_distrito_id` int(11) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time NOT NULL,
  `resena` text NOT NULL,
  `mostrar_app` enum('SI','NO') NOT NULL,
  `link_aula` varchar(1000) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_horario`
--

CREATE TABLE `personal_horario` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `titulo` varchar(500) NOT NULL,
  `grupo` varchar(500) NOT NULL,
  `fecha` date NOT NULL,
  `inicio` time NOT NULL,
  `fin` time NOT NULL,
  `dia` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prematriculas`
--

CREATE TABLE `prematriculas` (
  `id` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `entrevista_fecha_hora` datetime NOT NULL,
  `personal_id` int(11) NOT NULL,
  `ingreso` enum('NO','SI') NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `data` longtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promedios`
--

CREATE TABLE `promedios` (
  `id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `ciclo` int(11) NOT NULL,
  `promedio` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `provincias`
--

CREATE TABLE `provincias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `codigo` varchar(100) NOT NULL COMMENT 'Se incluye el código del pais'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicaciones`
--

CREATE TABLE `publicaciones` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `contenido` text NOT NULL,
  `tipo_video` enum('YOUTUBE') NOT NULL,
  `video_id` varchar(500) NOT NULL,
  `images` text NOT NULL,
  `archivos` text NOT NULL,
  `privacidad` text NOT NULL,
  `fecha_hora` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schema_migrations`
--

CREATE TABLE `schema_migrations` (
  `version` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sedes`
--

CREATE TABLE `sedes` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `nombre` varchar(500) NOT NULL,
  `codigo_sunat` varchar(50) NOT NULL,
  `prefijo_boleta` varchar(10) NOT NULL,
  `direccion` varchar(1000) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sistema`
--

CREATE TABLE `sistema` (
  `id` int(11) NOT NULL,
  `detalle` text NOT NULL,
  `whatsapp` text NOT NULL,
  `correo` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system_pages`
--

CREATE TABLE `system_pages` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `url` tinytext NOT NULL,
  `display_location` int(11) NOT NULL DEFAULT 0 COMMENT '0: No Display, 1: Sidebar, 2: Menú, 3: Category',
  `display_type` int(11) NOT NULL DEFAULT 0 COMMENT '0: No Display, 1: Url, 2: Modal',
  `icon` varchar(255) NOT NULL DEFAULT '',
  `parent_id` bigint(20) DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT 0,
  `operations_map` longtext NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system_page_permissions`
--

CREATE TABLE `system_page_permissions` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `system_page_id` bigint(20) NOT NULL,
  `role` enum('ADMINISTRADOR','DIRECTOR','ALUMNO','APODERADO','DOCENTE','AUXILIAR','SECRETARIA','CAJERO','ENFERMERA','PROMOTORIA','COORDINADOR','PSICOLOGA','PERSONALIZADO','ASISTENCIA') NOT NULL,
  `operations` bigint(20) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system_resources`
--

CREATE TABLE `system_resources` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `operations_map` longtext NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system_resource_permissions`
--

CREATE TABLE `system_resource_permissions` (
  `id` bigint(20) NOT NULL,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `system_resource_id` bigint(20) NOT NULL,
  `role` enum('ADMINISTRADOR','DIRECTOR','ALUMNO','APODERADO','DOCENTE','AUXILIAR','SECRETARIA','CAJERO','ENFERMERA','PROMOTORIA','COORDINADOR','PSICOLOGA','PERSONALIZADO','ASISTENCIA') NOT NULL,
  `operations` bigint(20) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `puntos_correcta` float(8,2) NOT NULL,
  `desde` datetime NOT NULL,
  `hasta` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas_preguntas`
--

CREATE TABLE `tareas_preguntas` (
  `id` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `puntos` float(8,2) NOT NULL,
  `orden` int(11) NOT NULL,
  `tipo_respuesta` enum('ALTERNATIVAS','TEXTO') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas_preguntas_alternativas`
--

CREATE TABLE `tareas_preguntas_alternativas` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(1000) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `correcta` enum('SI','NO') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas_pruebas`
--

CREATE TABLE `tareas_pruebas` (
  `id` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `respuestas` text NOT NULL,
  `expiracion` datetime NOT NULL,
  `estado` enum('ACTIVO','FINALIZADA') NOT NULL,
  `token` varchar(40) NOT NULL,
  `resultados` text NOT NULL,
  `preguntas` longtext NOT NULL,
  `puntaje` float(8,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `topico_atenciones`
--

CREATE TABLE `topico_atenciones` (
  `id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `motivo` text NOT NULL,
  `tratamiento` text NOT NULL,
  `personal_id` int(11) NOT NULL,
  `tipo` enum('ALUMNO','APODERADO') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajadores`
--

CREATE TABLE `trabajadores` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `nombres` varchar(500) NOT NULL,
  `apellidos` varchar(500) NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajadores_asistencia`
--

CREATE TABLE `trabajadores_asistencia` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `tipo` enum('ENTRADA','SALIDA') NOT NULL,
  `fecha` date NOT NULL,
  `hora_permitida` time NOT NULL,
  `hora_real` time NOT NULL,
  `minutos_tardanza` int(11) NOT NULL,
  `descuento_minuto` float(8,2) NOT NULL,
  `descuento` float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajadores_faltas`
--

CREATE TABLE `trabajadores_faltas` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `asistencia` enum('PRESENTE','FALTA') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajos`
--

CREATE TABLE `trabajos` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `fecha_entrega` date NOT NULL,
  `personal_id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `entregas` text NOT NULL,
  `visto` text NOT NULL COMMENT 'serialized',
  `archivos` text NOT NULL,
  `archivos_subidos` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

CREATE TABLE `turnos` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `abreviatura` varchar(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `apoderado_id` int(11) NOT NULL,
  `usuario` varchar(30) NOT NULL,
  `password` varchar(40) NOT NULL,
  `tipo` enum('ADMINISTRADOR','DIRECTOR','ALUMNO','APODERADO','DOCENTE','AUXILIAR','SECRETARIA','CAJERO','ENFERMERA','PROMOTORIA','COORDINADOR','PSICOLOGA','PERSONALIZADO','ASISTENCIA') NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') NOT NULL,
  `ms_email` varchar(500) NOT NULL,
  `ms_access_token` text NOT NULL,
  `ms_refresh_token` text NOT NULL,
  `ms_id_token` text NOT NULL,
  `auth_token` text NOT NULL,
  `permisos` text NOT NULL,
  `cambiar_password` enum('NO','SI') NOT NULL,
  `app_terminos_aceptados` enum('NO','SI') NOT NULL,
  `cambiar_datos` enum('SI','NO') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_devices`
--

CREATE TABLE `usuarios_devices` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `device_token` longtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_tokens`
--

CREATE TABLE `usuarios_tokens` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(40) NOT NULL,
  `expiracion` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `placa` varchar(100) NOT NULL,
  `conductor` varchar(500) NOT NULL,
  `descripcion` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos_pasajeros`
--

CREATE TABLE `vehiculos_pasajeros` (
  `id` int(11) NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `vehiculo_id` int(11) NOT NULL,
  `orden` int(11) NOT NULL,
  `direccion` varchar(1000) NOT NULL,
  `lat` varchar(500) NOT NULL,
  `long` varchar(500) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `verificaciones_deudas`
--

CREATE TABLE `verificaciones_deudas` (
  `id` int(11) NOT NULL,
  `matricula_id` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `deudas` longtext NOT NULL,
  `conforme` enum('SI','NO') NOT NULL,
  `mensaje` longtext NOT NULL,
  `fecha_limite` date NOT NULL,
  `apoderado_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visitantes`
--

CREATE TABLE `visitantes` (
  `id` int(11) NOT NULL,
  `fecha_registro` datetime NOT NULL,
  `fecha` date NOT NULL,
  `dni` char(12) NOT NULL,
  `apoderado` varchar(500) NOT NULL,
  `telefono` varchar(50) NOT NULL,
  `email` varchar(200) NOT NULL,
  `grado` varchar(50) NOT NULL,
  `nivel` varchar(50) NOT NULL,
  `alumno` varchar(500) NOT NULL,
  `direccion` varchar(200) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `actividades_personal`
--
ALTER TABLE `actividades_personal`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `agendas`
--
ALTER TABLE `agendas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `alertas`
--
ALTER TABLE `alertas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `alquileres_cancha`
--
ALTER TABLE `alquileres_cancha`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `alquileres_cancha_clientes`
--
ALTER TABLE `alquileres_cancha_clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `alumnos` ADD FULLTEXT KEY `apellido_paterno` (`apellido_paterno`,`apellido_materno`,`nombres`);

--
-- Indices de la tabla `alumnos_documentos`
--
ALTER TABLE `alumnos_documentos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `apoderados`
--
ALTER TABLE `apoderados`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `apoderados` ADD FULLTEXT KEY `nombres` (`nombres`,`apellido_paterno`,`apellido_materno`,`nro_documento`);

--
-- Indices de la tabla `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `areas_cursos`
--
ALTER TABLE `areas_cursos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ar_internal_metadata`
--
ALTER TABLE `ar_internal_metadata`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `asignaturas`
--
ALTER TABLE `asignaturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `colegio_id` (`colegio_id`,`grupo_id`,`curso_id`,`personal_id`,`habilitar_aula`) USING BTREE;

--
-- Indices de la tabla `asignaturas_actividades`
--
ALTER TABLE `asignaturas_actividades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asignaturas_archivos`
--
ALTER TABLE `asignaturas_archivos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asignaturas_asistencias`
--
ALTER TABLE `asignaturas_asistencias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asignaturas_criterios`
--
ALTER TABLE `asignaturas_criterios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asignaturas_enlaces`
--
ALTER TABLE `asignaturas_enlaces`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asignaturas_examenes`
--
ALTER TABLE `asignaturas_examenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asignatura_id` (`asignatura_id`,`ciclo`);

--
-- Indices de la tabla `asignaturas_examenes_preguntas`
--
ALTER TABLE `asignaturas_examenes_preguntas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `examen_id` (`examen_id`);

--
-- Indices de la tabla `asignaturas_examenes_preguntas_alternativas`
--
ALTER TABLE `asignaturas_examenes_preguntas_alternativas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asignaturas_examenes_pruebas`
--
ALTER TABLE `asignaturas_examenes_pruebas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `examen_id` (`examen_id`,`matricula_id`,`estado`),
  ADD KEY `id` (`id`,`token`);

--
-- Indices de la tabla `asignaturas_indicadores`
--
ALTER TABLE `asignaturas_indicadores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asignaturas_notificaciones`
--
ALTER TABLE `asignaturas_notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`,`asignatura_id`,`tipo`,`fecha_hora`,`item_id`);

--
-- Indices de la tabla `asignaturas_notificaciones_vistos`
--
ALTER TABLE `asignaturas_notificaciones_vistos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fecha_hora` (`fecha_hora`,`notificacion_id`,`usuario_id`) USING BTREE;

--
-- Indices de la tabla `asignaturas_participaciones`
--
ALTER TABLE `asignaturas_participaciones`
  ADD KEY `asignatura_id` (`asignatura_id`,`matricula_id`);

--
-- Indices de la tabla `asignaturas_publicaciones`
--
ALTER TABLE `asignaturas_publicaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asignatura_id` (`asignatura_id`);

--
-- Indices de la tabla `asignaturas_publicaciones_comentarios`
--
ALTER TABLE `asignaturas_publicaciones_comentarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asignaturas_sesiones`
--
ALTER TABLE `asignaturas_sesiones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asignaturas_tareas`
--
ALTER TABLE `asignaturas_tareas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asignaturas_tareas_archivos`
--
ALTER TABLE `asignaturas_tareas_archivos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asignaturas_tareas_entregas`
--
ALTER TABLE `asignaturas_tareas_entregas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asignaturas_temas`
--
ALTER TABLE `asignaturas_temas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asignaturas_videos`
--
ALTER TABLE `asignaturas_videos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `avatar_shop_items`
--
ALTER TABLE `avatar_shop_items`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `avatar_shop_sales`
--
ALTER TABLE `avatar_shop_sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_avatar_shop_sales_on_item_id` (`item_id`);

--
-- Indices de la tabla `banco_temas`
--
ALTER TABLE `banco_temas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `bloques`
--
ALTER TABLE `bloques`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `bloques_cursos`
--
ALTER TABLE `bloques_cursos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `boletas`
--
ALTER TABLE `boletas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `colegio_id` (`colegio_id`,`serie`,`numero`,`fecha`);

--
-- Indices de la tabla `boletas_categorias`
--
ALTER TABLE `boletas_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `boletas_conceptos`
--
ALTER TABLE `boletas_conceptos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `boletas_configuracion`
--
ALTER TABLE `boletas_configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `boletas_detalles`
--
ALTER TABLE `boletas_detalles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `boletas_ingresos`
--
ALTER TABLE `boletas_ingresos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `boletas_ingresos_detalles`
--
ALTER TABLE `boletas_ingresos_detalles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `boletas_subcategorias`
--
ALTER TABLE `boletas_subcategorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `caja_categorias`
--
ALTER TABLE `caja_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `caja_conceptos`
--
ALTER TABLE `caja_conceptos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `caja_registros`
--
ALTER TABLE `caja_registros`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `caja_saldos`
--
ALTER TABLE `caja_saldos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `canchas`
--
ALTER TABLE `canchas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `carpetas`
--
ALTER TABLE `carpetas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cash_accounts`
--
ALTER TABLE `cash_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_cash_accounts_on_cash_currency_id` (`cash_currency_id`),
  ADD KEY `index_cash_accounts_on_cash_account_type_id` (`cash_account_type_id`);

--
-- Indices de la tabla `cash_account_flows`
--
ALTER TABLE `cash_account_flows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_cash_account_flows_on_cash_account_id` (`cash_account_id`);

--
-- Indices de la tabla `cash_account_types`
--
ALTER TABLE `cash_account_types`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cash_currencies`
--
ALTER TABLE `cash_currencies`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_chat_messages_on_chat_id` (`chat_id`);

--
-- Indices de la tabla `colegios`
--
ALTER TABLE `colegios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `compendios`
--
ALTER TABLE `compendios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `compendios_paginas`
--
ALTER TABLE `compendios_paginas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `compendios_paginas_bloques`
--
ALTER TABLE `compendios_paginas_bloques`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `compendios_paginas_bloques_preguntas`
--
ALTER TABLE `compendios_paginas_bloques_preguntas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `compendios_paginas_bloques_preguntas_alternativas`
--
ALTER TABLE `compendios_paginas_bloques_preguntas_alternativas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `compendios_paginas_bloques_preguntas_respuestas`
--
ALTER TABLE `compendios_paginas_bloques_preguntas_respuestas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `concursos`
--
ALTER TABLE `concursos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `concursos_preguntas`
--
ALTER TABLE `concursos_preguntas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `concursos_preguntas_alternativas`
--
ALTER TABLE `concursos_preguntas_alternativas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `concursos_pruebas`
--
ALTER TABLE `concursos_pruebas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `contactos_trabajos`
--
ALTER TABLE `contactos_trabajos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `costos`
--
ALTER TABLE `costos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `colegio_id` (`colegio_id`,`nivel_id`,`id`);

--
-- Indices de la tabla `cursos_criterios`
--
ALTER TABLE `cursos_criterios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `distritos`
--
ALTER TABLE `distritos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `documentos_matriculas`
--
ALTER TABLE `documentos_matriculas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `encuestas`
--
ALTER TABLE `encuestas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `encuestas_compartidos`
--
ALTER TABLE `encuestas_compartidos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `encuestas_preguntas`
--
ALTER TABLE `encuestas_preguntas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `encuestas_preguntas_alternativas`
--
ALTER TABLE `encuestas_preguntas_alternativas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `encuestas_pruebas`
--
ALTER TABLE `encuestas_pruebas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `enrollment_incidents`
--
ALTER TABLE `enrollment_incidents`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `examenes`
--
ALTER TABLE `examenes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `examenes_bloques`
--
ALTER TABLE `examenes_bloques`
  ADD PRIMARY KEY (`id`),
  ADD KEY `colegio_id` (`colegio_id`,`personal_id`,`bloque_id`);

--
-- Indices de la tabla `examenes_bloques_compartidos`
--
ALTER TABLE `examenes_bloques_compartidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `examen_id` (`examen_id`,`grupo_id`,`ciclo`,`nro`);

--
-- Indices de la tabla `examenes_bloques_preguntas`
--
ALTER TABLE `examenes_bloques_preguntas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `examen_id` (`examen_id`,`curso_id`);

--
-- Indices de la tabla `examenes_bloques_preguntas_alternativas`
--
ALTER TABLE `examenes_bloques_preguntas_alternativas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pregunta_id` (`pregunta_id`);

--
-- Indices de la tabla `examenes_bloques_pruebas`
--
ALTER TABLE `examenes_bloques_pruebas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compartido_id` (`compartido_id`,`matricula_id`);

--
-- Indices de la tabla `examenes_compartidos`
--
ALTER TABLE `examenes_compartidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `examen_id` (`examen_id`,`grupo_id`);

--
-- Indices de la tabla `examenes_preguntas`
--
ALTER TABLE `examenes_preguntas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `examen_id` (`examen_id`);

--
-- Indices de la tabla `examenes_preguntas_alternativas`
--
ALTER TABLE `examenes_preguntas_alternativas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pregunta_id` (`pregunta_id`);

--
-- Indices de la tabla `examenes_pruebas`
--
ALTER TABLE `examenes_pruebas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `familias`
--
ALTER TABLE `familias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `grupos`
--
ALTER TABLE `grupos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `grupos_horarios`
--
ALTER TABLE `grupos_horarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `grupos_talleres`
--
ALTER TABLE `grupos_talleres`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `grupos_talleres_matriculas`
--
ALTER TABLE `grupos_talleres_matriculas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `grupos_talleres_matriculasx`
--
ALTER TABLE `grupos_talleres_matriculasx`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `impresiones`
--
ALTER TABLE `impresiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tipo_documento` (`tipo_documento`,`numero`,`serie`,`estado`,`impreso`,`fecha_impresion`,`pago_id`,`boleta_id`);

--
-- Indices de la tabla `infracciones`
--
ALTER TABLE `infracciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `infracciones_categorias`
--
ALTER TABLE `infracciones_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `invoice_payments`
--
ALTER TABLE `invoice_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `matriculas`
--
ALTER TABLE `matriculas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `colegio_id` (`colegio_id`,`grupo_id`,`alumno_id`,`fecha_registro`,`personal_id`,`estado`,`costo_id`);

--
-- Indices de la tabla `matriculas_asistencias`
--
ALTER TABLE `matriculas_asistencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matricula_id` (`matricula_id`,`fecha`,`tipo`);

--
-- Indices de la tabla `matriculas_sanciones`
--
ALTER TABLE `matriculas_sanciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `mensajes_archivos`
--
ALTER TABLE `mensajes_archivos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `niveles`
--
ALTER TABLE `niveles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `matricula_id` (`matricula_id`),
  ADD KEY `asignatura_id` (`asignatura_id`),
  ADD KEY `trimestre` (`ciclo`),
  ADD KEY `criterio_numero` (`criterio_id`);

--
-- Indices de la tabla `notas_detalles`
--
ALTER TABLE `notas_detalles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `asignatura_id` (`asignatura_id`,`matricula_id`,`ciclo`),
  ADD KEY `asignatura_id_2` (`asignatura_id`,`matricula_id`,`ciclo`);

--
-- Indices de la tabla `notas_examen_mensual`
--
ALTER TABLE `notas_examen_mensual`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matricula_id` (`matricula_id`,`asignatura_id`,`ciclo`,`nro`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `objetivos`
--
ALTER TABLE `objetivos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `matricula_id` (`matricula_id`,`nro_pago`,`tipo`);

--
-- Indices de la tabla `pagos_comedor`
--
ALTER TABLE `pagos_comedor`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pagos_comedor_fechas`
--
ALTER TABLE `pagos_comedor_fechas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pagos_historial`
--
ALTER TABLE `pagos_historial`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `paises`
--
ALTER TABLE `paises`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `personal` ADD FULLTEXT KEY `nombres` (`nombres`,`apellidos`,`nro_documento`,`profesion`,`cargo`,`email`);
ALTER TABLE `personal` ADD FULLTEXT KEY `nombres_2` (`nombres`,`apellidos`);

--
-- Indices de la tabla `personal_horario`
--
ALTER TABLE `personal_horario`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `prematriculas`
--
ALTER TABLE `prematriculas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `promedios`
--
ALTER TABLE `promedios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `matricula_id` (`matricula_id`,`asignatura_id`,`ciclo`) USING BTREE;

--
-- Indices de la tabla `provincias`
--
ALTER TABLE `provincias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `schema_migrations`
--
ALTER TABLE `schema_migrations`
  ADD PRIMARY KEY (`version`);

--
-- Indices de la tabla `sedes`
--
ALTER TABLE `sedes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `system_pages`
--
ALTER TABLE `system_pages`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `system_page_permissions`
--
ALTER TABLE `system_page_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_system_page_permissions_on_system_page_id` (`system_page_id`);

--
-- Indices de la tabla `system_resources`
--
ALTER TABLE `system_resources`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `system_resource_permissions`
--
ALTER TABLE `system_resource_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_system_resource_permissions_on_system_resource_id` (`system_resource_id`);

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tareas_preguntas`
--
ALTER TABLE `tareas_preguntas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tareas_preguntas_alternativas`
--
ALTER TABLE `tareas_preguntas_alternativas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tareas_pruebas`
--
ALTER TABLE `tareas_pruebas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `topico_atenciones`
--
ALTER TABLE `topico_atenciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `trabajadores`
--
ALTER TABLE `trabajadores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `trabajadores_asistencia`
--
ALTER TABLE `trabajadores_asistencia`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `trabajadores_faltas`
--
ALTER TABLE `trabajadores_faltas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `trabajos`
--
ALTER TABLE `trabajos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios_devices`
--
ALTER TABLE `usuarios_devices`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios_tokens`
--
ALTER TABLE `usuarios_tokens`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `vehiculos_pasajeros`
--
ALTER TABLE `vehiculos_pasajeros`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `verificaciones_deudas`
--
ALTER TABLE `verificaciones_deudas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `visitantes`
--
ALTER TABLE `visitantes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividades`
--
ALTER TABLE `actividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `actividades_personal`
--
ALTER TABLE `actividades_personal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `agendas`
--
ALTER TABLE `agendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `alertas`
--
ALTER TABLE `alertas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `alquileres_cancha`
--
ALTER TABLE `alquileres_cancha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `alquileres_cancha_clientes`
--
ALTER TABLE `alquileres_cancha_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `alumnos_documentos`
--
ALTER TABLE `alumnos_documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `apoderados`
--
ALTER TABLE `apoderados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `areas`
--
ALTER TABLE `areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `areas_cursos`
--
ALTER TABLE `areas_cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas`
--
ALTER TABLE `asignaturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_actividades`
--
ALTER TABLE `asignaturas_actividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_archivos`
--
ALTER TABLE `asignaturas_archivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_asistencias`
--
ALTER TABLE `asignaturas_asistencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_criterios`
--
ALTER TABLE `asignaturas_criterios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_enlaces`
--
ALTER TABLE `asignaturas_enlaces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_examenes`
--
ALTER TABLE `asignaturas_examenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_examenes_preguntas`
--
ALTER TABLE `asignaturas_examenes_preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_examenes_preguntas_alternativas`
--
ALTER TABLE `asignaturas_examenes_preguntas_alternativas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_examenes_pruebas`
--
ALTER TABLE `asignaturas_examenes_pruebas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_indicadores`
--
ALTER TABLE `asignaturas_indicadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_notificaciones`
--
ALTER TABLE `asignaturas_notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_notificaciones_vistos`
--
ALTER TABLE `asignaturas_notificaciones_vistos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_publicaciones`
--
ALTER TABLE `asignaturas_publicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_publicaciones_comentarios`
--
ALTER TABLE `asignaturas_publicaciones_comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_sesiones`
--
ALTER TABLE `asignaturas_sesiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_tareas`
--
ALTER TABLE `asignaturas_tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_tareas_archivos`
--
ALTER TABLE `asignaturas_tareas_archivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_tareas_entregas`
--
ALTER TABLE `asignaturas_tareas_entregas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_temas`
--
ALTER TABLE `asignaturas_temas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignaturas_videos`
--
ALTER TABLE `asignaturas_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `avatar_shop_items`
--
ALTER TABLE `avatar_shop_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `avatar_shop_sales`
--
ALTER TABLE `avatar_shop_sales`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `banco_temas`
--
ALTER TABLE `banco_temas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bloques`
--
ALTER TABLE `bloques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bloques_cursos`
--
ALTER TABLE `bloques_cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `boletas`
--
ALTER TABLE `boletas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `boletas_categorias`
--
ALTER TABLE `boletas_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `boletas_conceptos`
--
ALTER TABLE `boletas_conceptos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `boletas_configuracion`
--
ALTER TABLE `boletas_configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `boletas_detalles`
--
ALTER TABLE `boletas_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `boletas_ingresos`
--
ALTER TABLE `boletas_ingresos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `boletas_ingresos_detalles`
--
ALTER TABLE `boletas_ingresos_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `boletas_subcategorias`
--
ALTER TABLE `boletas_subcategorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `caja_categorias`
--
ALTER TABLE `caja_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `caja_conceptos`
--
ALTER TABLE `caja_conceptos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `caja_registros`
--
ALTER TABLE `caja_registros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `caja_saldos`
--
ALTER TABLE `caja_saldos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `canchas`
--
ALTER TABLE `canchas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `carpetas`
--
ALTER TABLE `carpetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cash_accounts`
--
ALTER TABLE `cash_accounts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cash_account_flows`
--
ALTER TABLE `cash_account_flows`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cash_account_types`
--
ALTER TABLE `cash_account_types`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cash_currencies`
--
ALTER TABLE `cash_currencies`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chats`
--
ALTER TABLE `chats`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `colegios`
--
ALTER TABLE `colegios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compendios`
--
ALTER TABLE `compendios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compendios_paginas`
--
ALTER TABLE `compendios_paginas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compendios_paginas_bloques`
--
ALTER TABLE `compendios_paginas_bloques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compendios_paginas_bloques_preguntas`
--
ALTER TABLE `compendios_paginas_bloques_preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compendios_paginas_bloques_preguntas_alternativas`
--
ALTER TABLE `compendios_paginas_bloques_preguntas_alternativas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compendios_paginas_bloques_preguntas_respuestas`
--
ALTER TABLE `compendios_paginas_bloques_preguntas_respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `concursos`
--
ALTER TABLE `concursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `concursos_preguntas`
--
ALTER TABLE `concursos_preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `concursos_preguntas_alternativas`
--
ALTER TABLE `concursos_preguntas_alternativas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `concursos_pruebas`
--
ALTER TABLE `concursos_pruebas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `config`
--
ALTER TABLE `config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contactos_trabajos`
--
ALTER TABLE `contactos_trabajos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `costos`
--
ALTER TABLE `costos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cursos_criterios`
--
ALTER TABLE `cursos_criterios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `distritos`
--
ALTER TABLE `distritos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documentos_matriculas`
--
ALTER TABLE `documentos_matriculas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `encuestas`
--
ALTER TABLE `encuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `encuestas_compartidos`
--
ALTER TABLE `encuestas_compartidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `encuestas_preguntas`
--
ALTER TABLE `encuestas_preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `encuestas_preguntas_alternativas`
--
ALTER TABLE `encuestas_preguntas_alternativas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `encuestas_pruebas`
--
ALTER TABLE `encuestas_pruebas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `enrollment_incidents`
--
ALTER TABLE `enrollment_incidents`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `examenes`
--
ALTER TABLE `examenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `examenes_bloques`
--
ALTER TABLE `examenes_bloques`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `examenes_bloques_compartidos`
--
ALTER TABLE `examenes_bloques_compartidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `examenes_bloques_preguntas`
--
ALTER TABLE `examenes_bloques_preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `examenes_bloques_preguntas_alternativas`
--
ALTER TABLE `examenes_bloques_preguntas_alternativas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `examenes_bloques_pruebas`
--
ALTER TABLE `examenes_bloques_pruebas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `examenes_compartidos`
--
ALTER TABLE `examenes_compartidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `examenes_preguntas`
--
ALTER TABLE `examenes_preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `examenes_preguntas_alternativas`
--
ALTER TABLE `examenes_preguntas_alternativas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `examenes_pruebas`
--
ALTER TABLE `examenes_pruebas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `familias`
--
ALTER TABLE `familias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `grupos`
--
ALTER TABLE `grupos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `grupos_horarios`
--
ALTER TABLE `grupos_horarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `grupos_talleres`
--
ALTER TABLE `grupos_talleres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `grupos_talleres_matriculas`
--
ALTER TABLE `grupos_talleres_matriculas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `grupos_talleres_matriculasx`
--
ALTER TABLE `grupos_talleres_matriculasx`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `impresiones`
--
ALTER TABLE `impresiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `infracciones`
--
ALTER TABLE `infracciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `infracciones_categorias`
--
ALTER TABLE `infracciones_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `invoice_payments`
--
ALTER TABLE `invoice_payments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `matriculas`
--
ALTER TABLE `matriculas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `matriculas_asistencias`
--
ALTER TABLE `matriculas_asistencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `matriculas_sanciones`
--
ALTER TABLE `matriculas_sanciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensajes_archivos`
--
ALTER TABLE `mensajes_archivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `niveles`
--
ALTER TABLE `niveles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notas_detalles`
--
ALTER TABLE `notas_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notas_examen_mensual`
--
ALTER TABLE `notas_examen_mensual`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `objetivos`
--
ALTER TABLE `objetivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos_comedor`
--
ALTER TABLE `pagos_comedor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos_comedor_fechas`
--
ALTER TABLE `pagos_comedor_fechas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos_historial`
--
ALTER TABLE `pagos_historial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `paises`
--
ALTER TABLE `paises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personal`
--
ALTER TABLE `personal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `personal_horario`
--
ALTER TABLE `personal_horario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `prematriculas`
--
ALTER TABLE `prematriculas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `promedios`
--
ALTER TABLE `promedios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `provincias`
--
ALTER TABLE `provincias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sedes`
--
ALTER TABLE `sedes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `system_pages`
--
ALTER TABLE `system_pages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `system_page_permissions`
--
ALTER TABLE `system_page_permissions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `system_resources`
--
ALTER TABLE `system_resources`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `system_resource_permissions`
--
ALTER TABLE `system_resource_permissions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tareas_preguntas`
--
ALTER TABLE `tareas_preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tareas_preguntas_alternativas`
--
ALTER TABLE `tareas_preguntas_alternativas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tareas_pruebas`
--
ALTER TABLE `tareas_pruebas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `topico_atenciones`
--
ALTER TABLE `topico_atenciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trabajadores`
--
ALTER TABLE `trabajadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trabajadores_asistencia`
--
ALTER TABLE `trabajadores_asistencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trabajadores_faltas`
--
ALTER TABLE `trabajadores_faltas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trabajos`
--
ALTER TABLE `trabajos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `turnos`
--
ALTER TABLE `turnos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios_devices`
--
ALTER TABLE `usuarios_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios_tokens`
--
ALTER TABLE `usuarios_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vehiculos_pasajeros`
--
ALTER TABLE `vehiculos_pasajeros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `verificaciones_deudas`
--
ALTER TABLE `verificaciones_deudas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `visitantes`
--
ALTER TABLE `visitantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `avatar_shop_sales`
--
ALTER TABLE `avatar_shop_sales`
  ADD CONSTRAINT `fk_rails_0a6a5f2674` FOREIGN KEY (`item_id`) REFERENCES `avatar_shop_items` (`id`);

--
-- Filtros para la tabla `cash_accounts`
--
ALTER TABLE `cash_accounts`
  ADD CONSTRAINT `fk_rails_81fc58f854` FOREIGN KEY (`cash_account_type_id`) REFERENCES `cash_account_types` (`id`),
  ADD CONSTRAINT `fk_rails_cc8ec577de` FOREIGN KEY (`cash_currency_id`) REFERENCES `cash_currencies` (`id`);

--
-- Filtros para la tabla `cash_account_flows`
--
ALTER TABLE `cash_account_flows`
  ADD CONSTRAINT `fk_rails_5353751738` FOREIGN KEY (`cash_account_id`) REFERENCES `cash_accounts` (`id`);

--
-- Filtros para la tabla `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `fk_rails_2024dfabfd` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`id`);

--
-- Filtros para la tabla `system_page_permissions`
--
ALTER TABLE `system_page_permissions`
  ADD CONSTRAINT `fk_rails_3857503190` FOREIGN KEY (`system_page_id`) REFERENCES `system_pages` (`id`);

--
-- Filtros para la tabla `system_resource_permissions`
--
ALTER TABLE `system_resource_permissions`
  ADD CONSTRAINT `fk_rails_ab1839be6e` FOREIGN KEY (`system_resource_id`) REFERENCES `system_resources` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
