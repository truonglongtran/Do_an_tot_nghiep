-- MySQL dump 10.13  Distrib 8.0.36, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: clone_sp
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_logs`
--

DROP TABLE IF EXISTS `admin_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` bigint unsigned NOT NULL,
  `action_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_id` bigint unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_logs_admin_id_foreign` (`admin_id`),
  CONSTRAINT `admin_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_logs`
--

LOCK TABLES `admin_logs` WRITE;
/*!40000 ALTER TABLE `admin_logs` DISABLE KEYS */;
INSERT INTO `admin_logs` VALUES (1,5,'dispute_resolved',16,'Esse ullam aspernatur facere voluptatem quasi sit.','2025-03-29 01:27:14','2025-02-07 05:48:29'),(2,5,'product_approved',5,NULL,'2025-02-10 14:00:36','2025-03-25 04:39:00'),(3,2,'user_banned',2,'Ut non similique dolorum quia a.','2025-04-04 14:29:58','2025-04-27 03:48:13'),(4,4,'dispute_resolved',1,'Eos molestiae ut vel quod et neque asperiores.','2025-03-23 18:34:31','2025-02-26 11:29:29'),(5,4,'dispute_resolved',17,'Facilis molestias ut aut neque facere.','2025-04-19 16:58:04','2025-03-05 04:46:12'),(6,5,'product_approved',2,'Necessitatibus aut eveniet asperiores tempora consequatur.','2025-03-29 01:30:41','2025-02-16 05:11:05'),(7,5,'product_approved',1,'Ullam sit mollitia tempora nesciunt explicabo nihil similique.','2025-04-11 15:51:40','2025-04-11 10:01:18'),(8,3,'product_rejected',3,'Sunt et ut at perspiciatis placeat iusto.','2025-05-05 09:27:29','2025-03-12 14:26:04'),(9,3,'dispute_resolved',12,'Error quis exercitationem aperiam nemo optio sit.','2025-02-15 17:33:45','2025-03-06 07:06:35'),(10,5,'dispute_resolved',14,'Porro ut soluta beatae molestiae.','2025-04-19 15:41:54','2025-02-18 04:09:57'),(11,1,'product_rejected',1,'Facere quos et quas autem non.','2025-03-18 13:07:22','2025-03-25 16:03:21'),(12,5,'product_rejected',1,'Unde odio eveniet in veniam explicabo rem cupiditate.','2025-02-10 01:49:19','2025-03-02 12:36:08'),(13,5,'user_banned',2,'Aperiam esse a debitis provident consequuntur impedit rem.','2025-03-20 18:54:52','2025-03-18 04:59:36'),(14,5,'product_approved',1,'Possimus fugiat animi illo qui consequuntur.','2025-03-05 07:20:45','2025-04-25 19:48:07'),(15,2,'dispute_resolved',12,'Sequi perferendis et quia nisi et deleniti autem.','2025-03-27 10:30:58','2025-03-11 06:40:03'),(16,1,'user_banned',3,'Eos tenetur quibusdam aut et accusantium.','2025-03-10 11:30:26','2025-04-17 14:12:01'),(17,5,'dispute_rejected',16,'Unde nisi aut cumque distinctio laborum ut sit.','2025-03-18 12:39:06','2025-03-16 10:26:43'),(18,3,'dispute_resolved',16,'Omnis qui nostrum commodi dolores earum.','2025-04-30 09:15:21','2025-04-20 19:14:06'),(19,1,'dispute_rejected',3,'Deserunt tenetur consequatur exercitationem ullam et qui voluptatem officiis.','2025-04-15 11:31:23','2025-03-08 04:56:37'),(20,2,'product_rejected',4,'Accusamus nostrum est quia qui.','2025-02-27 21:49:34','2025-04-07 12:24:56');
/*!40000 ALTER TABLE `admin_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('superadmin','admin','moderator') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'superadmin@example.com','$2y$12$G/6spLGEQIB2Rkvh9MEokuCFqgf1aSIdxDJN3vKUjTp663peHvUfm','superadmin','active','2025-05-07 04:01:55','2025-05-07 04:01:55'),(2,'admin1@example.com','$2y$12$HFTrsbePK2QzQm7L/PWFe.YsrzLFgzHYyaZjyIqlPGKM.wIESZ.9i','admin','active','2025-05-07 04:01:55','2025-05-07 04:01:55'),(3,'admin2@example.com','$2y$12$1DlqEupDEHsPRonJh7PAtOj4Pa3mdmNzxus140DvtMgYv7D2rhw4W','admin','active','2025-05-07 04:01:55','2025-05-07 04:01:55'),(4,'moderator1@example.com','$2y$12$mekTHs63gq7OrwH3w00jY.kp3GXAHYCLFvWTCdJliV.LNDcCcEqqe','moderator','active','2025-05-07 04:01:56','2025-05-07 04:01:56'),(5,'moderator2@example.com','$2y$12$5m8NoP.8jR4P2v/V12nk4OpQtHAXRlugs6VjF1zsB4V/wFoE57fiK','moderator','active','2025-05-07 04:01:56','2025-05-07 04:01:56');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banners`
--

DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `img_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banners`
--

LOCK TABLES `banners` WRITE;
/*!40000 ALTER TABLE `banners` DISABLE KEYS */;
INSERT INTO `banners` VALUES (1,'Vero necessitatibus enim architecto.','https://via.placeholder.com/1200x400.png/000011?text=blanditiis','http://www.runolfsdottir.com/placeat-explicabo-est-est-id-sint-placeat.html','product_page','2025-04-26','2025-05-25','2025-05-06 04:27:23','2025-03-24 17:45:04'),(2,'Quam quasi itaque.','https://via.placeholder.com/1200x400.png/007733?text=nesciunt','http://toy.com/eveniet-reprehenderit-sequi-molestiae-et','category_page','2025-04-25','2025-06-07','2025-02-20 10:48:48','2025-04-17 15:59:29'),(3,'Dolorem placeat cum.','https://via.placeholder.com/1200x400.png/00cc11?text=non',NULL,'category_page','2025-04-11','2025-05-23','2025-03-20 13:46:57','2025-02-27 01:40:02'),(4,'Est molestiae laborum eum.','https://via.placeholder.com/1200x400.png/008822?text=et','https://www.corwin.org/sit-aperiam-rerum-qui-nam','product_page','2025-04-07','2025-06-02','2025-02-23 03:59:52','2025-04-25 08:02:06'),(5,'Qui omnis aliquid.','https://via.placeholder.com/1200x400.png/001133?text=inventore',NULL,'homepage_top','2025-04-19','2025-05-15','2025-04-10 04:03:51','2025-04-29 07:48:56'),(6,'Officia iste eos dicta.','https://via.placeholder.com/1200x400.png/000066?text=voluptas','http://www.christiansen.biz/debitis-sed-accusantium-molestias-in-accusamus.html','category_page','2025-04-10','2025-06-03','2025-04-03 01:53:43','2025-02-21 07:09:10'),(7,'Sit neque et aliquam similique.','https://via.placeholder.com/1200x400.png/0066ff?text=optio','http://www.huels.com/sed-corrupti-possimus-cum-et-ut-ad-suscipit.html','product_page','2025-04-13','2025-06-03','2025-02-12 08:17:57','2025-02-24 02:01:49'),(8,'Sequi provident aut impedit.','https://via.placeholder.com/1200x400.png/0033ff?text=consequatur','https://www.keeling.com/id-sunt-reprehenderit-magnam','category_page','2025-05-01','2025-05-08','2025-02-22 20:55:58','2025-03-01 16:47:06'),(9,'Saepe est voluptas omnis eius.','https://via.placeholder.com/1200x400.png/0011ff?text=provident','http://heathcote.net/quo-est-voluptatem-corporis-saepe-et-amet-voluptatem-quisquam.html','category_page','2025-04-23','2025-05-07','2025-02-25 14:56:32','2025-03-25 19:27:17'),(10,'Et saepe qui aut.','https://via.placeholder.com/1200x400.png/0055bb?text=velit',NULL,'product_page','2025-04-28','2025-06-05','2025-04-09 22:14:25','2025-04-11 05:54:36');
/*!40000 ALTER TABLE `banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `disputes`
--

DROP TABLE IF EXISTS `disputes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `disputes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `buyer_id` bigint unsigned NOT NULL,
  `seller_id` bigint unsigned NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('open','resolved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `disputes_order_id_foreign` (`order_id`),
  KEY `disputes_buyer_id_foreign` (`buyer_id`),
  KEY `disputes_seller_id_foreign` (`seller_id`),
  CONSTRAINT `disputes_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `disputes_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `disputes_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disputes`
--

LOCK TABLES `disputes` WRITE;
/*!40000 ALTER TABLE `disputes` DISABLE KEYS */;
INSERT INTO `disputes` VALUES (1,50,2,3,'Alias rerum officia iure laboriosam hic libero assumenda. Tempore nobis maxime temporibus qui sit.','resolved','Dolore earum voluptas natus aliquam natus tenetur quod sit.','2025-02-25 18:18:13','2025-04-01 04:41:16'),(2,7,2,3,'Et amet sint omnis illum. Hic accusamus inventore officiis qui. Voluptas recusandae unde eveniet architecto optio sapiente vel.','resolved','Id itaque rerum voluptatem libero autem.','2025-02-20 17:49:43','2025-03-30 06:00:12'),(3,3,2,4,'Iste dignissimos molestiae dignissimos. Harum molestiae ut sit a. Sed magni cumque vitae vel excepturi.','resolved','Quia consequatur ea sit sint.','2025-03-26 21:24:55','2025-04-18 06:35:48'),(4,28,1,3,'Consectetur quaerat tenetur dolor nulla similique a quas molestias. Consectetur nulla sint eius maiores. Aut quam ea vel ab consequatur qui consectetur.','resolved','Numquam quia cumque unde provident inventore porro.','2025-02-24 08:06:38','2025-04-21 22:18:50'),(5,37,2,3,'Voluptas sint laboriosam quia. Aspernatur facere sit magnam maiores.','resolved','Harum nihil officia excepturi sed aut.','2025-03-11 05:34:02','2025-04-12 01:34:26'),(6,48,2,3,'Voluptates qui voluptatum repudiandae dolorem illum. Eos qui ut quia quasi atque.','open',NULL,'2025-02-22 15:37:22','2025-04-11 12:22:53'),(7,21,1,4,'Voluptatem possimus quia quisquam dolorem quis. Voluptatem alias facere laborum.','resolved','Beatae accusamus quas facilis sed totam reiciendis.','2025-03-01 06:09:19','2025-02-26 12:50:41'),(8,26,2,3,'Accusantium id a eos neque. Hic rerum porro distinctio sit fugiat molestiae.','resolved','Mollitia officiis ex incidunt quia aspernatur rerum natus.','2025-02-19 19:27:04','2025-04-23 14:50:45'),(9,50,2,3,'Accusamus dicta ducimus quia totam ut ut. Delectus recusandae officiis nesciunt corporis unde possimus culpa. Aut in est alias debitis.','resolved','Et tenetur perferendis voluptatem necessitatibus.','2025-03-21 13:31:39','2025-04-20 04:51:22'),(10,1,2,4,'Soluta distinctio consequuntur voluptas nam. Fuga iste aut ullam est occaecati soluta autem. Itaque nulla nostrum dolorum.','resolved','Voluptates qui inventore assumenda quia.','2025-03-26 21:33:37','2025-02-26 08:44:19'),(11,25,1,4,'Facilis optio similique sequi. Sed id nisi consequatur quia possimus reiciendis aspernatur eum.','rejected','Recusandae qui voluptas eum vel.','2025-04-28 07:14:05','2025-02-21 02:34:54'),(12,22,1,3,'Saepe et repellat quisquam est iste reprehenderit inventore. Eos non cupiditate rerum et eius suscipit. Cumque esse maiores porro corrupti molestiae corrupti molestiae iste.','resolved','Illum sit et fugiat asperiores saepe repellat.','2025-04-18 11:35:25','2025-02-13 06:10:47'),(13,5,2,3,'Omnis iusto voluptatem consequatur quo et distinctio omnis sit. Ab magnam alias inventore vel.','resolved','Nihil eveniet aut error dolorum ratione neque.','2025-02-10 12:37:37','2025-05-02 03:25:57'),(14,41,1,3,'Id iusto soluta quod sint consequatur. Dolor dignissimos deserunt voluptate possimus hic nihil consectetur voluptates.','open',NULL,'2025-05-04 05:33:49','2025-04-08 09:49:00'),(15,26,2,3,'Nisi sit aut ea. Ducimus quasi perferendis quos sed.','open',NULL,'2025-02-18 14:14:53','2025-05-06 01:17:40'),(16,14,2,4,'Velit ratione nam quod sed ut voluptatum qui. Voluptas reiciendis quisquam non tempora est.','rejected','Ipsum eos enim alias rerum qui voluptatem dignissimos.','2025-03-20 11:34:18','2025-03-14 20:58:54'),(17,1,2,4,'Ex consequuntur iusto quidem non cupiditate. Amet odit deserunt debitis quia.','open',NULL,'2025-03-03 03:32:36','2025-04-11 21:53:44'),(18,1,2,4,'Blanditiis molestias quo qui quis illo. Repellendus esse vel libero aspernatur eaque voluptatem qui.','open',NULL,'2025-03-07 07:29:29','2025-02-10 11:47:18'),(19,15,1,3,'Sed perspiciatis vel qui quo aliquid est. Amet voluptatem quo sequi dolorem recusandae ut necessitatibus. Molestiae necessitatibus aut esse et magnam soluta.','rejected','Est natus nam accusamus qui dolores.','2025-04-12 03:04:04','2025-02-17 12:43:18'),(20,10,1,4,'Laudantium dolor sint delectus qui ipsa eligendi. Qui porro quaerat sunt ut voluptas atque.','resolved','Distinctio asperiores minus perferendis odit.','2025-02-13 22:28:28','2025-04-11 19:49:03');
/*!40000 ALTER TABLE `disputes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (20,'2014_10_12_100000_create_password_reset_tokens_table',1),(21,'2019_08_19_000000_create_failed_jobs_table',1),(22,'2019_12_14_000001_create_personal_access_tokens_table',1),(23,'2025_05_06_060635_create_admins_table',1),(24,'2025_05_06_075704_create_users_table',1),(25,'2025_05_06_075709_create_shops_table',1),(26,'2025_05_06_075710_create_products_table',1),(27,'2025_05_06_075712_create_orders_table',1),(28,'2025_05_06_075712_create_product_variants_table',1),(29,'2025_05_06_075713_create_banners_table',1),(30,'2025_05_06_075713_create_disputes_table',1),(31,'2025_05_06_075713_create_order_items_table',1),(32,'2025_05_06_075713_create_payments_table',1),(33,'2025_05_06_075713_create_reports_table',1),(34,'2025_05_06_075713_create_shipping_partners_table',1),(35,'2025_05_06_075713_create_vouchers_table',1),(36,'2025_05_06_075714_create_admin_logs_table',1),(37,'2025_05_06_075714_create_reviews_table',1),(38,'2025_05_06_075714_create_voucher_products_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,28,5,4,115.24,'2025-03-29 06:51:17','2025-03-27 11:36:30'),(2,30,1,5,260.95,'2025-04-25 15:45:51','2025-03-03 10:49:53'),(3,50,3,3,495.73,'2025-03-01 19:59:08','2025-03-07 04:22:14'),(4,23,1,4,146.88,'2025-04-20 12:36:34','2025-03-09 12:08:06'),(5,40,1,4,476.13,'2025-03-19 16:19:48','2025-04-24 23:46:45'),(6,23,5,1,385.24,'2025-02-16 20:17:11','2025-04-17 20:55:02'),(7,4,4,5,232.19,'2025-02-21 12:11:20','2025-02-15 19:39:12'),(8,45,5,2,367.79,'2025-02-24 01:42:57','2025-03-14 19:37:18'),(9,13,5,3,55.22,'2025-05-01 04:20:03','2025-04-22 05:40:59'),(10,29,4,1,196.90,'2025-03-12 05:36:49','2025-03-27 15:19:58'),(11,33,4,3,17.63,'2025-03-30 20:55:03','2025-03-01 02:31:30'),(12,44,1,3,339.88,'2025-03-23 11:31:35','2025-04-24 11:17:41'),(13,4,1,3,466.34,'2025-02-19 17:38:32','2025-02-12 02:04:50'),(14,15,2,3,162.92,'2025-03-03 09:35:24','2025-02-24 08:00:17'),(15,26,4,3,149.12,'2025-03-31 11:09:54','2025-04-06 23:15:34'),(16,13,3,2,454.82,'2025-03-09 19:07:04','2025-04-11 19:00:14'),(17,35,4,4,270.93,'2025-04-07 22:16:09','2025-03-24 11:03:26'),(18,1,5,4,418.45,'2025-03-21 14:56:07','2025-05-03 20:15:37'),(19,15,5,2,453.06,'2025-04-01 15:26:52','2025-04-30 07:19:48'),(20,38,5,1,382.39,'2025-04-10 19:48:43','2025-04-19 11:13:12'),(21,38,1,1,307.63,'2025-03-25 01:15:45','2025-03-06 05:52:08'),(22,50,1,4,419.19,'2025-02-12 13:58:54','2025-04-02 19:31:27'),(23,23,1,4,243.21,'2025-04-04 13:05:28','2025-05-02 02:24:39'),(24,27,1,2,431.03,'2025-03-18 19:19:43','2025-02-11 21:47:09'),(25,36,4,1,431.69,'2025-03-22 13:16:21','2025-04-19 08:07:07'),(26,30,3,3,31.19,'2025-05-01 12:51:05','2025-03-06 09:13:37'),(27,46,2,5,486.97,'2025-04-17 09:17:31','2025-03-28 20:53:16'),(28,26,3,3,68.33,'2025-03-11 10:24:29','2025-03-10 04:37:35'),(29,48,1,4,272.63,'2025-05-07 02:14:19','2025-05-01 05:02:17'),(30,30,2,2,458.90,'2025-02-28 18:54:55','2025-05-06 21:27:48'),(31,14,5,1,279.82,'2025-03-16 00:23:41','2025-02-26 21:48:43'),(32,16,3,3,418.80,'2025-03-11 21:22:06','2025-02-28 20:56:52'),(33,39,4,3,205.13,'2025-03-24 13:00:06','2025-04-13 01:45:46'),(34,11,2,2,382.91,'2025-02-13 15:34:45','2025-03-02 08:35:09'),(35,21,4,5,24.43,'2025-05-06 16:11:53','2025-03-29 13:12:18'),(36,7,3,1,415.19,'2025-03-25 22:09:39','2025-04-20 11:52:05'),(37,32,5,3,181.47,'2025-03-20 14:41:13','2025-02-14 21:19:10'),(38,50,1,4,419.10,'2025-04-21 18:43:45','2025-05-01 04:05:47'),(39,16,4,5,134.16,'2025-05-05 01:04:08','2025-04-02 12:38:04'),(40,4,4,1,425.40,'2025-04-17 10:06:50','2025-02-10 13:55:05'),(41,19,5,5,157.83,'2025-04-19 15:21:50','2025-04-17 17:37:49'),(42,3,5,4,10.62,'2025-03-04 15:57:19','2025-03-03 00:26:53'),(43,6,1,5,419.65,'2025-04-10 22:45:35','2025-02-22 16:52:57'),(44,27,5,5,441.03,'2025-04-26 07:33:47','2025-03-13 01:09:44'),(45,47,1,1,273.17,'2025-02-17 00:03:54','2025-05-03 19:22:26'),(46,32,5,1,6.91,'2025-02-21 04:42:16','2025-03-06 14:46:00'),(47,30,3,2,447.00,'2025-02-19 00:13:04','2025-05-04 22:14:49'),(48,5,2,1,26.64,'2025-02-14 21:37:46','2025-04-24 02:20:49'),(49,35,3,1,496.27,'2025-03-08 22:07:47','2025-04-07 16:11:52'),(50,22,5,4,353.98,'2025-03-14 20:08:58','2025-03-20 07:15:18');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `buyer_id` bigint unsigned NOT NULL,
  `seller_id` bigint unsigned NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `settled_status` enum('unsettled','settled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unsettled',
  `settled_at` timestamp NULL DEFAULT NULL,
  `shipping_status` enum('pending','processing','shipping','delivered','failed','return') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `order_status` enum('pending','paid','canceled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_buyer_id_foreign` (`buyer_id`),
  KEY `orders_seller_id_foreign` (`seller_id`),
  CONSTRAINT `orders_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `orders_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,2,4,219.56,'unsettled',NULL,'pending','paid','2025-05-01 04:33:45','2025-04-25 22:22:08'),(2,1,4,854.73,'unsettled',NULL,'delivered','paid','2025-03-13 23:16:11','2025-04-09 16:37:12'),(3,2,4,702.94,'unsettled',NULL,'shipping','pending','2025-03-05 05:24:10','2025-03-29 23:03:23'),(4,1,3,987.48,'settled','2025-04-24 20:54:44','pending','paid','2025-02-07 06:44:21','2025-04-17 04:40:35'),(5,2,3,531.67,'settled','2025-04-28 16:52:07','return','paid','2025-04-27 02:12:06','2025-03-09 12:44:09'),(6,1,4,818.29,'unsettled',NULL,'processing','pending','2025-04-08 04:38:20','2025-05-03 02:17:32'),(7,2,3,255.54,'unsettled',NULL,'delivered','pending','2025-03-16 03:17:08','2025-02-17 11:16:43'),(8,1,4,322.61,'unsettled',NULL,'processing','canceled','2025-04-14 20:00:41','2025-04-27 10:41:58'),(9,1,4,834.63,'unsettled',NULL,'return','canceled','2025-03-29 12:04:31','2025-04-28 12:45:17'),(10,1,4,943.44,'unsettled',NULL,'return','pending','2025-03-09 17:14:23','2025-04-15 09:41:17'),(11,1,3,619.95,'unsettled',NULL,'delivered','pending','2025-03-03 04:12:42','2025-03-03 21:02:33'),(12,1,4,426.56,'settled','2025-04-29 00:48:09','delivered','paid','2025-03-18 16:32:41','2025-02-10 01:30:40'),(13,1,3,841.54,'unsettled',NULL,'return','paid','2025-03-01 03:57:08','2025-04-30 21:33:13'),(14,2,4,725.76,'unsettled',NULL,'return','canceled','2025-04-19 21:34:39','2025-02-21 11:18:00'),(15,1,3,622.14,'unsettled',NULL,'processing','paid','2025-03-07 10:33:49','2025-03-03 08:59:00'),(16,2,4,470.77,'unsettled',NULL,'delivered','pending','2025-04-29 18:39:05','2025-02-15 09:00:12'),(17,2,3,478.48,'settled','2025-04-25 22:28:15','failed','paid','2025-03-14 15:11:49','2025-02-10 10:32:52'),(18,1,3,262.57,'unsettled',NULL,'return','pending','2025-02-23 15:00:27','2025-04-13 22:44:49'),(19,1,3,405.15,'settled','2025-04-17 11:12:48','return','paid','2025-05-04 03:54:12','2025-02-28 20:14:40'),(20,1,3,35.12,'unsettled',NULL,'pending','canceled','2025-02-17 20:27:13','2025-03-05 00:14:24'),(21,1,4,441.28,'unsettled',NULL,'shipping','pending','2025-04-02 23:53:32','2025-03-01 08:46:50'),(22,1,3,590.60,'unsettled',NULL,'processing','canceled','2025-04-09 03:06:38','2025-03-07 04:18:33'),(23,1,4,346.54,'unsettled',NULL,'return','pending','2025-05-03 15:54:36','2025-03-26 23:31:59'),(24,1,3,414.82,'settled','2025-04-25 08:59:22','return','paid','2025-02-19 06:57:59','2025-03-28 18:26:50'),(25,1,4,85.32,'unsettled',NULL,'processing','pending','2025-03-23 14:38:19','2025-02-27 05:45:53'),(26,2,3,889.74,'unsettled',NULL,'shipping','pending','2025-05-02 13:56:35','2025-03-12 17:28:45'),(27,1,3,149.78,'unsettled',NULL,'processing','canceled','2025-05-05 02:42:03','2025-02-16 09:18:28'),(28,1,3,304.23,'unsettled',NULL,'delivered','paid','2025-03-22 23:04:37','2025-02-19 00:57:23'),(29,2,3,99.05,'unsettled',NULL,'processing','pending','2025-03-09 05:20:56','2025-02-08 23:47:54'),(30,1,3,811.02,'unsettled',NULL,'delivered','pending','2025-03-26 02:10:59','2025-03-19 06:03:41'),(31,2,4,355.00,'unsettled',NULL,'pending','pending','2025-02-25 03:46:37','2025-03-29 08:10:38'),(32,2,4,473.31,'unsettled',NULL,'shipping','pending','2025-04-21 15:35:13','2025-04-26 06:52:25'),(33,1,3,557.57,'settled','2025-04-09 18:35:40','pending','paid','2025-04-19 05:37:41','2025-04-10 08:31:36'),(34,1,3,950.47,'settled','2025-04-21 09:11:06','return','paid','2025-02-14 15:54:16','2025-04-12 20:36:50'),(35,1,3,91.49,'settled','2025-04-11 08:58:33','delivered','paid','2025-02-13 22:46:48','2025-03-13 21:20:38'),(36,2,3,817.61,'unsettled',NULL,'delivered','canceled','2025-03-25 14:42:24','2025-02-15 02:59:07'),(37,2,3,981.07,'unsettled',NULL,'failed','pending','2025-04-05 11:48:00','2025-02-07 22:05:28'),(38,1,3,649.50,'unsettled',NULL,'delivered','pending','2025-02-17 16:02:36','2025-02-28 16:51:46'),(39,2,3,955.82,'unsettled',NULL,'pending','pending','2025-04-14 00:02:42','2025-03-18 20:29:40'),(40,2,4,694.19,'settled','2025-04-27 03:24:46','delivered','paid','2025-02-21 13:49:19','2025-04-06 12:49:33'),(41,1,3,314.10,'unsettled',NULL,'delivered','pending','2025-05-03 06:11:21','2025-02-11 16:15:16'),(42,2,4,324.56,'unsettled',NULL,'processing','canceled','2025-02-23 11:24:55','2025-04-16 18:03:00'),(43,2,3,88.66,'unsettled',NULL,'pending','pending','2025-04-28 17:04:42','2025-02-23 11:35:25'),(44,1,4,634.46,'unsettled',NULL,'processing','canceled','2025-04-12 00:55:57','2025-03-14 14:18:36'),(45,1,4,846.11,'unsettled',NULL,'pending','pending','2025-02-27 06:23:26','2025-03-16 17:25:27'),(46,2,4,65.11,'unsettled',NULL,'return','pending','2025-04-04 21:46:20','2025-03-20 21:23:08'),(47,2,4,175.64,'unsettled',NULL,'delivered','pending','2025-03-14 17:49:47','2025-04-02 04:29:40'),(48,2,3,817.00,'unsettled',NULL,'delivered','canceled','2025-03-16 06:56:43','2025-03-15 22:09:36'),(49,1,3,740.54,'settled','2025-04-21 23:59:03','delivered','paid','2025-02-20 02:42:33','2025-03-23 17:21:43'),(50,2,3,815.88,'unsettled',NULL,'return','pending','2025-04-28 02:48:37','2025-05-05 06:01:21');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('success','failed','refund') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'success',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_order_id_foreign` (`order_id`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,1,219.56,'paypal','success','2025-02-08 22:22:41','2025-04-25 20:24:28'),(2,2,854.73,'credit_card','failed','2025-03-13 20:29:02','2025-03-25 15:11:27'),(3,3,702.94,'cash_on_delivery','refund','2025-04-21 09:07:12','2025-04-29 18:01:35'),(4,4,987.48,'bank_transfer','refund','2025-04-10 17:00:48','2025-04-03 07:18:52'),(5,5,531.67,'bank_transfer','success','2025-03-08 07:20:14','2025-04-22 10:04:53'),(6,6,818.29,'bank_transfer','refund','2025-03-17 14:10:26','2025-02-23 00:13:47'),(7,7,255.54,'bank_transfer','success','2025-03-12 18:02:34','2025-02-22 07:51:19'),(8,8,322.61,'credit_card','failed','2025-04-10 18:00:58','2025-03-28 05:22:26'),(9,9,834.63,'paypal','failed','2025-03-14 15:46:22','2025-03-16 15:14:21'),(10,10,943.44,'credit_card','refund','2025-04-06 10:43:32','2025-04-01 18:42:36'),(11,11,619.95,'bank_transfer','success','2025-03-14 09:14:19','2025-03-12 18:24:56'),(12,12,426.56,'bank_transfer','success','2025-03-05 13:41:09','2025-02-24 05:56:53'),(13,13,841.54,'credit_card','failed','2025-03-12 17:15:30','2025-04-19 00:00:17'),(14,14,725.76,'paypal','refund','2025-04-17 23:33:03','2025-05-06 21:18:20'),(15,15,622.14,'bank_transfer','failed','2025-02-18 19:20:56','2025-05-03 14:24:30'),(16,16,470.77,'paypal','success','2025-04-02 20:02:38','2025-03-08 20:18:04'),(17,17,478.48,'paypal','success','2025-03-25 09:55:13','2025-03-19 03:46:59'),(18,18,262.57,'cash_on_delivery','failed','2025-04-14 09:02:13','2025-04-10 09:56:25'),(19,19,405.15,'cash_on_delivery','success','2025-04-03 23:20:54','2025-04-07 16:04:15'),(20,20,35.12,'bank_transfer','success','2025-04-13 15:16:08','2025-04-05 02:21:59'),(21,21,441.28,'credit_card','success','2025-04-17 18:29:46','2025-02-19 12:09:26'),(22,22,590.60,'cash_on_delivery','refund','2025-02-13 21:11:03','2025-04-15 13:15:03'),(23,23,346.54,'credit_card','failed','2025-04-18 11:25:48','2025-03-26 03:03:26'),(24,24,414.82,'bank_transfer','failed','2025-02-15 19:46:55','2025-03-09 15:00:09'),(25,25,85.32,'credit_card','refund','2025-03-07 05:14:12','2025-04-29 13:30:50'),(26,26,889.74,'paypal','success','2025-03-07 14:22:13','2025-04-16 01:45:27'),(27,27,149.78,'paypal','success','2025-03-27 04:52:56','2025-03-29 17:37:12'),(28,28,304.23,'cash_on_delivery','success','2025-04-15 22:28:15','2025-05-04 00:08:51'),(29,29,99.05,'credit_card','refund','2025-04-10 08:45:26','2025-03-08 14:07:46'),(30,30,811.02,'bank_transfer','failed','2025-04-10 19:37:17','2025-03-18 18:13:59'),(31,31,355.00,'cash_on_delivery','refund','2025-04-23 08:54:12','2025-03-04 02:06:58'),(32,32,473.31,'paypal','success','2025-02-16 06:16:23','2025-03-17 16:11:39'),(33,33,557.57,'bank_transfer','refund','2025-04-09 07:22:47','2025-02-16 19:56:28'),(34,34,950.47,'cash_on_delivery','refund','2025-02-09 02:50:02','2025-03-13 06:49:47'),(35,35,91.49,'credit_card','refund','2025-03-06 15:23:07','2025-04-20 21:05:15'),(36,36,817.61,'bank_transfer','failed','2025-02-08 22:46:17','2025-02-16 13:30:15'),(37,37,981.07,'paypal','success','2025-04-20 16:33:58','2025-03-09 12:14:20'),(38,38,649.50,'cash_on_delivery','success','2025-04-12 11:19:38','2025-04-12 13:40:18'),(39,39,955.82,'paypal','refund','2025-05-01 07:26:27','2025-04-30 01:53:47'),(40,40,694.19,'credit_card','refund','2025-05-03 21:37:40','2025-03-17 08:12:38'),(41,41,314.10,'paypal','success','2025-03-28 07:08:35','2025-03-21 05:53:26'),(42,42,324.56,'cash_on_delivery','refund','2025-04-16 05:10:19','2025-04-12 14:38:06'),(43,43,88.66,'cash_on_delivery','refund','2025-05-03 17:38:47','2025-04-26 10:12:19'),(44,44,634.46,'paypal','refund','2025-02-26 08:06:04','2025-04-27 00:35:55'),(45,45,846.11,'credit_card','success','2025-03-02 03:16:14','2025-04-22 21:36:26'),(46,46,65.11,'bank_transfer','failed','2025-03-28 01:24:18','2025-03-29 06:35:35'),(47,47,175.64,'bank_transfer','refund','2025-04-06 22:52:51','2025-03-11 16:56:20'),(48,48,817.00,'paypal','failed','2025-02-27 16:32:44','2025-04-28 16:09:55'),(49,49,740.54,'credit_card','success','2025-02-23 00:51:46','2025-02-24 20:53:54'),(50,50,815.88,'paypal','failed','2025-04-06 20:39:01','2025-03-22 10:24:36');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\Admin',1,'admin-token','b478c7289856cd20e5ca134d5debcd3b68674b32f2fc0b089df225e8b51741e4','[\"*\"]',NULL,NULL,'2025-05-08 02:32:00','2025-05-08 02:32:00'),(3,'App\\Models\\Admin',2,'admin-token','b0db28e5181e24b2982474b7478387955b010d03d83bb9e2500d17db8a5a8fbf','[\"*\"]',NULL,NULL,'2025-05-08 02:34:34','2025-05-08 02:34:34'),(4,'App\\Models\\Admin',1,'admin-token','4fbc07e7efd8f013626573ac3c3b6285ea793d9768520d43ff7f0c65ac54da52','[\"*\"]',NULL,NULL,'2025-05-08 03:00:46','2025-05-08 03:00:46'),(7,'App\\Models\\User',3,'user-token','7d5f14d2b0f66d5dafe9a8a929c6b73fa2f4a565322728a59ac85f55fa48a0b8','[\"role:seller\"]',NULL,NULL,'2025-05-08 03:30:39','2025-05-08 03:30:39'),(8,'App\\Models\\User',3,'user-token','8d8e8aade68dbfd882f32c8f3400c25d5d92f3fa43e45e6b69e8781f8bce8917','[\"role:seller\"]',NULL,NULL,'2025-05-08 03:31:10','2025-05-08 03:31:10'),(9,'App\\Models\\User',3,'user-token','c72f4c3b9ee47d263e4be6f66a2341517eb821fe6131e11a1e6204034057b535','[\"role:seller\"]',NULL,NULL,'2025-05-08 03:38:27','2025-05-08 03:38:27'),(13,'App\\Models\\Admin',1,'admin-token','b9ec9ff834e931fdda627e4d2a2e2008b54135fbf30830412a4dec7dcc029e57','[\"role:admin\"]',NULL,NULL,'2025-05-08 03:40:01','2025-05-08 03:40:01'),(14,'App\\Models\\Admin',4,'admin-token','e6013e059593ec4a4e88cf33a04fe4a3b642fa15178d7ee1d6e743533045c283','[\"role:admin\"]',NULL,NULL,'2025-05-08 04:17:05','2025-05-08 04:17:05'),(15,'App\\Models\\Admin',3,'admin-token','28fda744d44affd4d9ca0fa8b87fca6426e793a3df04b4c3d0700de3aa9f0ac5','[\"role:admin\"]',NULL,NULL,'2025-05-08 04:18:50','2025-05-08 04:18:50'),(16,'App\\Models\\Admin',1,'admin-token','d9e6ba511ade09898edd4cec1dc8c7f13ef14ad64984e1054ce835b4d4de79b2','[\"role:admin\"]',NULL,NULL,'2025-05-08 04:47:24','2025-05-08 04:47:24');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variants_sku_unique` (`sku`),
  KEY `product_variants_product_id_foreign` (`product_id`),
  CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
INSERT INTO `product_variants` VALUES (1,1,'Red','L','Z7NB3Z2N',895850.00,74,'https://example.com/variant_1_0.jpg','active','2025-05-07 04:01:57','2025-05-07 04:01:57'),(2,2,'Red','M','CAM1YIRK',203143.00,5,'https://example.com/variant_2_0.jpg','active','2025-05-07 04:01:57','2025-05-07 04:01:57'),(3,2,'Yellow','S','E4UQWPGT',577377.00,2,'https://example.com/variant_2_1.jpg','active','2025-05-07 04:01:57','2025-05-07 04:01:57'),(4,5,'Yellow','S','K7Z83UPF',934843.00,2,'https://example.com/variant_5_0.jpg','active','2025-05-07 04:01:57','2025-05-07 04:01:57'),(5,3,'Blue','L','JWTDMURS',200382.00,81,'https://example.com/variant_3_0.jpg','active','2025-05-07 04:01:57','2025-05-07 04:01:57'),(6,3,'Black','XL','VBS5YZJN',341222.00,59,'https://example.com/variant_3_1.jpg','active','2025-05-07 04:01:57','2025-05-07 04:01:57'),(7,3,'Black','L','YBBWQQGM',722317.00,43,'https://example.com/variant_3_2.jpg','active','2025-05-07 04:01:57','2025-05-07 04:01:57'),(8,4,'Green','S','TOR7UGKE',819211.00,88,'https://example.com/variant_4_0.jpg','active','2025-05-07 04:01:57','2025-05-07 04:01:57'),(9,4,'Black','L','J9WCVJSC',115652.00,75,'https://example.com/variant_4_1.jpg','active','2025-05-07 04:01:57','2025-05-07 04:01:57');
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `status` enum('pending','approved','banned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_shop_id_foreign` (`shop_id`),
  CONSTRAINT `products_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Áo thun basic','Áo thun cotton 100% thoáng mát',150000.00,100,'approved','2025-05-07 04:01:57','2025-05-07 04:01:57'),(2,1,'Quần jean ống đứng','Quần jean chất liệu cao cấp, bền đẹp',350000.00,50,'approved','2025-05-07 04:01:57','2025-05-07 04:01:57'),(3,2,'Váy hoa xòe','Váy hoa xinh xắn, phù hợp đi chơi, dạo phố',250000.00,75,'approved','2025-05-07 04:01:57','2025-05-07 04:01:57'),(4,2,'Giày sneaker thể thao','Giày sneaker năng động, êm ái',400000.00,30,'approved','2025-05-07 04:01:57','2025-05-07 04:01:57'),(5,1,'Mũ lưỡi trai','Mũ lưỡi trai thời trang, nhiều màu sắc',80000.00,120,'pending','2025-05-07 04:01:57','2025-05-07 04:01:57');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `report_type` enum('daily','week','month') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
INSERT INTO `reports` VALUES (1,'week','http://www.schoen.net/quae-ut-facilis-illo/reports/week_e39312cd-2c39-3ae8-950d-07e0b61ae7a2.pdf','2024-12-05 07:13:13','2025-04-13 17:47:48'),(2,'daily','http://www.collier.com/dolor-mollitia-ipsum-quos-deleniti/reports/daily_8b86cfdf-d440-3513-a268-30334f3c1eb0.pdf','2025-02-28 04:01:01','2024-12-05 16:17:42'),(3,'month','http://www.stracke.biz/quasi-illo-voluptate-aut-est-rerum-quidem.html/reports/month_5ac2163d-40bb-3c4f-8c83-4c4d601a311e.pdf','2025-02-04 05:05:22','2025-03-24 17:31:52'),(4,'daily','http://osinski.info/vel-facere-ut-non-eaque-quas-est-aut-laboriosam/reports/daily_53d51169-3a85-30e9-8f4e-d1879a0d58ec.pdf','2025-01-24 06:59:08','2025-01-30 12:54:44'),(5,'month','https://www.nienow.com/at-iure-ipsa-iure-delectus-accusantium-possimus/reports/month_28798deb-aa45-3bf9-a1c0-ba3faa327905.pdf','2024-12-19 03:06:24','2025-01-11 14:05:15'),(6,'month','http://borer.biz//reports/month_914b6759-a15c-3c3a-8f21-0e4d38a26cf9.pdf','2024-11-30 23:47:57','2025-04-18 05:34:24'),(7,'month','https://www.weber.com/qui-eveniet-voluptatibus-ipsa-voluptas-error/reports/month_5b72810c-5a0d-3562-b232-5a3b0055e5de.pdf','2025-02-01 16:04:24','2025-05-03 21:01:47'),(8,'daily','https://rath.org/maiores-suscipit-consequatur-aliquid-quod-sit-labore.html/reports/daily_43bc0190-e109-3b02-b8d2-16971e158883.pdf','2024-12-11 06:27:57','2025-01-31 02:10:04'),(9,'month','http://lemke.com/soluta-vitae-consequuntur-omnis-qui-officiis/reports/month_7198753d-9aa3-320c-86c3-231eaac3e6fe.pdf','2025-01-31 19:56:44','2025-04-12 15:12:51'),(10,'daily','http://www.ankunding.com/dignissimos-dolor-ut-sed-corrupti/reports/daily_c5064f2b-e310-3663-ade2-b495b5993ccc.pdf','2025-03-02 14:28:04','2024-11-14 15:21:05'),(11,'week','http://www.kerluke.info/et-molestiae-eum-nihil-velit-eveniet/reports/week_eb65cc2e-f1d1-3091-af65-fa932cb10dfa.pdf','2025-01-26 16:35:15','2025-03-09 06:28:24'),(12,'week','http://hammes.com/non-assumenda-rerum-dolore-tempora-sequi/reports/week_29647b86-78d7-33fb-a08e-d2d4eef2f1ab.pdf','2025-01-04 03:08:44','2025-01-06 13:57:08'),(13,'month','http://witting.org/sunt-id-magni-labore-dolorem-facilis-et/reports/month_579fd565-9430-30f9-99e7-93599f9e87e5.pdf','2024-12-03 23:57:03','2025-04-10 09:06:44'),(14,'week','http://www.kris.net/et-pariatur-sit-quam-placeat/reports/week_921e4b69-09d8-33f4-aabc-eeb9d20372e3.pdf','2025-01-08 02:32:28','2025-03-02 16:49:24'),(15,'week','http://www.walker.com/sapiente-laboriosam-praesentium-harum-iure-tempore-quam/reports/week_b2a6488a-5f7b-35fc-81dc-ee89147b1b29.pdf','2025-01-05 16:29:31','2024-11-22 15:28:32');
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `buyer_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `rating` tinyint unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `images` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_order_id_foreign` (`order_id`),
  KEY `reviews_buyer_id_foreign` (`buyer_id`),
  KEY `reviews_product_id_foreign` (`product_id`),
  CONSTRAINT `reviews_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,30,1,1,4,NULL,NULL,'2025-04-20 00:06:05','2025-03-06 19:26:32'),(2,15,1,5,5,NULL,NULL,'2025-04-13 11:55:48','2025-04-22 01:17:06'),(3,14,2,4,2,'Dolorem sed nihil corporis. Alias sed dolorem tempora incidunt cupiditate possimus laboriosam exercitationem. Et corporis et laudantium cupiditate. Repellat sunt sunt cum nemo eos ad.',NULL,'2025-03-05 15:01:47','2025-03-31 12:36:20'),(4,21,1,4,2,NULL,NULL,'2025-02-22 02:37:24','2025-04-25 12:29:07'),(5,16,2,5,3,'Dolorum quia amet asperiores nihil. Consectetur commodi ut veniam nam et et fugit. Omnis aut dolorem et non voluptatem. Nesciunt veniam alias perspiciatis id.',NULL,'2025-02-26 19:41:48','2025-03-20 02:48:45'),(6,23,1,3,1,NULL,'[\"https://via.placeholder.com/640x480.png/00aa22?text=reiciendis\", \"https://via.placeholder.com/640x480.png/004400?text=ut\"]','2025-04-16 12:13:43','2025-05-01 12:23:31'),(7,30,1,5,2,NULL,NULL,'2025-03-29 09:56:20','2025-04-25 18:38:11'),(8,46,2,2,2,'Doloremque et fugit nobis voluptas sed hic veniam. Corrupti totam quos ipsa porro. Ut ipsam doloribus cumque labore soluta. Harum laborum dolorem ipsa cumque fugiat.',NULL,'2025-04-04 20:55:22','2025-04-29 20:41:36'),(9,34,1,4,2,'Ut voluptatem asperiores totam ab et hic. Voluptas accusamus impedit eligendi quo dicta. Et repellat enim quod quisquam qui.',NULL,'2025-02-15 23:10:19','2025-02-13 04:57:12'),(10,29,2,5,1,'Unde asperiores ea voluptas explicabo et sunt magni. Aliquam voluptates doloribus natus ut. Labore nobis qui delectus odit. Ratione saepe possimus nihil assumenda amet aut quia.',NULL,'2025-02-25 21:33:56','2025-03-09 21:44:55'),(11,15,1,5,4,'Debitis quibusdam nihil cupiditate. Asperiores blanditiis facere enim eum quidem. Occaecati deserunt sapiente eius consequuntur. Sed ut nihil perferendis illum.',NULL,'2025-04-20 14:44:18','2025-04-15 00:44:28'),(12,25,1,3,1,NULL,NULL,'2025-03-16 14:50:17','2025-04-13 07:12:34'),(13,20,1,2,4,NULL,NULL,'2025-04-18 10:50:52','2025-02-28 04:04:11'),(14,48,2,2,3,NULL,NULL,'2025-04-17 22:50:11','2025-02-18 23:33:30'),(15,44,1,3,3,NULL,'[\"https://via.placeholder.com/640x480.png/00cc11?text=minus\", \"https://via.placeholder.com/640x480.png/004400?text=sint\"]','2025-03-19 01:11:44','2025-05-06 19:15:17'),(16,45,1,3,3,NULL,'[\"https://via.placeholder.com/640x480.png/0033ee?text=vel\", \"https://via.placeholder.com/640x480.png/004477?text=fuga\"]','2025-02-08 07:55:57','2025-04-14 20:32:24'),(17,40,2,3,4,'Amet architecto atque voluptatem nam voluptatem excepturi est. Qui sit et nobis dolorem voluptatem quam totam. Quisquam reiciendis eligendi culpa. Harum unde ut aut.','[\"https://via.placeholder.com/640x480.png/007711?text=delectus\", \"https://via.placeholder.com/640x480.png/002211?text=eos\"]','2025-03-28 13:50:35','2025-03-06 15:49:15'),(18,26,2,1,1,'Similique itaque eos est. Quos et tempora accusamus deleniti recusandae rerum eum id. Est reprehenderit necessitatibus blanditiis ut.','[\"https://via.placeholder.com/640x480.png/00aaee?text=impedit\", \"https://via.placeholder.com/640x480.png/00aa99?text=quo\"]','2025-04-16 22:05:26','2025-04-19 12:53:13'),(19,15,1,5,5,'Voluptatibus omnis debitis laudantium cumque aut quaerat aut. Et est sint assumenda qui. Quibusdam asperiores beatae repudiandae exercitationem.','[\"https://via.placeholder.com/640x480.png/0066bb?text=laboriosam\", \"https://via.placeholder.com/640x480.png/0011bb?text=dolor\"]','2025-03-26 04:08:52','2025-04-17 00:27:22'),(20,1,2,3,5,NULL,'[\"https://via.placeholder.com/640x480.png/00bb66?text=aut\", \"https://via.placeholder.com/640x480.png/0000bb?text=deleniti\"]','2025-03-10 23:56:44','2025-03-29 20:11:54'),(21,48,2,1,4,'Voluptatem est modi unde in temporibus. Est debitis aut omnis nam quae esse. Ea perferendis illum perferendis. Dicta dolor accusantium voluptatem tempore est libero recusandae.',NULL,'2025-04-10 12:16:42','2025-05-04 05:03:15'),(22,36,2,3,1,NULL,NULL,'2025-02-20 10:46:32','2025-04-13 11:12:55'),(23,42,2,4,3,'Excepturi qui placeat sequi labore. Magnam cumque quas quis porro veniam aut porro. Eligendi quibusdam similique placeat sit distinctio consequatur omnis.',NULL,'2025-04-17 08:56:25','2025-03-27 03:07:15'),(24,44,1,5,3,'Asperiores nisi quia dolorem minus tenetur. Possimus ratione nostrum explicabo at ducimus repellendus itaque.',NULL,'2025-04-16 11:37:44','2025-02-12 00:40:21'),(25,4,1,1,4,'Dignissimos officiis ab nam aut aspernatur sunt sint. Et et sequi quas deserunt sint. Exercitationem numquam doloremque a.','[\"https://via.placeholder.com/640x480.png/00dd55?text=quisquam\", \"https://via.placeholder.com/640x480.png/0088bb?text=laborum\"]','2025-04-08 01:35:00','2025-04-05 10:03:35'),(26,19,1,4,3,NULL,'[\"https://via.placeholder.com/640x480.png/0044dd?text=similique\", \"https://via.placeholder.com/640x480.png/001155?text=saepe\"]','2025-04-30 21:46:40','2025-03-15 23:14:24'),(27,50,2,1,5,'Nisi molestiae voluptas vitae mollitia laboriosam mollitia. Maxime possimus asperiores minus laborum corrupti. Et laborum nisi earum odio et. Et ut reiciendis molestiae dolores et.',NULL,'2025-02-09 21:35:50','2025-04-04 01:28:17'),(28,18,1,2,2,'Molestiae excepturi vel est delectus totam iusto ut dolores. Neque autem corporis velit et aut aspernatur. Voluptas voluptatem rem sunt odio occaecati earum. Magni debitis sit voluptatibus sit sint ullam. Ut sequi ipsa sunt autem molestias impedit necessitatibus.','[\"https://via.placeholder.com/640x480.png/00dd33?text=minima\", \"https://via.placeholder.com/640x480.png/006600?text=soluta\"]','2025-04-28 04:24:58','2025-03-20 12:47:28'),(29,3,2,2,5,NULL,NULL,'2025-02-21 17:12:52','2025-02-22 04:36:58'),(30,48,2,1,3,'Ducimus magnam error doloremque autem amet. Dolores ratione odit earum veniam nam.',NULL,'2025-04-01 08:23:41','2025-04-20 03:41:55');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_partners`
--

DROP TABLE IF EXISTS `shipping_partners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipping_partners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_partners`
--

LOCK TABLES `shipping_partners` WRITE;
/*!40000 ALTER TABLE `shipping_partners` DISABLE KEYS */;
INSERT INTO `shipping_partners` VALUES (1,'Giao hàng nhanh','https://ghn.vn/api','active','2025-05-07 04:01:57','2025-05-07 04:01:57'),(2,'Viettel Post','https://viettelpost.vn/api','active','2025-05-07 04:01:57','2025-05-07 04:01:57'),(3,'Ninja Van','https://ninjavan.vn/api','inactive','2025-05-07 04:01:57','2025-05-07 04:01:57'),(4,'J&T Express','https://jtexpress.vn/api','active','2025-05-07 04:01:57','2025-05-07 04:01:57'),(5,'VNPost Express','https://vnpost.vn/api','inactive','2025-05-07 04:01:57','2025-05-07 04:01:57');
/*!40000 ALTER TABLE `shipping_partners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shops`
--

DROP TABLE IF EXISTS `shops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shops` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` bigint unsigned NOT NULL,
  `shop_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','active','banned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `enabled_shipping_partners` json DEFAULT NULL,
  `avatar_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shops_owner_id_foreign` (`owner_id`),
  CONSTRAINT `shops_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shops`
--

LOCK TABLES `shops` WRITE;
/*!40000 ALTER TABLE `shops` DISABLE KEYS */;
INSERT INTO `shops` VALUES (1,3,'Cửa hàng A','active','[1, 2]','https://example.com/avatar_shop_a.jpg','https://example.com/cover_shop_a.jpg','2025-05-07 04:01:57','2025-05-07 04:01:57'),(2,4,'Thế giới B','active','[2, 3]','https://example.com/avatar_shop_b.jpg','https://example.com/cover_shop_b.jpg','2025-05-07 04:01:57','2025-05-07 04:01:57');
/*!40000 ALTER TABLE `shops` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('buyer','seller') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','banned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'buyer1@example.com','$2y$12$ipwBhekZ6nqb3h56KA1MMuYzQoIUKF7UAhCjUQLGwXaCLk3CqeXge','buyer','active','2025-05-07 04:01:56','2025-05-07 04:01:56'),(2,'buyer2@example.com','$2y$12$I6sLrmMioAB/HRmCSqZ/..7PVirX97bJeorGNUh5eTYJSfoZRHBXW','buyer','active','2025-05-07 04:01:56','2025-05-07 04:01:56'),(3,'seller1@example.com','$2y$12$EVzfJzsxyhaC9EUsMsSbW.PFSBoy1Wd/nER45Ir.Z4pIOXqgGX.26','seller','active','2025-05-07 04:01:56','2025-05-07 04:01:56'),(4,'seller2@example.com','$2y$12$i.ZuxPX0WWKS2bU/NgGuIOjomaXOFXx9FErGUO0oWLs0ljnqaJSbq','seller','banned','2025-05-07 04:01:57','2025-05-07 04:01:57');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voucher_products`
--

DROP TABLE IF EXISTS `voucher_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `voucher_products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `voucher_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `voucher_products_voucher_id_foreign` (`voucher_id`),
  KEY `voucher_products_product_id_foreign` (`product_id`),
  CONSTRAINT `voucher_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `voucher_products_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voucher_products`
--

LOCK TABLES `voucher_products` WRITE;
/*!40000 ALTER TABLE `voucher_products` DISABLE KEYS */;
INSERT INTO `voucher_products` VALUES (1,13,1,'2025-02-23 07:49:57','2025-03-17 13:48:49'),(2,10,5,'2025-04-09 21:21:25','2025-04-01 05:57:23'),(3,13,5,'2025-03-08 03:38:32','2025-03-26 13:49:38'),(4,13,3,'2025-02-28 03:28:14','2025-04-20 11:59:15'),(5,7,2,'2025-05-06 10:39:05','2025-03-11 15:44:02'),(6,10,3,'2025-04-05 17:01:29','2025-04-13 19:48:31'),(7,10,1,'2025-04-06 11:25:30','2025-04-06 21:44:39'),(8,13,4,'2025-04-21 08:31:32','2025-03-29 02:48:07'),(9,13,2,'2025-03-16 04:27:28','2025-04-21 21:48:54'),(10,7,2,'2025-02-21 11:45:17','2025-02-10 08:19:13'),(11,10,4,'2025-02-25 03:37:21','2025-03-24 10:53:52'),(12,10,5,'2025-04-08 05:28:08','2025-04-03 11:45:39'),(13,7,1,'2025-02-10 05:18:23','2025-04-23 04:17:17'),(14,10,5,'2025-04-02 16:20:00','2025-03-28 18:03:48'),(15,13,1,'2025-03-28 14:02:50','2025-04-26 08:15:23'),(16,10,2,'2025-04-25 22:32:27','2025-03-28 11:47:09'),(17,10,5,'2025-03-02 10:58:15','2025-02-24 04:56:05'),(18,13,2,'2025-03-13 15:14:19','2025-04-27 21:03:41'),(19,10,4,'2025-04-25 16:56:09','2025-03-01 05:42:52'),(20,10,5,'2025-02-22 03:40:34','2025-02-18 09:53:03');
/*!40000 ALTER TABLE `voucher_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vouchers`
--

DROP TABLE IF EXISTS `vouchers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vouchers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_type` enum('percentage','fixed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `usage_limited` int DEFAULT NULL,
  `used_count` int NOT NULL DEFAULT '0',
  `voucher_type` enum('platform','shop','shipping','product') COLLATE utf8mb4_unicode_ci NOT NULL,
  `shop_id` bigint unsigned DEFAULT NULL,
  `shipping_only` tinyint(1) NOT NULL DEFAULT '0',
  `applicable_shipping_partners` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vouchers_code_unique` (`code`),
  KEY `vouchers_shop_id_foreign` (`shop_id`),
  CONSTRAINT `vouchers_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vouchers`
--

LOCK TABLES `vouchers` WRITE;
/*!40000 ALTER TABLE `vouchers` DISABLE KEYS */;
INSERT INTO `vouchers` VALUES (1,'VOUCHER_ZEAE','fixed',10.05,159.20,'2025-04-27','2025-05-15',301,25,'shop',2,0,NULL,'2025-03-16 13:54:40','2025-05-04 00:21:25'),(2,'VOUCHER_HPPP','fixed',41.58,160.82,'2025-04-09','2025-05-18',214,2,'shop',2,0,NULL,'2025-02-21 19:45:51','2025-03-12 00:11:06'),(3,'VOUCHER_TPIB','percentage',31.00,173.34,'2025-05-06','2025-05-13',NULL,22,'platform',NULL,0,NULL,'2025-04-16 06:15:38','2025-04-23 08:49:58'),(4,'VOUCHER_XTAW','fixed',84.18,90.40,'2025-05-02','2025-05-11',NULL,42,'platform',NULL,0,NULL,'2025-03-05 03:33:07','2025-04-29 13:27:23'),(5,'VOUCHER_HACZ','fixed',9.09,60.10,'2025-04-08','2025-05-22',NULL,32,'platform',NULL,0,NULL,'2025-02-09 04:38:54','2025-03-19 13:18:31'),(6,'VOUCHER_FIZM','percentage',18.00,169.44,'2025-04-12','2025-06-04',NULL,8,'platform',NULL,0,NULL,'2025-03-06 18:03:50','2025-02-14 12:55:15'),(7,'VOUCHER_WBPQ','fixed',16.79,97.28,'2025-04-25','2025-05-31',458,21,'product',NULL,0,NULL,'2025-03-11 17:44:25','2025-02-15 07:36:39'),(8,'VOUCHER_RXMD','fixed',80.75,85.80,'2025-04-29','2025-05-26',392,12,'shop',2,0,NULL,'2025-05-02 04:24:14','2025-03-14 04:11:36'),(9,'VOUCHER_JENT','percentage',10.00,96.60,'2025-04-17','2025-05-22',258,8,'shipping',NULL,1,'[\"partner1\", \"partner3\"]','2025-04-28 17:18:56','2025-03-05 14:27:01'),(10,'VOUCHER_JCNG','fixed',94.48,74.26,'2025-04-07','2025-05-11',309,3,'product',NULL,0,NULL,'2025-05-02 09:51:08','2025-04-23 02:48:03'),(11,'VOUCHER_HWFK','fixed',80.66,184.46,'2025-04-20','2025-06-01',312,50,'shop',2,0,NULL,'2025-03-23 01:26:58','2025-04-20 01:16:29'),(12,'VOUCHER_YCQO','fixed',74.87,125.43,'2025-04-23','2025-05-22',173,40,'shipping',NULL,1,'[\"partner2\", \"partner3\"]','2025-03-02 09:34:18','2025-02-10 03:00:44'),(13,'VOUCHER_KEXC','percentage',38.00,27.65,'2025-04-16','2025-05-07',449,11,'product',NULL,0,NULL,'2025-02-08 02:57:34','2025-04-09 09:53:00'),(14,'VOUCHER_MTWL','percentage',18.00,135.08,'2025-05-05','2025-05-19',472,28,'shop',1,0,NULL,'2025-02-24 15:47:37','2025-02-23 13:03:42'),(15,'VOUCHER_ZSHJ','percentage',41.00,185.32,'2025-04-13','2025-05-23',194,37,'shipping',NULL,1,'[\"partner3\", \"partner1\"]','2025-04-08 17:39:25','2025-04-05 03:58:02');
/*!40000 ALTER TABLE `vouchers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-08 16:58:23
