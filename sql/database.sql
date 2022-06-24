-- MySQL dump 10.13  Distrib 5.7.37, for Win32 (AMD64)
--
-- Host: 127.0.0.1    Database: nic_db
-- ------------------------------------------------------
-- Server version	5.5.5-10.7.3-MariaDB-1:10.7.3+maria~focal

use nic_db;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `act_serv_func_reg`
--

DROP TABLE IF EXISTS `act_serv_func_reg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `act_serv_func_reg` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `service_function_id` bigint(255) NOT NULL,
  `template` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_service_function_id_act_serv_func_reg` (`service_function_id`),
  CONSTRAINT `foreign_service_function_id_act_serv_func_reg` FOREIGN KEY (`service_function_id`) REFERENCES `service_function` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `act_serv_func_reg`
--

LOCK TABLES `act_serv_func_reg` WRITE;
/*!40000 ALTER TABLE `act_serv_func_reg` DISABLE KEYS */;
/*!40000 ALTER TABLE `act_serv_func_reg` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity`
--

DROP TABLE IF EXISTS `activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `service_function_id` bigint(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `moment` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_service_function_id_activity` (`service_function_id`)
) ENGINE=InnoDB AUTO_INCREMENT=370 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity`
--

LOCK TABLES `activity` WRITE;
/*!40000 ALTER TABLE `activity` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client`
--

DROP TABLE IF EXISTS `client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client`
--

LOCK TABLES `client` WRITE;
/*!40000 ALTER TABLE `client` DISABLE KEYS */;
/*!40000 ALTER TABLE `client` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_path`
--

DROP TABLE IF EXISTS `client_path`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_path` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `dropbox_tk_id` bigint(255) NOT NULL,
  `client_id` bigint(255) NOT NULL,
  `directory_id` bigint(255) NOT NULL,
  `max_byte_cloud` bigint(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_dropbox_tk_id_client_path` (`dropbox_tk_id`),
  KEY `foreign_client_id_client_path` (`client_id`),
  KEY `foreign_directory_id_client_path` (`directory_id`),
  CONSTRAINT `foreign_client_id_client_path` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`) ON DELETE CASCADE,
  CONSTRAINT `foreign_directory_id_client_path` FOREIGN KEY (`directory_id`) REFERENCES `directory` (`id`) ON DELETE CASCADE,
  CONSTRAINT `foreign_dropbox_tk_id_client_path` FOREIGN KEY (`dropbox_tk_id`) REFERENCES `dropbox_tk` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_path`
--

LOCK TABLES `client_path` WRITE;
/*!40000 ALTER TABLE `client_path` DISABLE KEYS */;
/*!40000 ALTER TABLE `client_path` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `directory`
--

DROP TABLE IF EXISTS `directory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `directory` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `directory_id` bigint(255) DEFAULT NULL,
  `nome` varchar(255) NOT NULL,
  `hash_dir` varchar(255) NOT NULL,
  `client_id` bigint(255) NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash_dir` (`hash_dir`),
  KEY `foreign_client_id_directory` (`client_id`),
  KEY `foreign_directory_id_directory` (`directory_id`),
  CONSTRAINT `foreign_client_id_directory` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`) ON DELETE CASCADE,
  CONSTRAINT `foreign_directory_id_directory` FOREIGN KEY (`directory_id`) REFERENCES `directory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `directory`
--

LOCK TABLES `directory` WRITE;
/*!40000 ALTER TABLE `directory` DISABLE KEYS */;
/*!40000 ALTER TABLE `directory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `download_link`
--

DROP TABLE IF EXISTS `download_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `download_link` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `file_client_id` bigint(255) NOT NULL,
  `link` varchar(300) NOT NULL,
  `expire` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_file_client_id_download_link` (`file_client_id`),
  CONSTRAINT `foreign_file_client_id_download_link` FOREIGN KEY (`file_client_id`) REFERENCES `file_client` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `download_link`
--

LOCK TABLES `download_link` WRITE;
/*!40000 ALTER TABLE `download_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `download_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dropbox_tk`
--

DROP TABLE IF EXISTS `dropbox_tk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dropbox_tk` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `refresh_token` varchar(255) NOT NULL,
  `account_email` varchar(255) NOT NULL,
  `account_senha` varchar(255) NOT NULL,
  `app_key` varchar(255) NOT NULL,
  `secret_key` varchar(255) NOT NULL,
  `temp_key` varchar(255) DEFAULT NULL,
  `expire_temp_key` datetime DEFAULT NULL,
  `limit_size` bigint(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dropbox_tk`
--

LOCK TABLES `dropbox_tk` WRITE;
/*!40000 ALTER TABLE `dropbox_tk` DISABLE KEYS */;
/*!40000 ALTER TABLE `dropbox_tk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_client`
--

DROP TABLE IF EXISTS `file_client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_client` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `dropbox_hash_id` varchar(255) DEFAULT NULL,
  `hash_file` varchar(255) NOT NULL,
  `mime_type` varchar(5) NOT NULL,
  `saved` tinyint(1) NOT NULL DEFAULT 0,
  `directory_id` bigint(255) NOT NULL,
  `public` tinyint(1) NOT NULL DEFAULT 0,
  `ghost` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash_file` (`hash_file`),
  KEY `foreign_directory_id_file_client` (`directory_id`),
  CONSTRAINT `foreign_directory_id_file_client` FOREIGN KEY (`directory_id`) REFERENCES `directory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=344 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_client`
--

LOCK TABLES `file_client` WRITE;
/*!40000 ALTER TABLE `file_client` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_client` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_client_info`
--

DROP TABLE IF EXISTS `file_client_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_client_info` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `file_client_id` bigint(255) NOT NULL,
  `created_user_id` bigint(255) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `editedAt` datetime DEFAULT NULL,
  `size_bytes` bigint(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `file_client_id` (`file_client_id`),
  KEY `foreign_created_user_id_file_client_info` (`created_user_id`),
  CONSTRAINT `foreign_created_user_id_file_client_info` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `foreign_file_client_id_file_client_info` FOREIGN KEY (`file_client_id`) REFERENCES `file_client` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=197 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_client_info`
--

LOCK TABLES `file_client_info` WRITE;
/*!40000 ALTER TABLE `file_client_info` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_client_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_client_tag`
--

DROP TABLE IF EXISTS `file_client_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_client_tag` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `file_client_id` bigint(255) NOT NULL,
  `nome` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_file_client_id_file_client_tag` (`file_client_id`),
  CONSTRAINT `foreign_file_client_id_file_client_tag` FOREIGN KEY (`file_client_id`) REFERENCES `file_client` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=445 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_client_tag`
--

LOCK TABLES `file_client_tag` WRITE;
/*!40000 ALTER TABLE `file_client_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_client_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_to_delete`
--

DROP TABLE IF EXISTS `file_to_delete`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_to_delete` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `dropbox_tk_id` bigint(255) NOT NULL,
  `dropbox_hash_id` varchar(300) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `foreign_dropbox_tk_id_file_to_delete` (`dropbox_tk_id`),
  CONSTRAINT `foreign_dropbox_tk_id_file_to_delete` FOREIGN KEY (`dropbox_tk_id`) REFERENCES `dropbox_tk` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_to_delete`
--

LOCK TABLES `file_to_delete` WRITE;
/*!40000 ALTER TABLE `file_to_delete` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_to_delete` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mime_type`
--

DROP TABLE IF EXISTS `mime_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mime_type` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `ext` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mime_type`
--

LOCK TABLES `mime_type` WRITE;
/*!40000 ALTER TABLE `mime_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `mime_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `options`
--

DROP TABLE IF EXISTS `options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `options` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `pergunta_id` bigint(255) NOT NULL,
  `valor` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_pergunta_id_options` (`pergunta_id`),
  CONSTRAINT `foreign_pergunta_id_options` FOREIGN KEY (`pergunta_id`) REFERENCES `pergunta` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `options`
--

LOCK TABLES `options` WRITE;
/*!40000 ALTER TABLE `options` DISABLE KEYS */;
/*!40000 ALTER TABLE `options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pergunta`
--

DROP TABLE IF EXISTS `pergunta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pergunta` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `pesquisa_id` bigint(255) NOT NULL,
  `valor` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `required` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_pesquisa_id_pergunta` (`pesquisa_id`),
  CONSTRAINT `foreign_pesquisa_id_pergunta` FOREIGN KEY (`pesquisa_id`) REFERENCES `pesquisa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pergunta`
--

LOCK TABLES `pergunta` WRITE;
/*!40000 ALTER TABLE `pergunta` DISABLE KEYS */;
/*!40000 ALTER TABLE `pergunta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_func`
--

DROP TABLE IF EXISTS `permission_func`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_func` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `permission_pool_id` bigint(255) NOT NULL,
  `service_function_id` bigint(255) NOT NULL,
  `fixed` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `foreign_permission_pool_id_permission_func` (`permission_pool_id`),
  KEY `foreign_service_function_id_permission_func` (`service_function_id`),
  CONSTRAINT `foreign_permission_pool_id_permission_func` FOREIGN KEY (`permission_pool_id`) REFERENCES `permission_pool` (`id`),
  CONSTRAINT `foreign_service_function_id_permission_func` FOREIGN KEY (`service_function_id`) REFERENCES `service_function` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1924 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_func`
--

LOCK TABLES `permission_func` WRITE;
/*!40000 ALTER TABLE `permission_func` DISABLE KEYS */;
INSERT INTO `permission_func` VALUES (82,8,80,1),(83,5,80,1),(1868,3,1720,0),(1869,3,1721,0),(1870,3,1722,0),(1871,3,1723,0),(1872,3,1724,0),(1873,3,1725,0),(1874,3,1726,0),(1875,5,1727,0),(1876,5,1728,0),(1877,8,1728,0),(1878,5,1729,0),(1879,5,1730,0),(1880,5,1731,0),(1881,5,1732,0),(1882,5,1733,0),(1883,5,1734,0),(1884,5,1735,0),(1885,5,1736,0),(1886,5,1737,0),(1887,5,1738,0),(1888,5,1739,0),(1889,5,1740,0),(1890,5,1741,0),(1891,5,1742,0),(1892,5,1743,0),(1893,5,1744,0),(1894,8,1744,0),(1895,5,1745,0),(1896,8,1745,0),(1897,5,1746,0),(1898,17,1747,0),(1899,17,1748,0),(1900,17,1749,0),(1901,17,1750,0),(1902,17,1751,0),(1903,17,1752,0),(1904,18,1753,0),(1905,17,1753,0),(1906,18,1754,0),(1907,18,1755,0),(1908,18,1756,0),(1909,18,1757,0),(1910,18,1758,0),(1911,17,1759,0),(1912,17,1760,0),(1913,17,1761,0),(1914,2,1762,0),(1915,16,1763,0),(1916,2,1763,0),(1917,2,1764,0),(1918,2,1765,0),(1919,2,1766,0),(1920,2,1767,0),(1921,2,1768,0),(1922,3,1769,0),(1923,3,1770,0);
/*!40000 ALTER TABLE `permission_func` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_pool`
--

DROP TABLE IF EXISTS `permission_pool`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_pool` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `ativo` tinyint(1) NOT NULL,
  `pattern` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_pool`
--

LOCK TABLES `permission_pool` WRITE;
/*!40000 ALTER TABLE `permission_pool` DISABLE KEYS */;
INSERT INTO `permission_pool` VALUES (2,'administrar_contas','Administrar Contas','Listar, Criar, Editar e Bloquear',1,0),(3,'public','','',1,1),(5,'arquivos_full','Arquivos Full','Criar, Editar e Excluir',1,0),(6,'tarefas_full','Tarefas Full','Criar, Editar, Ler, Concluir, Fechar e Enviar arquivos',1,0),(7,'tarefas_basico','Tarefas Básico','Ler e Enviar Arquivos',1,0),(8,'arquivos_basico','Arquivos Básico','Ver, listar e fazer download',1,0),(16,'contas_basico','Ver Contatos','Listar',1,1),(17,'pesquisas_full','Pesquisas Full','Criar, Editar, Finalizar, Publicar e Excluir Questionários',1,0),(18,'pesquisas_basico','Pesquisas Básico','Usar questionários para responder as perguntas',1,0);
/*!40000 ALTER TABLE `permission_pool` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pesquisa`
--

DROP TABLE IF EXISTS `pesquisa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pesquisa` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `client_id` bigint(255) NOT NULL,
  `created_by` bigint(255) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `createdAt` datetime NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `foreign_client_id_pesquisa` (`client_id`),
  KEY `foreign_created_by_pesquisa` (`created_by`),
  CONSTRAINT `foreign_client_id_pesquisa` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`) ON DELETE CASCADE,
  CONSTRAINT `foreign_created_by_pesquisa` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pesquisa`
--

LOCK TABLES `pesquisa` WRITE;
/*!40000 ALTER TABLE `pesquisa` DISABLE KEYS */;
/*!40000 ALTER TABLE `pesquisa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pesquisa_profile_fields`
--

DROP TABLE IF EXISTS `pesquisa_profile_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pesquisa_profile_fields` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `pesquisa_id` bigint(255) NOT NULL,
  `field` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_pesquisa_id_pesquisa_profile_fields` (`pesquisa_id`),
  CONSTRAINT `foreign_pesquisa_id_pesquisa_profile_fields` FOREIGN KEY (`pesquisa_id`) REFERENCES `pesquisa` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pesquisa_profile_fields`
--

LOCK TABLES `pesquisa_profile_fields` WRITE;
/*!40000 ALTER TABLE `pesquisa_profile_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `pesquisa_profile_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recover`
--

DROP TABLE IF EXISTS `recover`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recover` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(255) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `code` varchar(100) NOT NULL,
  `expire` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_user_id_recover` (`user_id`),
  CONSTRAINT `foreign_user_id_recover` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recover`
--

LOCK TABLES `recover` WRITE;
/*!40000 ALTER TABLE `recover` DISABLE KEYS */;
/*!40000 ALTER TABLE `recover` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resposta`
--

DROP TABLE IF EXISTS `resposta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resposta` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `option_id` bigint(255) NOT NULL,
  `user_resposta_id` bigint(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_option_id_resposta` (`option_id`),
  KEY `foreign_user_resposta_id_resposta` (`user_resposta_id`),
  CONSTRAINT `foreign_option_id_resposta` FOREIGN KEY (`option_id`) REFERENCES `options` (`id`) ON DELETE CASCADE,
  CONSTRAINT `foreign_user_resposta_id_resposta` FOREIGN KEY (`user_resposta_id`) REFERENCES `user_resposta` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resposta`
--

LOCK TABLES `resposta` WRITE;
/*!40000 ALTER TABLE `resposta` DISABLE KEYS */;
/*!40000 ALTER TABLE `resposta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service`
--

DROP TABLE IF EXISTS `service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `route` varchar(255) NOT NULL,
  `ativo` tinyint(1) NOT NULL,
  `fixed` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=284 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service`
--

LOCK TABLES `service` WRITE;
/*!40000 ALTER TABLE `service` DISABLE KEYS */;
INSERT INTO `service` VALUES (40,'file_tools','no-route',1,1),(279,'@auth','auth.php',1,0),(280,'client@files','client/files.php',1,0),(281,'client@pesquisas','client/pesquisas.php',1,0),(282,'client@users','client/users.php',1,0),(283,'@test','test.php',1,0);
/*!40000 ALTER TABLE `service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_function`
--

DROP TABLE IF EXISTS `service_function`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_function` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `service_id` bigint(255) NOT NULL,
  `fixed` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `foreign_service_id` (`service_id`),
  CONSTRAINT `foreign_service_id` FOREIGN KEY (`service_id`) REFERENCES `service` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1771 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_function`
--

LOCK TABLES `service_function` WRITE;
/*!40000 ALTER TABLE `service_function` DISABLE KEYS */;
INSERT INTO `service_function` VALUES (80,'get',40,1),(1720,'logar',279,0),(1721,'forgot',279,0),(1722,'refresh_user_client',279,0),(1723,'refresh_user_admin',279,0),(1724,'load_me',279,0),(1725,'get_me',279,0),(1726,'change_me',279,0),(1727,'get_files_info',280,0),(1728,'get_public_link',280,0),(1729,'get_link',280,0),(1730,'move_file',280,0),(1731,'delete_file',280,0),(1732,'edit_file_name',280,0),(1733,'create_ghost_file',280,0),(1734,'get_tags',280,0),(1735,'remove_tag',280,0),(1736,'add_tag',280,0),(1737,'add_folder',280,0),(1738,'file_info',280,0),(1739,'edit_dir_name',280,0),(1740,'save_file',280,0),(1741,'list_all_folders',280,0),(1742,'list_all_files',280,0),(1743,'publish_file',280,0),(1744,'list_public_files',280,0),(1745,'search_public_files',280,0),(1746,'search_all_files',280,0),(1747,'estatistica_perfil',281,0),(1748,'editar_titulo',281,0),(1749,'excluir_pesquisa',281,0),(1750,'publicar_pesquisa',281,0),(1751,'finalizar_pesquisa',281,0),(1752,'estatistica_votos',281,0),(1753,'list_pesquisas',281,0),(1754,'get_new_user_resposta',281,0),(1755,'salvar_resposta',281,0),(1756,'get_inputs_fields',281,0),(1757,'get_profile_fields',281,0),(1758,'get_perguntas',281,0),(1759,'criar_pesquisa',281,0),(1760,'criar_cadastro_pesquisa',281,0),(1761,'criar_pergunta',281,0),(1762,'get_permissions_user',282,0),(1763,'list_all',282,0),(1764,'create_client_user',282,0),(1765,'delete_user',282,0),(1766,'update_user',282,0),(1767,'ativar_user',282,0),(1768,'reset_pass',282,0),(1769,'other_test',283,0),(1770,'test',283,0);
/*!40000 ALTER TABLE `service_function` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(255) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `expire` datetime NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `foreign_user_id_session` (`user_id`),
  CONSTRAINT `foreign_user_id_session` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session`
--

LOCK TABLES `session` WRITE;
/*!40000 ALTER TABLE `session` DISABLE KEYS */;
/*!40000 ALTER TABLE `session` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `senha_temp` varchar(255) DEFAULT NULL,
  `cargo` varchar(255) DEFAULT NULL,
  `telefone` varchar(255) DEFAULT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT 0,
  `ghost_id` bigint(255) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_ghost_id_user` (`ghost_id`),
  CONSTRAINT `foreign_ghost_id_user` FOREIGN KEY (`ghost_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_activity`
--

DROP TABLE IF EXISTS `user_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_activity` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(255) NOT NULL,
  `activity_id` bigint(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_user_id_user_activity` (`user_id`),
  KEY `foreign_activity_id_user_activity` (`activity_id`),
  CONSTRAINT `foreign_activity_id_user_activity` FOREIGN KEY (`activity_id`) REFERENCES `activity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `foreign_user_id_user_activity` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=370 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_activity`
--

LOCK TABLES `user_activity` WRITE;
/*!40000 ALTER TABLE `user_activity` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_admin`
--

DROP TABLE IF EXISTS `user_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_admin` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_user_id_user_admin` (`user_id`),
  CONSTRAINT `foreign_user_id_user_admin` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_admin`
--

LOCK TABLES `user_admin` WRITE;
/*!40000 ALTER TABLE `user_admin` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_client`
--

DROP TABLE IF EXISTS `user_client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_client` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(255) NOT NULL,
  `client_id` bigint(255) NOT NULL,
  `master` tinyint(1) NOT NULL,
  `ghost` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `foreign_client_id_user_client` (`client_id`),
  CONSTRAINT `foreign_client_id_user_client` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`) ON DELETE CASCADE,
  CONSTRAINT `foreign_user_id_user_client` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_client`
--

LOCK TABLES `user_client` WRITE;
/*!40000 ALTER TABLE `user_client` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_client` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_permission`
--

DROP TABLE IF EXISTS `user_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_permission` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `permission_pool_id` bigint(255) NOT NULL,
  `user_id` bigint(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_user_id_user_permission` (`user_id`),
  KEY `foreign_permission_pool_id_user_permission` (`permission_pool_id`),
  CONSTRAINT `foreign_permission_pool_id_user_permission` FOREIGN KEY (`permission_pool_id`) REFERENCES `permission_pool` (`id`) ON DELETE CASCADE,
  CONSTRAINT `foreign_user_id_user_permission` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_permission`
--

LOCK TABLES `user_permission` WRITE;
/*!40000 ALTER TABLE `user_permission` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_resposta`
--

DROP TABLE IF EXISTS `user_resposta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_resposta` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `pesquisa_id` bigint(255) NOT NULL,
  `response` tinyint(1) NOT NULL DEFAULT 0,
  `data_resposta` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_pesquisa_id_user_resposta` (`pesquisa_id`),
  CONSTRAINT `foreign_pesquisa_id_user_resposta` FOREIGN KEY (`pesquisa_id`) REFERENCES `pesquisa` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_resposta`
--

LOCK TABLES `user_resposta` WRITE;
/*!40000 ALTER TABLE `user_resposta` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_resposta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_resposta_cad_field`
--

DROP TABLE IF EXISTS `user_resposta_cad_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_resposta_cad_field` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `pesquisa_id` bigint(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_pesquisa_id_user_resposta_cad_field` (`pesquisa_id`),
  CONSTRAINT `foreign_pesquisa_id_user_resposta_cad_field` FOREIGN KEY (`pesquisa_id`) REFERENCES `pesquisa` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_resposta_cad_field`
--

LOCK TABLES `user_resposta_cad_field` WRITE;
/*!40000 ALTER TABLE `user_resposta_cad_field` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_resposta_cad_field` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_resposta_cad_value`
--

DROP TABLE IF EXISTS `user_resposta_cad_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_resposta_cad_value` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `user_resposta_id` bigint(255) NOT NULL,
  `user_resposta_cad_field_id` bigint(255) NOT NULL,
  `valor` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_user_resposta_cad_user_resposta_cad_valuefield_id_` (`user_resposta_cad_field_id`),
  KEY `foreign_user_resposta_id_user_resposta_cad_value` (`user_resposta_id`),
  CONSTRAINT `foreign_user_resposta_cad_user_resposta_cad_valuefield_id_` FOREIGN KEY (`user_resposta_cad_field_id`) REFERENCES `user_resposta_cad_field` (`id`),
  CONSTRAINT `foreign_user_resposta_id_user_resposta_cad_value` FOREIGN KEY (`user_resposta_id`) REFERENCES `user_resposta` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_resposta_cad_value`
--

LOCK TABLES `user_resposta_cad_value` WRITE;
/*!40000 ALTER TABLE `user_resposta_cad_value` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_resposta_cad_value` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_resposta_profile`
--

DROP TABLE IF EXISTS `user_resposta_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_resposta_profile` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `user_resposta_id` bigint(255) NOT NULL,
  `sexo` char(1) DEFAULT NULL,
  `idade` int(3) DEFAULT NULL,
  `casado` tinyint(1) DEFAULT NULL,
  `filhos` int(2) DEFAULT NULL,
  `cor` varchar(100) DEFAULT NULL COMMENT 'branca, preta, parda, indígena ou amarela',
  `genero` varchar(100) DEFAULT NULL COMMENT 'masculino, feminino, transgênero, neutro, não-binário, cisgênero',
  `escolaridade` varchar(255) DEFAULT NULL COMMENT 'fundamental, fundamental incompleto, medio, medio incompleto, superior, superior incompleto, sem instrução',
  `salario` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `foreign_user_resposta_id_user_resposta_profile` (`user_resposta_id`),
  CONSTRAINT `foreign_user_resposta_id_user_resposta_profile` FOREIGN KEY (`user_resposta_id`) REFERENCES `user_resposta` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_resposta_profile`
--

LOCK TABLES `user_resposta_profile` WRITE;
/*!40000 ALTER TABLE `user_resposta_profile` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_resposta_profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `view_page`
--

DROP TABLE IF EXISTS `view_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `view_page` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `main` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `view_page`
--

LOCK TABLES `view_page` WRITE;
/*!40000 ALTER TABLE `view_page` DISABLE KEYS */;
INSERT INTO `view_page` VALUES (1,'accounts','Contas','mdi-account-box-outline',0),(2,'tasks','Tarefas','mdi-book-open',0),(3,'files','Arquivos','mdi-folder-open',1),(4,'pesquisas','Pesquisas','mdi-flask-outline',0);
/*!40000 ALTER TABLE `view_page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `view_subpage`
--

DROP TABLE IF EXISTS `view_subpage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `view_subpage` (
  `id` bigint(255) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `icon` varchar(100) NOT NULL,
  `view_page_id` bigint(255) NOT NULL,
  `permission_pool_id` bigint(255) NOT NULL,
  `main` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `foreign_page_view_id_subpage_view` (`view_page_id`),
  KEY `foreign_permission_pool_id_subpage_view` (`permission_pool_id`),
  CONSTRAINT `foreign_page_view_id_subpage_view` FOREIGN KEY (`view_page_id`) REFERENCES `view_page` (`id`),
  CONSTRAINT `foreign_permission_pool_id_subpage_view` FOREIGN KEY (`permission_pool_id`) REFERENCES `permission_pool` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `view_subpage`
--

LOCK TABLES `view_subpage` WRITE;
/*!40000 ALTER TABLE `view_subpage` DISABLE KEYS */;
INSERT INTO `view_subpage` VALUES (1,'add_user','Adicionar Usuário','mdi-plus-box-outline',1,2,0),(3,'list_files','Arquivos Publicados','mdi-format-list-bulleted',3,8,1),(4,'add_task','Nova Tarefa','mdi-plus-box-outline',2,6,0),(5,'list_my_tasks','Minhas Tarefas','mdi-format-list-checks',2,7,0),(6,'list_tasks','Todas as Tarefas','mdi-format-list-bulleted',2,6,1),(7,'explore_files','Explorar Arquivos','mdi-compass-outline',3,5,2),(8,'list_users','Lista de Usuários','mdi-format-list-bulleted',1,3,1),(9,'list_pesquisas_ativas','Pesquisas Ativas','mdi-format-list-bulleted',4,18,1),(10,'add_pesquisa','Adicionar Pesquisa','mdi-plus-box-outline',4,17,0),(11,'list_pesquisas_all','Gerenciar Pesquisas','mdi-format-list-checks',4,17,0);
/*!40000 ALTER TABLE `view_subpage` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-06-20 12:08:59
