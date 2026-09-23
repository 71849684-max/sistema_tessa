-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: tessa_control_pagos
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `auditoria`
--

DROP TABLE IF EXISTS `auditoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditoria` (
  `id_auditoria` bigint unsigned NOT NULL AUTO_INCREMENT,
  `accion` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_usuario` int unsigned DEFAULT NULL,
  `entidad` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_registro` int unsigned NOT NULL,
  `datos_antes` json DEFAULT NULL,
  `datos_despues` json DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_auditoria`),
  KEY `ix_audit` (`created_at`),
  KEY `ix_entidad` (`entidad`,`id_registro`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria`
--

LOCK TABLES `auditoria` WRITE;
/*!40000 ALTER TABLE `auditoria` DISABLE KEYS */;
INSERT INTO `auditoria` VALUES (17,'LOGIN',1,'usuario',1,'[]','[]','127.0.0.1','2026-09-22 17:25:12'),(18,'GUARDAR',1,'cliente',2,'[]','{\"tipo\": \"NATURAL\", \"accion\": \"guardar\", \"correo\": \"admin@gmail.com\", \"estado\": \"1\", \"nombres\": \"JOSE FRANCISCO\", \"telefono\": \"99999999\", \"apellidos\": \"GOMEZ SAMANIEGO\", \"direccion\": \"\", \"documento\": \"71849684\", \"id_cliente\": \"0\", \"razon_social\": \"\"}','127.0.0.1','2026-09-22 17:29:45'),(19,'GUARDAR',1,'servicio',2,'[]','{\"accion\": \"guardar\", \"estado\": \"1\", \"nombre\": \"Asesoría de Tesis\", \"descripcion\": \"Asesoría para tu tesis\", \"id_servicio\": \"0\", \"precio_referencia\": \"5000\"}','127.0.0.1','2026-09-22 17:30:18');
/*!40000 ALTER TABLE `auditoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cargo`
--

DROP TABLE IF EXISTS `cargo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cargo` (
  `id_cargo` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cargo`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cargo`
--

LOCK TABLES `cargo` WRITE;
/*!40000 ALTER TABLE `cargo` DISABLE KEYS */;
INSERT INTO `cargo` VALUES (1,'Administrador',1,'2026-09-22 11:26:13','2026-09-22 11:26:13');
/*!40000 ALTER TABLE `cargo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cargo_permiso`
--

DROP TABLE IF EXISTS `cargo_permiso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cargo_permiso` (
  `id_cargo` int unsigned NOT NULL,
  `id_permiso` int unsigned NOT NULL,
  `permitido` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_cargo`,`id_permiso`),
  KEY `id_permiso` (`id_permiso`),
  CONSTRAINT `cargo_permiso_ibfk_1` FOREIGN KEY (`id_cargo`) REFERENCES `cargo` (`id_cargo`),
  CONSTRAINT `cargo_permiso_ibfk_2` FOREIGN KEY (`id_permiso`) REFERENCES `permiso` (`id_permiso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cargo_permiso`
--

LOCK TABLES `cargo_permiso` WRITE;
/*!40000 ALTER TABLE `cargo_permiso` DISABLE KEYS */;
INSERT INTO `cargo_permiso` VALUES (1,1,1),(1,2,1),(1,3,1),(1,4,1),(1,5,1),(1,6,1),(1,7,1),(1,8,1),(1,9,1),(1,10,1),(1,11,1),(1,12,1),(1,13,1),(1,14,1),(1,15,1),(1,16,1),(1,17,1),(1,18,1),(1,19,1),(1,20,1),(1,21,1),(1,22,1),(1,23,1),(1,24,1),(1,25,1),(1,26,1),(1,27,1),(1,28,1),(1,29,1),(1,30,1),(1,31,1),(1,32,1),(1,33,1),(1,34,1),(1,35,1),(1,36,1),(1,37,1),(1,38,1),(1,39,1),(1,40,1),(1,41,1),(1,42,1),(1,43,1),(1,44,1),(1,45,1),(1,64,1);
/*!40000 ALTER TABLE `cargo_permiso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cliente`
--

DROP TABLE IF EXISTS `cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cliente` (
  `id_cliente` int unsigned NOT NULL AUTO_INCREMENT,
  `tipo` enum('NATURAL','JURIDICA') COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apellidos` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razon_social` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(220) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `documento` (`documento`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cliente`
--

LOCK TABLES `cliente` WRITE;
/*!40000 ALTER TABLE `cliente` DISABLE KEYS */;
INSERT INTO `cliente` VALUES (2,'NATURAL','71849684','JOSE FRANCISCO','GOMEZ SAMANIEGO',NULL,'admin@gmail.com','99999999',NULL,1,'2026-09-22 17:29:45','2026-09-22 17:29:45');
/*!40000 ALTER TABLE `cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cobranza`
--

DROP TABLE IF EXISTS `cobranza`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cobranza` (
  `id_cobranza` int unsigned NOT NULL AUTO_INCREMENT,
  `id_contrato` int unsigned NOT NULL,
  `id_registrado_por` int unsigned NOT NULL,
  `fecha` datetime NOT NULL,
  `medio_pago` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_operacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto_total` decimal(12,2) NOT NULL,
  `estado` enum('REGISTRADO','ANULADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'REGISTRADO',
  `motivo_anulacion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cobranza`),
  KEY `id_contrato` (`id_contrato`),
  KEY `id_registrado_por` (`id_registrado_por`),
  KEY `ix_cobranza` (`fecha`,`estado`),
  CONSTRAINT `cobranza_ibfk_1` FOREIGN KEY (`id_contrato`) REFERENCES `contrato` (`id_contrato`),
  CONSTRAINT `cobranza_ibfk_2` FOREIGN KEY (`id_registrado_por`) REFERENCES `personal` (`id_personal`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cobranza`
--

LOCK TABLES `cobranza` WRITE;
/*!40000 ALTER TABLE `cobranza` DISABLE KEYS */;
/*!40000 ALTER TABLE `cobranza` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cobranza_detalle`
--

DROP TABLE IF EXISTS `cobranza_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cobranza_detalle` (
  `id_cobranza` int unsigned NOT NULL,
  `id_hito` int unsigned NOT NULL,
  `monto_aplicado` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id_cobranza`,`id_hito`),
  KEY `id_hito` (`id_hito`),
  CONSTRAINT `cobranza_detalle_ibfk_1` FOREIGN KEY (`id_cobranza`) REFERENCES `cobranza` (`id_cobranza`),
  CONSTRAINT `cobranza_detalle_ibfk_2` FOREIGN KEY (`id_hito`) REFERENCES `contrato_hito` (`id_hito`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cobranza_detalle`
--

LOCK TABLES `cobranza_detalle` WRITE;
/*!40000 ALTER TABLE `cobranza_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `cobranza_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contrato`
--

DROP TABLE IF EXISTS `contrato`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contrato` (
  `id_contrato` int unsigned NOT NULL AUTO_INCREMENT,
  `con_numero` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_cliente` int unsigned NOT NULL,
  `id_servicio` int unsigned NOT NULL,
  `id_responsable` int unsigned NOT NULL,
  `tipo` enum('INDIVIDUAL','GRUPAL') COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` date NOT NULL,
  `monto_bruto` decimal(12,2) NOT NULL,
  `descuento` decimal(12,2) NOT NULL DEFAULT '0.00',
  `monto_neto` decimal(12,2) NOT NULL,
  `estado` enum('BORRADOR','ACTIVO','FINALIZADO','ANULADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `motivo_anulacion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_contrato`),
  UNIQUE KEY `con_numero` (`con_numero`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_servicio` (`id_servicio`),
  KEY `id_responsable` (`id_responsable`),
  CONSTRAINT `contrato_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`),
  CONSTRAINT `contrato_ibfk_2` FOREIGN KEY (`id_servicio`) REFERENCES `servicio` (`id_servicio`),
  CONSTRAINT `contrato_ibfk_3` FOREIGN KEY (`id_responsable`) REFERENCES `personal` (`id_personal`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contrato`
--

LOCK TABLES `contrato` WRITE;
/*!40000 ALTER TABLE `contrato` DISABLE KEYS */;
/*!40000 ALTER TABLE `contrato` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contrato_hito`
--

DROP TABLE IF EXISTS `contrato_hito`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contrato_hito` (
  `id_hito` int unsigned NOT NULL AUTO_INCREMENT,
  `id_contrato` int unsigned NOT NULL,
  `numero` smallint unsigned NOT NULL,
  `descripcion` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `monto_pagado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `estado` enum('PENDIENTE','PARCIAL','PAGADO','VENCIDO','ANULADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  PRIMARY KEY (`id_hito`),
  UNIQUE KEY `uq_hito` (`id_contrato`,`numero`),
  CONSTRAINT `contrato_hito_ibfk_1` FOREIGN KEY (`id_contrato`) REFERENCES `contrato` (`id_contrato`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contrato_hito`
--

LOCK TABLES `contrato_hito` WRITE;
/*!40000 ALTER TABLE `contrato_hito` DISABLE KEYS */;
/*!40000 ALTER TABLE `contrato_hito` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contrato_integrante`
--

DROP TABLE IF EXISTS `contrato_integrante`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contrato_integrante` (
  `id_contrato` int unsigned NOT NULL,
  `id_cliente` int unsigned NOT NULL,
  PRIMARY KEY (`id_contrato`,`id_cliente`),
  KEY `id_cliente` (`id_cliente`),
  CONSTRAINT `contrato_integrante_ibfk_1` FOREIGN KEY (`id_contrato`) REFERENCES `contrato` (`id_contrato`),
  CONSTRAINT `contrato_integrante_ibfk_2` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contrato_integrante`
--

LOCK TABLES `contrato_integrante` WRITE;
/*!40000 ALTER TABLE `contrato_integrante` DISABLE KEYS */;
/*!40000 ALTER TABLE `contrato_integrante` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `egreso`
--

DROP TABLE IF EXISTS `egreso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `egreso` (
  `id_egreso` int unsigned NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `categoria` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `beneficiario` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `concepto` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `id_registrado_por` int unsigned NOT NULL,
  `estado` enum('REGISTRADO','ANULADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'REGISTRADO',
  `motivo_anulacion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_egreso`),
  KEY `id_registrado_por` (`id_registrado_por`),
  KEY `ix_egreso` (`fecha`,`estado`),
  CONSTRAINT `egreso_ibfk_1` FOREIGN KEY (`id_registrado_por`) REFERENCES `personal` (`id_personal`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `egreso`
--

LOCK TABLES `egreso` WRITE;
/*!40000 ALTER TABLE `egreso` DISABLE KEYS */;
/*!40000 ALTER TABLE `egreso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_intento`
--

DROP TABLE IF EXISTS `login_intento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_intento` (
  `id_login` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exitoso` tinyint NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_login`),
  KEY `ix_login` (`usuario`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_intento`
--

LOCK TABLES `login_intento` WRITE;
/*!40000 ALTER TABLE `login_intento` DISABLE KEYS */;
INSERT INTO `login_intento` VALUES (7,'admin',1,NULL,'2026-09-22 17:25:12');
/*!40000 ALTER TABLE `login_intento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modulo`
--

DROP TABLE IF EXISTS `modulo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modulo` (
  `id_modulo` int unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_modulo`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modulo`
--

LOCK TABLES `modulo` WRITE;
/*!40000 ALTER TABLE `modulo` DISABLE KEYS */;
INSERT INTO `modulo` VALUES (1,'clientes','Clientes'),(2,'servicios','Servicios'),(3,'contratos','Contratos'),(4,'cobranzas','Cobranzas'),(5,'egresos','Egresos'),(6,'personal','Personal'),(7,'cargos','Cargos'),(8,'usuarios','Usuarios'),(9,'auditoria','Auditor├¡a'),(10,'dashboard','Dashboard de ventas');
/*!40000 ALTER TABLE `modulo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permiso`
--

DROP TABLE IF EXISTS `permiso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permiso` (
  `id_permiso` int unsigned NOT NULL AUTO_INCREMENT,
  `id_modulo` int unsigned NOT NULL,
  `accion` enum('ver','crear','editar','anular','administrar') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_permiso`),
  UNIQUE KEY `uq_permiso` (`id_modulo`,`accion`),
  CONSTRAINT `permiso_ibfk_1` FOREIGN KEY (`id_modulo`) REFERENCES `modulo` (`id_modulo`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permiso`
--

LOCK TABLES `permiso` WRITE;
/*!40000 ALTER TABLE `permiso` DISABLE KEYS */;
INSERT INTO `permiso` VALUES (7,1,'ver'),(16,1,'crear'),(25,1,'editar'),(34,1,'anular'),(43,1,'administrar'),(2,2,'ver'),(11,2,'crear'),(20,2,'editar'),(29,2,'anular'),(38,2,'administrar'),(5,3,'ver'),(14,3,'crear'),(23,3,'editar'),(32,3,'anular'),(41,3,'administrar'),(6,4,'ver'),(15,4,'crear'),(24,4,'editar'),(33,4,'anular'),(42,4,'administrar'),(4,5,'ver'),(13,5,'crear'),(22,5,'editar'),(31,5,'anular'),(40,5,'administrar'),(3,6,'ver'),(12,6,'crear'),(21,6,'editar'),(30,6,'anular'),(39,6,'administrar'),(8,7,'ver'),(17,7,'crear'),(26,7,'editar'),(35,7,'anular'),(44,7,'administrar'),(1,8,'ver'),(10,8,'crear'),(19,8,'editar'),(28,8,'anular'),(37,8,'administrar'),(9,9,'ver'),(18,9,'crear'),(27,9,'editar'),(36,9,'anular'),(45,9,'administrar'),(64,10,'ver');
/*!40000 ALTER TABLE `permiso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `persona_permiso`
--

DROP TABLE IF EXISTS `persona_permiso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `persona_permiso` (
  `id_personal` int unsigned NOT NULL,
  `id_permiso` int unsigned NOT NULL,
  `permitido` tinyint NOT NULL,
  PRIMARY KEY (`id_personal`,`id_permiso`),
  KEY `id_permiso` (`id_permiso`),
  CONSTRAINT `persona_permiso_ibfk_1` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`),
  CONSTRAINT `persona_permiso_ibfk_2` FOREIGN KEY (`id_permiso`) REFERENCES `permiso` (`id_permiso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `persona_permiso`
--

LOCK TABLES `persona_permiso` WRITE;
/*!40000 ALTER TABLE `persona_permiso` DISABLE KEYS */;
/*!40000 ALTER TABLE `persona_permiso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal`
--

DROP TABLE IF EXISTS `personal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal` (
  `id_personal` int unsigned NOT NULL AUTO_INCREMENT,
  `dni` char(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_cargo` int unsigned NOT NULL,
  `estado` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_personal`),
  UNIQUE KEY `dni` (`dni`),
  KEY `id_cargo` (`id_cargo`),
  CONSTRAINT `personal_ibfk_1` FOREIGN KEY (`id_cargo`) REFERENCES `cargo` (`id_cargo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal`
--

LOCK TABLES `personal` WRITE;
/*!40000 ALTER TABLE `personal` DISABLE KEYS */;
INSERT INTO `personal` VALUES (1,'00000001','Administrador','Tessa','admin@gmail.com','99999999',1,1,'2026-09-22 11:26:13','2026-09-22 14:49:59');
/*!40000 ALTER TABLE `personal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servicio`
--

DROP TABLE IF EXISTS `servicio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `servicio` (
  `id_servicio` int unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio_referencia` decimal(12,2) NOT NULL DEFAULT '0.00',
  `estado` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_servicio`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servicio`
--

LOCK TABLES `servicio` WRITE;
/*!40000 ALTER TABLE `servicio` DISABLE KEYS */;
INSERT INTO `servicio` VALUES (2,'Asesoría de Tesis','Asesoría para tu tesis',5000.00,1,'2026-09-22 17:30:18','2026-09-22 17:30:18');
/*!40000 ALTER TABLE `servicio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `id_usuario` int unsigned NOT NULL AUTO_INCREMENT,
  `id_personal` int unsigned NOT NULL,
  `usu_nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usu_estado` tinyint NOT NULL DEFAULT '1',
  `failed_attempts` tinyint unsigned NOT NULL DEFAULT '0',
  `failed_window_started` datetime DEFAULT NULL,
  `locked_until` datetime DEFAULT NULL,
  `ultimo_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `id_personal` (`id_personal`),
  UNIQUE KEY `usu_nombre` (`usu_nombre`),
  CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,1,'admin','$2y$10$po4vGMzkQR.D3NgyIn9TauPpj7b04bpakxwwCuy57Cf84qZNgb2dS',1,0,NULL,NULL,'2026-09-22 17:25:12','2026-09-22 11:26:13');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voucher`
--

DROP TABLE IF EXISTS `voucher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voucher` (
  `id_voucher` int unsigned NOT NULL AUTO_INCREMENT,
  `id_cobranza` int unsigned NOT NULL,
  `nombre_interno` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tamano` int unsigned NOT NULL,
  `hash_sha256` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('VALIDADO','RETIRADO') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VALIDADO',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_voucher`),
  UNIQUE KEY `nombre_interno` (`nombre_interno`),
  KEY `id_cobranza` (`id_cobranza`),
  CONSTRAINT `voucher_ibfk_1` FOREIGN KEY (`id_cobranza`) REFERENCES `cobranza` (`id_cobranza`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voucher`
--

LOCK TABLES `voucher` WRITE;
/*!40000 ALTER TABLE `voucher` DISABLE KEYS */;
/*!40000 ALTER TABLE `voucher` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'tessa_control_pagos'
--
/*!50003 DROP PROCEDURE IF EXISTS `sp_admin_inicializar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_admin_inicializar`(IN p_usuario VARCHAR(80),IN p_hash VARCHAR(255),IN p_nombres VARCHAR(120),IN p_apellidos VARCHAR(120),IN p_dni CHAR(8))
BEGIN DECLARE v_cargo INT UNSIGNED; DECLARE v_personal INT UNSIGNED; IF EXISTS(SELECT 1 FROM usuario u JOIN personal p ON p.id_personal=u.id_personal JOIN cargo c ON c.id_cargo=p.id_cargo WHERE c.nombre='Administrador') THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Ya existe un administrador.'; END IF; START TRANSACTION; INSERT INTO cargo(nombre) VALUES('Administrador') ON DUPLICATE KEY UPDATE estado=1; SELECT id_cargo INTO v_cargo FROM cargo WHERE nombre='Administrador'; INSERT INTO cargo_permiso(id_cargo,id_permiso,permitido) SELECT v_cargo,id_permiso,1 FROM permiso ON DUPLICATE KEY UPDATE permitido=1; INSERT INTO personal(dni,nombres,apellidos,id_cargo) VALUES(p_dni,p_nombres,p_apellidos,v_cargo); SET v_personal=LAST_INSERT_ID(); INSERT INTO usuario(id_personal,usu_nombre,password_hash) VALUES(v_personal,p_usuario,p_hash); COMMIT; SELECT 1 resultado,'Administrador inicializado.' mensaje,v_personal id_personal; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_auditoria_listar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_auditoria_listar`(IN p_inicio VARCHAR(10),IN p_fin VARCHAR(10),IN p_buscar VARCHAR(100))
BEGIN SELECT a.*,u.usu_nombre FROM auditoria a LEFT JOIN usuario u ON u.id_usuario=a.id_usuario WHERE(p_inicio='' OR DATE(a.created_at)>=p_inicio) AND(p_fin='' OR DATE(a.created_at)<=p_fin) AND(p_buscar='' OR a.entidad LIKE CONCAT('%',p_buscar,'%') OR a.accion LIKE CONCAT('%',p_buscar,'%')) ORDER BY a.id_auditoria DESC LIMIT 500; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_auditoria_registrar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_auditoria_registrar`(IN p_accion VARCHAR(60),IN p_usuario INT UNSIGNED,IN p_id INT UNSIGNED,IN p_entidad VARCHAR(80),IN p_antes JSON,IN p_despues JSON,IN p_ip VARCHAR(45))
BEGIN INSERT INTO auditoria(accion,id_usuario,entidad,id_registro,datos_antes,datos_despues,ip) VALUES(p_accion,p_usuario,p_entidad,p_id,p_antes,p_despues,p_ip); END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_cargo_guardar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cargo_guardar`(IN p_id INT UNSIGNED,IN p_nombre VARCHAR(100),IN p_estado TINYINT)
BEGIN IF p_id=0 THEN INSERT INTO cargo(nombre,estado) VALUES(p_nombre,p_estado); SET p_id=LAST_INSERT_ID(); ELSE UPDATE cargo SET nombre=p_nombre,estado=p_estado WHERE id_cargo=p_id; END IF; SELECT 1 resultado,'Cargo guardado.' mensaje,p_id id_cargo; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_cargo_listar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cargo_listar`()
BEGIN SELECT * FROM cargo ORDER BY nombre; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_cargo_permiso_configuracion_listar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cargo_permiso_configuracion_listar`(IN p_cargo INT UNSIGNED)
BEGIN SELECT pr.id_permiso,m.codigo modulo,pr.accion,cp.permitido plantilla FROM permiso pr JOIN modulo m ON m.id_modulo=pr.id_modulo LEFT JOIN cargo_permiso cp ON cp.id_permiso=pr.id_permiso AND cp.id_cargo=p_cargo ORDER BY m.nombre,pr.accion; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_cargo_permiso_guardar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cargo_permiso_guardar`(IN p_cargo INT UNSIGNED,IN p_permiso INT UNSIGNED,IN p_permitido TINYINT)
BEGIN INSERT INTO cargo_permiso VALUES(p_cargo,p_permiso,p_permitido) ON DUPLICATE KEY UPDATE permitido=p_permitido; SELECT 1 resultado,'Permiso de cargo guardado.' mensaje; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_cliente_buscar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cliente_buscar`(IN p_consulta VARCHAR(100))
BEGIN SELECT id_cliente,tipo,documento,nombres,apellidos,razon_social,estado FROM cliente WHERE estado=1 AND (documento LIKE CONCAT('%',p_consulta,'%') OR nombres LIKE CONCAT('%',p_consulta,'%') OR apellidos LIKE CONCAT('%',p_consulta,'%') OR CONCAT_WS(' ',nombres,apellidos) LIKE CONCAT('%',p_consulta,'%') OR razon_social LIKE CONCAT('%',p_consulta,'%')) ORDER BY CASE WHEN documento=p_consulta THEN 0 ELSE 1 END,documento LIMIT 20; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_cliente_guardar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cliente_guardar`(IN p_id INT UNSIGNED,IN p_tipo VARCHAR(10),IN p_doc VARCHAR(11),IN p_nombres VARCHAR(150),IN p_apellidos VARCHAR(150),IN p_razon VARCHAR(180),IN p_correo VARCHAR(160),IN p_telefono VARCHAR(30),IN p_direccion VARCHAR(220),IN p_estado TINYINT)
BEGIN IF p_id=0 THEN INSERT INTO cliente(tipo,documento,nombres,apellidos,razon_social,correo,telefono,direccion,estado) VALUES(p_tipo,p_doc,NULLIF(p_nombres,''),NULLIF(p_apellidos,''),NULLIF(p_razon,''),NULLIF(p_correo,''),NULLIF(p_telefono,''),NULLIF(p_direccion,''),p_estado); SET p_id=LAST_INSERT_ID(); ELSE UPDATE cliente SET tipo=p_tipo,documento=p_doc,nombres=NULLIF(p_nombres,''),apellidos=NULLIF(p_apellidos,''),razon_social=NULLIF(p_razon,''),correo=NULLIF(p_correo,''),telefono=NULLIF(p_telefono,''),direccion=NULLIF(p_direccion,''),estado=p_estado WHERE id_cliente=p_id; END IF; SELECT 1 resultado,'Cliente guardado.' mensaje,p_id id_cliente; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_cliente_listar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cliente_listar`()
BEGIN SELECT * FROM cliente ORDER BY id_cliente DESC; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_cobranza_anular` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cobranza_anular`(IN p_cobranza INT UNSIGNED,IN p_motivo VARCHAR(500))
BEGIN DECLARE v_contrato INT UNSIGNED; DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END; IF CHAR_LENGTH(TRIM(p_motivo))=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El motivo de anulación es obligatorio.'; END IF; START TRANSACTION; SELECT id_contrato INTO v_contrato FROM cobranza WHERE id_cobranza=p_cobranza AND estado='REGISTRADO' FOR UPDATE; IF v_contrato IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cobranza no disponible para anulación.'; END IF; UPDATE cobranza SET estado='ANULADO',motivo_anulacion=p_motivo WHERE id_cobranza=p_cobranza AND estado='REGISTRADO'; IF ROW_COUNT()<>1 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cobranza no disponible para anulación.'; END IF; UPDATE contrato_hito h JOIN(SELECT id_hito,SUM(monto_aplicado) monto FROM cobranza_detalle WHERE id_cobranza=p_cobranza GROUP BY id_hito)d ON d.id_hito=h.id_hito SET h.monto_pagado=GREATEST(0,h.monto_pagado-d.monto),h.estado=IF(GREATEST(0,h.monto_pagado-d.monto)=0,'PENDIENTE','PARCIAL'); UPDATE contrato SET estado='ACTIVO' WHERE id_contrato=v_contrato AND estado='FINALIZADO'; COMMIT; SELECT 1 resultado,'Cobranza anulada y cuotas revertidas.' mensaje; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_cobranza_listar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cobranza_listar`(IN p_inicio VARCHAR(10),IN p_fin VARCHAR(10),IN p_estado VARCHAR(15))
BEGIN SELECT c.*,co.con_numero FROM cobranza c JOIN contrato co ON co.id_contrato=c.id_contrato WHERE(p_inicio='' OR DATE(c.fecha)>=p_inicio) AND(p_fin='' OR DATE(c.fecha)<=p_fin) AND(p_estado='' OR c.estado=p_estado) ORDER BY c.id_cobranza DESC; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_cobranza_registrar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cobranza_registrar`(IN p_contrato INT UNSIGNED,IN p_personal INT UNSIGNED,IN p_fecha DATETIME,IN p_medio VARCHAR(60),IN p_operacion VARCHAR(100),IN p_detalles JSON,IN p_voucher_nombre VARCHAR(80),IN p_voucher_mime VARCHAR(80),IN p_voucher_tamano INT,IN p_voucher_hash CHAR(64))
BEGIN DECLARE v_i INT DEFAULT 0; DECLARE v_total DECIMAL(12,2) DEFAULT 0; DECLARE v_hito INT UNSIGNED; DECLARE v_monto DECIMAL(12,2); DECLARE v_saldo DECIMAL(12,2); DECLARE v_cobranza INT UNSIGNED; DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END; IF JSON_LENGTH(p_detalles)=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Debe aplicar el pago a una cuota.'; END IF; START TRANSACTION; WHILE v_i<JSON_LENGTH(p_detalles) DO SET v_hito=CAST(JSON_UNQUOTE(JSON_EXTRACT(p_detalles,CONCAT('$[',v_i,'].id_hito'))) AS UNSIGNED); SET v_monto=CAST(JSON_UNQUOTE(JSON_EXTRACT(p_detalles,CONCAT('$[',v_i,'].monto'))) AS DECIMAL(12,2)); SELECT monto-monto_pagado INTO v_saldo FROM contrato_hito WHERE id_hito=v_hito AND id_contrato=p_contrato AND estado IN('PENDIENTE','PARCIAL') FOR UPDATE; IF v_monto<=0 OR v_saldo IS NULL OR v_monto>v_saldo THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El pago supera el saldo de una cuota.'; END IF; SET v_total=v_total+v_monto; SET v_i=v_i+1; END WHILE; INSERT INTO cobranza(id_contrato,id_registrado_por,fecha,medio_pago,numero_operacion,monto_total) VALUES(p_contrato,p_personal,p_fecha,p_medio,NULLIF(p_operacion,''),v_total); SET v_cobranza=LAST_INSERT_ID(); SET v_i=0; WHILE v_i<JSON_LENGTH(p_detalles) DO SET v_hito=CAST(JSON_UNQUOTE(JSON_EXTRACT(p_detalles,CONCAT('$[',v_i,'].id_hito'))) AS UNSIGNED); SET v_monto=CAST(JSON_UNQUOTE(JSON_EXTRACT(p_detalles,CONCAT('$[',v_i,'].monto'))) AS DECIMAL(12,2)); INSERT INTO cobranza_detalle VALUES(v_cobranza,v_hito,v_monto); UPDATE contrato_hito SET monto_pagado=monto_pagado+v_monto,estado=IF(monto_pagado+v_monto>=monto,'PAGADO','PARCIAL') WHERE id_hito=v_hito; SET v_i=v_i+1; END WHILE; IF p_voucher_nombre<>'' THEN INSERT INTO voucher(id_cobranza,nombre_interno,mime,tamano,hash_sha256) VALUES(v_cobranza,p_voucher_nombre,p_voucher_mime,p_voucher_tamano,p_voucher_hash); END IF; IF NOT EXISTS(SELECT 1 FROM contrato_hito WHERE id_contrato=p_contrato AND estado IN('PENDIENTE','PARCIAL','VENCIDO')) THEN UPDATE contrato SET estado='FINALIZADO' WHERE id_contrato=p_contrato; END IF; COMMIT; SELECT 1 resultado,'Cobranza registrada.' mensaje,v_cobranza id_cobranza; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_contrato_anular` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_contrato_anular`(IN p_contrato INT UNSIGNED,IN p_motivo VARCHAR(500))
BEGIN IF CHAR_LENGTH(TRIM(p_motivo))=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El motivo de anulación es obligatorio.'; END IF; IF EXISTS(SELECT 1 FROM cobranza WHERE id_contrato=p_contrato AND estado='REGISTRADO') THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='No puede anular un contrato con cobranzas activas.'; END IF; UPDATE contrato SET estado='ANULADO',motivo_anulacion=p_motivo WHERE id_contrato=p_contrato AND estado<>'ANULADO'; IF ROW_COUNT()=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Contrato no disponible para anulación.'; END IF; UPDATE contrato_hito SET estado='ANULADO' WHERE id_contrato=p_contrato; SELECT 1 resultado,'Contrato anulado.' mensaje; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_contrato_crear` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_contrato_crear`(IN p_cliente INT UNSIGNED,IN p_servicio INT UNSIGNED,IN p_responsable INT UNSIGNED,IN p_tipo VARCHAR(12),IN p_fecha DATE,IN p_bruto DECIMAL(12,2),IN p_descuento DECIMAL(12,2),IN p_usuario INT UNSIGNED)
BEGIN DECLARE v_numero VARCHAR(30); DECLARE v_siguiente INT; SELECT COUNT(*)+1 INTO v_siguiente FROM contrato WHERE YEAR(fecha)=YEAR(p_fecha); SET v_numero=CONCAT('TES-',YEAR(p_fecha),'-',LPAD(v_siguiente,6,'0')); INSERT INTO contrato(con_numero,id_cliente,id_servicio,id_responsable,tipo,fecha,monto_bruto,descuento,monto_neto,created_by) VALUES(v_numero,p_cliente,p_servicio,p_responsable,p_tipo,p_fecha,p_bruto,p_descuento,p_bruto-p_descuento,p_usuario); SELECT 1 resultado,'Contrato registrado.' mensaje,LAST_INSERT_ID() id_contrato,v_numero con_numero; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_contrato_detalle` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_contrato_detalle`(IN p_contrato INT UNSIGNED)
BEGIN SELECT * FROM contrato WHERE id_contrato=p_contrato; SELECT * FROM contrato_hito WHERE id_contrato=p_contrato ORDER BY numero; SELECT cl.* FROM contrato_integrante ci JOIN cliente cl ON cl.id_cliente=ci.id_cliente WHERE ci.id_contrato=p_contrato; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_contrato_hitos_validar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_contrato_hitos_validar`(IN p_contrato INT UNSIGNED)
BEGIN DECLARE v_neto DECIMAL(12,2); DECLARE v_total DECIMAL(12,2); SELECT monto_neto INTO v_neto FROM contrato WHERE id_contrato=p_contrato FOR UPDATE; SELECT COALESCE(SUM(monto),0) INTO v_total FROM contrato_hito WHERE id_contrato=p_contrato; IF v_neto IS NULL OR ABS(v_neto-v_total)>0.001 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Las cuotas no coinciden con el monto neto.'; END IF; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_contrato_hito_guardar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_contrato_hito_guardar`(IN p_contrato INT UNSIGNED,IN p_numero SMALLINT,IN p_descripcion VARCHAR(180),IN p_fecha DATE,IN p_monto DECIMAL(12,2))
BEGIN IF p_monto<=0 OR p_fecha IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cuota inválida.'; END IF; INSERT INTO contrato_hito(id_contrato,numero,descripcion,fecha_vencimiento,monto) VALUES(p_contrato,p_numero,p_descripcion,p_fecha,p_monto); END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_contrato_integrante_guardar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_contrato_integrante_guardar`(IN p_contrato INT UNSIGNED,IN p_cliente INT UNSIGNED)
BEGIN
    IF NOT EXISTS(SELECT 1 FROM cliente WHERE id_cliente=p_cliente AND estado=1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El integrante no es un cliente activo.';
    END IF;
    INSERT INTO contrato_integrante VALUES(p_contrato,p_cliente);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_contrato_listar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_contrato_listar`()
BEGIN SELECT c.*,COALESCE(SUM(h.monto-h.monto_pagado),0) saldo,COALESCE(cl.razon_social,CONCAT_WS(' ',cl.nombres,cl.apellidos)) cliente_nombre,s.nombre servicio_nombre FROM contrato c JOIN cliente cl ON cl.id_cliente=c.id_cliente JOIN servicio s ON s.id_servicio=c.id_servicio LEFT JOIN contrato_hito h ON h.id_contrato=c.id_contrato AND h.estado<>'ANULADO' GROUP BY c.id_contrato ORDER BY c.id_contrato DESC; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_dashboard_ventas_embudo` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_dashboard_ventas_embudo`(IN p_desde DATE,IN p_hasta DATE)
BEGIN SELECT h.estado,COUNT(*) cantidad,COALESCE(SUM(h.monto-h.monto_pagado),0) monto FROM contrato_hito h JOIN contrato c ON c.id_contrato=h.id_contrato WHERE c.estado<>'ANULADO' AND h.fecha_vencimiento BETWEEN p_desde AND p_hasta GROUP BY h.estado ORDER BY FIELD(h.estado,'PENDIENTE','PARCIAL','VENCIDO','PAGADO'); END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_dashboard_ventas_evolucion` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_dashboard_ventas_evolucion`(IN p_desde DATE,IN p_hasta DATE)
BEGIN SELECT DATE(fecha) fecha,COALESCE(SUM(monto_total),0) cobrado FROM cobranza WHERE estado='REGISTRADO' AND DATE(fecha) BETWEEN p_desde AND p_hasta GROUP BY DATE(fecha) ORDER BY fecha; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_dashboard_ventas_pendientes` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_dashboard_ventas_pendientes`(IN p_desde DATE,IN p_hasta DATE)
BEGIN SELECT h.id_hito,c.con_numero,COALESCE(cl.razon_social,CONCAT_WS(' ',cl.nombres,cl.apellidos)) cliente,h.descripcion,h.fecha_vencimiento,h.monto-h.monto_pagado saldo,h.estado FROM contrato_hito h JOIN contrato c ON c.id_contrato=h.id_contrato JOIN cliente cl ON cl.id_cliente=c.id_cliente WHERE c.estado='ACTIVO' AND h.estado IN('PENDIENTE','PARCIAL','VENCIDO') AND h.fecha_vencimiento BETWEEN p_desde AND p_hasta ORDER BY h.fecha_vencimiento,h.id_hito LIMIT 30; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_dashboard_ventas_resumen` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_dashboard_ventas_resumen`(IN p_desde DATE,IN p_hasta DATE)
BEGIN DECLARE v_cobrado DECIMAL(12,2) DEFAULT 0; DECLARE v_pendiente DECIMAL(12,2) DEFAULT 0; DECLARE v_activos INT DEFAULT 0; SELECT COALESCE(SUM(monto_total),0) INTO v_cobrado FROM cobranza WHERE estado='REGISTRADO' AND DATE(fecha) BETWEEN p_desde AND p_hasta; SELECT COALESCE(SUM(h.monto-h.monto_pagado),0) INTO v_pendiente FROM contrato_hito h JOIN contrato c ON c.id_contrato=h.id_contrato WHERE c.estado IN('ACTIVO','FINALIZADO') AND h.estado IN('PENDIENTE','PARCIAL','VENCIDO'); SELECT COUNT(*) INTO v_activos FROM contrato WHERE estado='ACTIVO'; SELECT v_cobrado cobrado,v_pendiente por_cobrar,v_activos contratos_activos,ROUND(IFNULL(v_cobrado/NULLIF(v_cobrado+v_pendiente,0)*100,0),2) efectividad; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_dashboard_ventas_servicios` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_dashboard_ventas_servicios`(IN p_desde DATE,IN p_hasta DATE)
BEGIN SELECT s.nombre,COALESCE(SUM(c.monto_neto),0) contratado,COALESCE((SELECT SUM(co.monto_total) FROM cobranza co JOIN contrato cx ON cx.id_contrato=co.id_contrato WHERE co.estado='REGISTRADO' AND cx.id_servicio=s.id_servicio AND DATE(co.fecha) BETWEEN p_desde AND p_hasta),0) cobrado FROM servicio s LEFT JOIN contrato c ON c.id_servicio=s.id_servicio AND c.estado<>'ANULADO' AND c.fecha BETWEEN p_desde AND p_hasta GROUP BY s.id_servicio,s.nombre HAVING contratado>0 OR cobrado>0 ORDER BY contratado DESC,s.nombre LIMIT 8; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_egreso_anular` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_egreso_anular`(IN p_egreso INT UNSIGNED,IN p_motivo VARCHAR(500))
BEGIN IF CHAR_LENGTH(TRIM(p_motivo))=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='El motivo de anulación es obligatorio.'; END IF; UPDATE egreso SET estado='ANULADO',motivo_anulacion=p_motivo WHERE id_egreso=p_egreso AND estado='REGISTRADO'; IF ROW_COUNT()=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Egreso no disponible para anulación.'; END IF; SELECT 1 resultado,'Egreso anulado.' mensaje; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_egreso_listar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_egreso_listar`(IN p_inicio VARCHAR(10),IN p_fin VARCHAR(10),IN p_estado VARCHAR(15))
BEGIN SELECT * FROM egreso WHERE(p_inicio='' OR fecha>=p_inicio) AND(p_fin='' OR fecha<=p_fin) AND(p_estado='' OR estado=p_estado) ORDER BY fecha DESC,id_egreso DESC; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_egreso_registrar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_egreso_registrar`(IN p_fecha DATE,IN p_categoria VARCHAR(100),IN p_beneficiario VARCHAR(160),IN p_concepto VARCHAR(500),IN p_monto DECIMAL(12,2),IN p_personal INT UNSIGNED)
BEGIN INSERT INTO egreso(fecha,categoria,beneficiario,concepto,monto,id_registrado_por) VALUES(p_fecha,p_categoria,p_beneficiario,p_concepto,p_monto,p_personal); SELECT 1 resultado,'Egreso registrado.' mensaje,LAST_INSERT_ID() id_egreso; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_hitos_cobranza_listar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_hitos_cobranza_listar`(IN p_contrato INT UNSIGNED)
BEGIN SELECT *,monto-monto_pagado saldo FROM contrato_hito WHERE id_contrato=p_contrato AND estado IN('PENDIENTE','PARCIAL','VENCIDO') ORDER BY numero; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_login_obtener_hash` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_login_obtener_hash`(IN p_usuario VARCHAR(80))
BEGIN SELECT u.id_usuario,u.id_personal,u.usu_nombre,u.password_hash,u.usu_estado,u.locked_until,CONCAT(p.nombres,' ',p.apellidos) nombre_completo FROM usuario u JOIN personal p ON p.id_personal=u.id_personal WHERE u.usu_nombre=p_usuario AND p.estado=1 LIMIT 1; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_login_registrar_intento` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_login_registrar_intento`(IN p_usuario VARCHAR(80),IN p_exitoso TINYINT)
BEGIN DECLARE v_intentos TINYINT UNSIGNED DEFAULT 0; DECLARE v_inicio DATETIME; INSERT INTO login_intento(usuario,exitoso) VALUES(p_usuario,p_exitoso); IF p_exitoso=1 THEN UPDATE usuario SET failed_attempts=0,failed_window_started=NULL,locked_until=NULL,ultimo_login=NOW() WHERE usu_nombre=p_usuario; ELSE SELECT failed_attempts,failed_window_started INTO v_intentos,v_inicio FROM usuario WHERE usu_nombre=p_usuario FOR UPDATE; IF v_inicio IS NULL OR v_inicio<DATE_SUB(NOW(),INTERVAL 15 MINUTE) THEN SET v_intentos=1; SET v_inicio=NOW(); ELSE SET v_intentos=v_intentos+1; END IF; UPDATE usuario SET failed_attempts=v_intentos,failed_window_started=v_inicio,locked_until=IF(v_intentos>=5,DATE_ADD(NOW(),INTERVAL 15 MINUTE),NULL) WHERE usu_nombre=p_usuario; END IF; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_permiso_efectivo_listar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_permiso_efectivo_listar`(IN p_personal INT UNSIGNED)
BEGIN SELECT m.codigo modulo,pr.accion,cp.permitido,'CARGO' origen FROM personal pe JOIN cargo_permiso cp ON cp.id_cargo=pe.id_cargo JOIN permiso pr ON pr.id_permiso=cp.id_permiso JOIN modulo m ON m.id_modulo=pr.id_modulo WHERE pe.id_personal=p_personal UNION ALL SELECT m.codigo,pr.accion,pp.permitido,'PERSONA' FROM persona_permiso pp JOIN permiso pr ON pr.id_permiso=pp.id_permiso JOIN modulo m ON m.id_modulo=pr.id_modulo WHERE pp.id_personal=p_personal; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_permiso_listar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_permiso_listar`()
BEGIN SELECT pr.id_permiso,m.codigo modulo,pr.accion FROM permiso pr JOIN modulo m ON m.id_modulo=pr.id_modulo ORDER BY m.nombre,pr.accion; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_personal_guardar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_personal_guardar`(IN p_id INT UNSIGNED,IN p_dni CHAR(8),IN p_nombres VARCHAR(120),IN p_apellidos VARCHAR(120),IN p_correo VARCHAR(160),IN p_telefono VARCHAR(30),IN p_cargo INT UNSIGNED,IN p_estado TINYINT)
BEGIN IF p_id=0 THEN INSERT INTO personal(dni,nombres,apellidos,correo,telefono,id_cargo,estado) VALUES(p_dni,p_nombres,p_apellidos,NULLIF(p_correo,''),NULLIF(p_telefono,''),p_cargo,p_estado); SET p_id=LAST_INSERT_ID(); ELSE UPDATE personal SET dni=p_dni,nombres=p_nombres,apellidos=p_apellidos,correo=NULLIF(p_correo,''),telefono=NULLIF(p_telefono,''),id_cargo=p_cargo,estado=p_estado WHERE id_personal=p_id; END IF; SELECT 1 resultado,'Personal guardado.' mensaje,p_id id_personal; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_personal_listar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_personal_listar`()
BEGIN SELECT p.*,c.nombre cargo,u.id_usuario,u.usu_nombre,u.usu_estado FROM personal p JOIN cargo c ON c.id_cargo=p.id_cargo LEFT JOIN usuario u ON u.id_personal=p.id_personal ORDER BY p.apellidos,p.nombres; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_persona_permiso_configuracion_listar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_persona_permiso_configuracion_listar`(IN p_persona INT UNSIGNED)
BEGIN SELECT pr.id_permiso,m.codigo modulo,pr.accion,cp.permitido plantilla,pp.permitido excepcion,COALESCE(pp.permitido,cp.permitido,0) efectivo FROM personal pe CROSS JOIN permiso pr JOIN modulo m ON m.id_modulo=pr.id_modulo LEFT JOIN cargo_permiso cp ON cp.id_cargo=pe.id_cargo AND cp.id_permiso=pr.id_permiso LEFT JOIN persona_permiso pp ON pp.id_personal=pe.id_personal AND pp.id_permiso=pr.id_permiso WHERE pe.id_personal=p_persona ORDER BY m.nombre,pr.accion; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_persona_permiso_guardar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_persona_permiso_guardar`(IN p_personal INT UNSIGNED,IN p_permiso INT UNSIGNED,IN p_permitido TINYINT)
BEGIN INSERT INTO persona_permiso VALUES(p_personal,p_permiso,p_permitido) ON DUPLICATE KEY UPDATE permitido=p_permitido; SELECT 1 resultado,'Permiso individual guardado.' mensaje; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_persona_permiso_quitar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_persona_permiso_quitar`(IN p_persona INT UNSIGNED,IN p_permiso INT UNSIGNED)
BEGIN DELETE FROM persona_permiso WHERE id_personal=p_persona AND id_permiso=p_permiso; SELECT 1 resultado,'Excepción retirada; se aplica la plantilla del cargo.' mensaje; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_servicio_guardar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_servicio_guardar`(IN p_id INT UNSIGNED,IN p_nombre VARCHAR(150),IN p_descripcion VARCHAR(500),IN p_precio DECIMAL(12,2),IN p_estado TINYINT)
BEGIN IF p_id=0 THEN INSERT INTO servicio(nombre,descripcion,precio_referencia,estado) VALUES(p_nombre,NULLIF(p_descripcion,''),p_precio,p_estado); SET p_id=LAST_INSERT_ID(); ELSE UPDATE servicio SET nombre=p_nombre,descripcion=NULLIF(p_descripcion,''),precio_referencia=p_precio,estado=p_estado WHERE id_servicio=p_id; END IF; SELECT 1 resultado,'Servicio guardado.' mensaje,p_id id_servicio; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_servicio_listar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_servicio_listar`()
BEGIN SELECT * FROM servicio ORDER BY nombre; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_usuario_guardar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_usuario_guardar`(IN p_id INT UNSIGNED,IN p_personal INT UNSIGNED,IN p_nombre VARCHAR(80),IN p_hash VARCHAR(255),IN p_estado TINYINT)
BEGIN IF p_id=0 THEN INSERT INTO usuario(id_personal,usu_nombre,password_hash,usu_estado) VALUES(p_personal,p_nombre,p_hash,p_estado); ELSE UPDATE usuario SET usu_nombre=p_nombre,usu_estado=p_estado,password_hash=IF(p_hash='',password_hash,p_hash) WHERE id_usuario=p_id; END IF; SELECT 1 resultado,'Usuario guardado.' mensaje,IF(p_id=0,LAST_INSERT_ID(),p_id) id_usuario; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_voucher_obtener` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_voucher_obtener`(IN p_voucher INT UNSIGNED)
BEGIN SELECT * FROM voucher WHERE id_voucher=p_voucher AND estado='VALIDADO'; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_voucher_registrar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_voucher_registrar`(IN p_cobranza INT UNSIGNED,IN p_nombre VARCHAR(80),IN p_mime VARCHAR(80),IN p_tamano INT,IN p_hash CHAR(64))
BEGIN INSERT INTO voucher(id_cobranza,nombre_interno,mime,tamano,hash_sha256) VALUES(p_cobranza,p_nombre,p_mime,p_tamano,p_hash); SELECT 1 resultado,'Voucher registrado.' mensaje,LAST_INSERT_ID() id_voucher; END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-22 17:43:37
