-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: kairos
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

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
-- Table structure for table `client_category`
--

DROP TABLE IF EXISTS `client_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_category` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_category`
--

LOCK TABLES `client_category` WRITE;
/*!40000 ALTER TABLE `client_category` DISABLE KEYS */;
INSERT INTO `client_category` VALUES (1,'Healthcare');
/*!40000 ALTER TABLE `client_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `client_subcategory`
--

DROP TABLE IF EXISTS `client_subcategory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_subcategory` (
  `subcategory_id` int NOT NULL AUTO_INCREMENT,
  `subcategory` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`subcategory_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_subcategory`
--

LOCK TABLES `client_subcategory` WRITE;
/*!40000 ALTER TABLE `client_subcategory` DISABLE KEYS */;
INSERT INTO `client_subcategory` VALUES (1,'Hospitals');
/*!40000 ALTER TABLE `client_subcategory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `groups` (
  `group_id` int NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'Admin Group');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grp_role_usr_mapping`
--

DROP TABLE IF EXISTS `grp_role_usr_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grp_role_usr_mapping` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `group_id` int DEFAULT NULL,
  `role_id` int DEFAULT NULL,
  `grp_usr_mapping_id` int DEFAULT NULL,
  `is_active` int DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grp_role_usr_mapping`
--

LOCK TABLES `grp_role_usr_mapping` WRITE;
/*!40000 ALTER TABLE `grp_role_usr_mapping` DISABLE KEYS */;
INSERT INTO `grp_role_usr_mapping` VALUES (1,1,1,1,1,1);
/*!40000 ALTER TABLE `grp_role_usr_mapping` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `looker`
--

DROP TABLE IF EXISTS `looker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `looker` (
  `id` int NOT NULL AUTO_INCREMENT,
  `host` varchar(255) DEFAULT NULL,
  `client_id` varchar(255) DEFAULT NULL,
  `client_secret` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `looker`
--

LOCK TABLES `looker` WRITE;
/*!40000 ALTER TABLE `looker` DISABLE KEYS */;
INSERT INTO `looker` VALUES (1,'lookerstudio.google.com','191041767818-smivnrc2tdjjm946btfjfj0gqk6g80cl.apps.googleusercontent.com','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',NULL,NULL);
/*!40000 ALTER TABLE `looker` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `looker_data`
--

DROP TABLE IF EXISTS `looker_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `looker_data` (
  `tbl_id` int NOT NULL AUTO_INCREMENT,
  `client_primary_id` int DEFAULT NULL,
  `client_id` int DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `folder_id` int DEFAULT NULL,
  `folder_name` varchar(255) DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `subcategory_id` int DEFAULT NULL,
  `dash_id` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`tbl_id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `looker_data`
--

LOCK TABLES `looker_data` WRITE;
/*!40000 ALTER TABLE `looker_data` DISABLE KEYS */;
INSERT INTO `looker_data` VALUES (1,1,1,'Demo',1,'Care Coordination (Evidence-Based Rules)',1,1,'f141a4d4-4ec4-4334-b4b3-4fc8257595a2/page/BwhrF','Additional Risk Factors (Member-Level Analysis)',NULL,NULL),(2,1,1,'Demo',1,'Care Coordination (Evidence-Based Rules)',1,1,'3e897e25-9aa2-43f3-ad7e-72efab31be26/page/6igsF','Cancer - Preventive Screening Compliance',NULL,NULL),(3,1,1,'Demo',1,'Care Coordination (Evidence-Based Rules)',1,1,'23ffd42b-3f34-4da9-a440-b6b44f243baa/page/wa6sF','Diabetes - Evidence-Based Rules Compliance',NULL,NULL),(4,1,1,'Demo',1,'Care Coordination (Evidence-Based Rules)',1,1,'5ad3029b-4cc1-49b4-b455-76632543b112/page/uKjrF','Evidence-Based Rules Compliance (Member-Level Analysis)',NULL,NULL),(5,1,1,'Demo',1,'Care Coordination (Evidence-Based Rules)',1,1,'23558699-9a01-4645-b048-4f7a5815ecf0/page/6uZrF','Evidence-Based Rules Compliance Summary',NULL,NULL),(6,1,1,'Demo',1,'Care Coordination (Evidence-Based Rules)',1,1,'0fb3cdff-6fee-4baf-a3fa-8178b009ad09/page/LlcrF','Overall Compliance to Evidence-Based Rules - Percentage',NULL,NULL),(7,1,1,'Demo',1,'Care Coordination (Evidence-Based Rules)',1,1,'b7d03c29-c802-4cb8-b615-d81ab34f4123/page/8rYrF','Preventive Screening Compliance (Member-Level Analysis)',NULL,NULL),(8,1,1,'Demo',2,'Chronic Condition Reports',1,1,'cd86b02c-55a4-44d1-a635-804848a969ed/page/qmSsF','Diabetes - Demographic & Economic Insights',NULL,NULL),(9,1,1,'Demo',2,'Chronic Condition Reports',1,1,'bb19f877-be8d-46d5-8f0a-9972afe8151f/page/ALxqF','Diabetes - Medical & Pharmacy Claims Summary',NULL,NULL),(10,1,1,'Demo',2,'Chronic Condition Reports',1,1,'af862975-cca2-4457-8769-23570d13f40f/page/GdgrF','Heart Disease - Demographic & Economic Insights',NULL,NULL),(11,1,1,'Demo',2,'Chronic Condition Reports',1,1,'56378c09-07b3-42fb-b77b-212faeaf0236/page/GdgrF','Heart Disease - Medical & Pharmacy Claims Summary',NULL,NULL),(12,1,1,'Demo',2,'Chronic Condition Reports',1,1,'48eb81f4-b1ef-4053-b160-2e3e7e443d03/page/dAyrF','Hypertension - Demographic & Economic Insights',NULL,NULL),(13,1,1,'Demo',2,'Chronic Condition Reports',1,1,'c5c9ece6-34af-46cd-96f6-57080cc988e3/page/N26rF','Hypertension - Medical & Pharmacy Claims Summary',NULL,NULL),(14,1,1,'Demo',3,'Client Services',1,1,'6b552946-9e2f-4057-b447-d6b3d8a1eecb/page/KJutF','Chronic Conditions Summary',NULL,NULL),(15,1,1,'Demo',3,'Client Services',1,1,'40a282cd-a638-4d9d-a118-1e8bb3177fcf/page/VxqrF','Claims Analysis Summary (Filter by Calendar Year)',NULL,NULL),(16,1,1,'Demo',3,'Client Services',1,1,'79d48e3b-2b09-4d39-a446-5ff85e4d6eed/page/DsorF','Claims Analysis Summary (Filter by Plan Year)',NULL,NULL),(17,1,1,'Demo',3,'Client Services',1,1,'ff856ed9-9931-4748-8a69-e2bcb5c25ecc/page/DTErF','Demographic & Claims Summary',NULL,NULL),(18,1,1,'Demo',3,'Client Services',1,1,'142ac897-91ad-4e2d-9e4c-f8904410da55/page/bLNqF','Members with Claims above Average Paid Amount',NULL,NULL),(19,1,1,'Demo',3,'Client Services',1,1,'7a0b5df2-abf6-4be3-b8d2-f72c15cf5b4c/page/V3ntF','Monthly Report - Member Data',NULL,NULL),(20,1,1,'Demo',3,'Client Services',1,1,'e8cc9825-ac8e-4be6-95eb-1c0715cb7a09/page/WivqF','Monthly Report - Summary',NULL,NULL),(21,1,1,'Demo',3,'Client Services',1,1,'0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF','Overall Population Demographic Summary',NULL,NULL),(22,1,1,'Demo',3,'Client Services',1,1,'4a99e41e-559d-4ecc-af28-4d20df8b081e/page/FyDrF','Quarterly Report',NULL,NULL),(23,1,1,'Demo',3,'Client Services',1,1,'e64461ae-904c-4858-9c46-96416cd74629/page/W2ErF','Referral List (All Members)',NULL,NULL),(24,1,1,'Demo',3,'Client Services',1,1,'7267098f-c2c1-4d2e-a89a-eebb14bfdfba/page/OxYrF','Referral List (New Eligible Members)',NULL,NULL),(25,1,1,'Demo',4,'Clinical',1,1,'06081a4e-d5c3-4b5a-ac29-4640fcad03a4/page/LD3pF','Chronic Conditions Summary',NULL,NULL),(26,1,1,'Demo',4,'Clinical',1,1,'3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF','Diabetes - Evidence-Based Rules Compliance',NULL,NULL),(27,1,1,'Demo',4,'Clinical',1,1,'69ea3c08-c9d6-4931-9555-1f841b8d907a/page/Pa5rF','Heart Disease - Demographic & Economic Insights',NULL,NULL),(28,1,1,'Demo',4,'Clinical',1,1,'adbe15cb-290e-462f-ab97-2322c6a121ee/page/RI7rF','Hypertension - Demographic & Economic Insights',NULL,NULL),(29,1,1,'Demo',4,'Clinical',1,1,'a447804d-7d9c-430d-ae13-e5b413f8f3ca/page/TGvsF?s=pNyj_yFAdrk','Member Data Summary (Filter by Calendar Year)',NULL,NULL),(30,1,1,'Demo',4,'Clinical',1,1,'73c06507-70a6-45bc-a7fa-17e467109d4b/page/xzYrF','Member Data Summary (Filter by Plan Year)',NULL,NULL),(31,1,1,'Demo',4,'Clinical',1,1,'e5b4dcb1-b170-44e8-9ffd-a929cf3d2ee9/page/TRCrF','Member Summary (Additional Details)',NULL,NULL),(32,1,1,'Demo',4,'Clinical',1,1,'3e1b27d4-53b4-4b82-8259-b56c03a41cd2/page/277sF','Risk Groups Migration (Detailed Analysis)',NULL,NULL),(33,1,1,'Demo',5,'Cohort Analysis',1,1,'c147daa4-623c-4ed2-9d0a-cdacdf5624a4/page/jKbtF','Cohort Analysis (Compare 2 Groups)',NULL,NULL),(34,1,1,'Demo',6,'Executive Summary Report',1,1,'47063557-d4ce-4759-a35a-f3ad48e7fc3d/page/q2frF','Executive Summary Report',NULL,NULL),(35,1,1,'Demo',6,'Executive Summary Report',1,1,'38a89819-13fd-4bdb-8798-065d8b192caa/page/q2frF','Executive Summary Report NEW',NULL,NULL),(36,1,1,'Demo',7,'Health Score & Predictive Reports',1,1,'bfec70c9-7cb1-4854-9a1c-b656e9b9382d/page/wjvqF','All Disease Variable Trend',NULL,NULL),(37,1,1,'Demo',7,'Health Score & Predictive Reports',1,1,'51d47db0-d0b9-480d-a45f-c01df0c8488e/page/eNoqF','Data Science Overview',NULL,NULL),(38,1,1,'Demo',7,'Health Score & Predictive Reports',1,1,'8758e0a2-e25e-46be-afee-841ae094954c/page/wQarF','Data Science Predictive Analysis (Overview & Member-Level Analysis)',NULL,NULL),(39,1,1,'Demo',7,'Health Score & Predictive Reports',1,1,'c9bb5a3b-19cd-41a2-88a6-48dce309150a/page/9TQsF','Evidence-Based Rules Compliance Statistical Analysis',NULL,NULL),(40,1,1,'Demo',7,'Health Score & Predictive Reports',1,1,'f8cb318d-3ed3-4fc2-aacd-07186782f951/page/QmpqF','Health Score (Member-Level Analysis)',NULL,NULL),(41,1,1,'Demo',7,'Health Score & Predictive Reports',1,1,'3a49c605-dbf8-41c1-b1dd-a85973ddfb37/page/4wvqF','Health Score & Risk Group Overview',NULL,NULL),(42,1,1,'Demo',7,'Health Score & Predictive Reports',1,1,'f0be7979-521b-43bb-b759-6d7f737ffd3b/page/hvrrF','Health Score Decile & Quartile Analysis',NULL,NULL),(43,1,1,'Demo',7,'Health Score & Predictive Reports',1,1,'4a91a614-142d-4415-9b2f-6eb3ba9dc20e/page/vbqrF','Health Score Summary',NULL,NULL),(44,1,1,'Demo',7,'Health Score & Predictive Reports',1,1,'e58aba4a-2ff1-4b38-8d0b-4677eecafe72/page/xzorF','Spend Analysis',NULL,NULL),(45,1,1,'Demo',8,'Medical Reports',1,1,'cdd12ab1-e2bf-4cee-b09f-226307ad758d/page/khDqF','Ad Hoc Query Tool',NULL,NULL),(46,1,1,'Demo',8,'Medical Reports',1,1,'6f2243e3-016d-48b0-a7e8-aba8b78f2969/page/O3YrF','Ad Hoc Query Tool 2.0',NULL,NULL),(47,1,1,'Demo',8,'Medical Reports',1,1,'3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF','Chronic Conditions Summary',NULL,NULL),(48,1,1,'Demo',8,'Medical Reports',1,1,'7c416038-6a2a-4871-bf0f-59dfb862f0ff/page/OZLrF','Demographic & Claims Summary',NULL,NULL),(49,1,1,'Demo',8,'Medical Reports',1,1,'039a2081-96d4-4b1c-b5b9-838179aca217?s=mEGINX8eOXM','Diagnostic Category Summary',NULL,NULL),(50,1,1,'Demo',8,'Medical Reports',1,1,'ce22cf0a-e84f-45a6-8e17-467df3a3f7b5?s=qu8LHQQoGRQ','Lifestyle Modifiable & Preventive Summary',NULL,NULL),(51,1,1,'Demo',8,'Medical Reports',1,1,'cb34fc42-7e4d-4543-aaed-4c927851650a/page/6lYrF?s=nrXqnsahTbA','Medical summary - Care Coordination',NULL,NULL),(52,1,1,'Demo',8,'Medical Reports',1,1,'dd47879a-9083-4ef7-a32a-40c098f026ec/page/8xrsF','Members with Claims above Average Paid Amount',NULL,NULL),(53,1,1,'Demo',8,'Medical Reports',1,1,'0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF','Overall Population Demographic Summary',NULL,NULL),(54,1,1,'Demo',8,'Medical Reports',1,1,'d0afceba-9d8b-4015-8e3a-f66c8e8da421/page/PcrrF?s=ltoHxOaGM9Y','Preventive Claims Summary',NULL,NULL),(55,1,1,'Demo',8,'Medical Reports',1,1,'ad1f3cb4-cff1-4e05-8d9f-bc7daba14234/page/c0DrF','Total Lost Days Summary',NULL,NULL),(56,1,1,'Demo',9,'MSK Reports',1,1,'e8d44de7-523d-42bb-9768-36cd43daab40/page/O7grF','Hip ICD Codes Analysis',NULL,NULL),(57,1,1,'Demo',9,'MSK Reports',1,1,'48ad9390-6e83-4437-8f26-c02cbebcb978/page/O7grF','Knee ICD Codes Analysis',NULL,NULL),(58,1,1,'Demo',9,'MSK Reports',1,1,'94febcad-6fc2-42c2-93ca-7bdc63412f16/page/DbErF','Medical MSK - Overall Demographic & Economic Insights',NULL,NULL),(59,1,1,'Demo',9,'MSK Reports',1,1,'38def230-e743-48df-bb7f-761e7adbc89d/page/O7grF','Medical MSK - Productivity and Absenteeism Insights',NULL,NULL),(60,1,1,'Demo',9,'MSK Reports',1,1,'e187b01a-888a-4f91-9997-2a53ef6460ce/page/CDEqF','Medical MSK - Provider Insights',NULL,NULL),(61,1,1,'Demo',9,'MSK Reports',1,1,'30854a03-9a10-43a7-aa7b-c31f7d8b0136/page/a5YrF','Medical MSK - Work Related Disorders',NULL,NULL),(62,1,1,'Demo',9,'MSK Reports',1,1,'90f0e070-9b11-4034-8344-513e30d7ccd3/page/7RStF','MRS Modifiable ICD Codes - Overall Analysis',NULL,NULL),(63,1,1,'Demo',9,'MSK Reports',1,1,'81bd2b0a-ed50-454a-a3e1-5098b2acfc50/page/O7grF','MSK MED/PHARMA Analysis',NULL,NULL),(64,1,1,'Demo',9,'MSK Reports',1,1,'e6f97bb9-c053-4c83-b84f-d0e9b89b31c0/page/O7grF','Shoulder ICD Codes Analysis',NULL,NULL),(65,1,1,'Demo',9,'MSK Reports',1,1,'e5b9770f-7bdd-44b4-9120-ce576216b631/page/n5xrF','Spine ICD Codes Analysis',NULL,NULL),(66,1,1,'Demo',9,'MSK Reports',1,1,'c4386014-392e-49e8-ba4d-c9cb69f168b9/page/O7grF','TRUE MSK Cost Analysis',NULL,NULL),(67,1,1,'Demo',10,'Operations',1,1,'7b758ca9-c8d5-4a88-867b-b154ec0d5b5d/page/QPMrF','Eligibility History Report',NULL,NULL),(68,1,1,'Demo',10,'Operations',1,1,'16986e3c-13e1-4318-8845-a333bcbfdd5d/page/aVxqF','Referral Data - Demographic information',NULL,NULL),(69,1,1,'Demo',11,'Pharmacy Reports',1,1,'71d2743e-7002-4480-976c-262b83e22e08/page/uqgrF','Drug Class (Member-Level Analysis)',NULL,NULL),(70,1,1,'Demo',11,'Pharmacy Reports',1,1,'a19b0a75-102f-4f0e-a70a-d393e86422a4/page/5HYrF','Drug Class Summary',NULL,NULL),(71,1,1,'Demo',11,'Pharmacy Reports',1,1,'90e4a1d7-e3a7-4b8a-8f56-be45cff395ce','Medication Compliance Summary',NULL,NULL),(72,1,1,'Demo',11,'Pharmacy Reports',1,1,'343d5047-005d-448f-8602-fa69251642b4/page/laxrF','Pharmacy Claims Overview',NULL,NULL),(73,1,1,'Demo',11,'Pharmacy Reports',1,1,'596c34ed-f288-4368-bcbe-ed0f41a59a3a/page/bCEqF','Proportion of Days Covered (Member-Level Summary)',NULL,NULL),(74,1,1,'Demo',12,'Risk Groups',1,1,'ae9843b1-4d25-4119-8038-9acf1c1ab235/page/8yZrF','Risk Groups (Member-Level Analysis)',NULL,NULL),(75,1,1,'Demo',12,'Risk Groups',1,1,'064898c2-6d58-4e31-8556-8befbd5f21e1/page/kIFrF','Risk Groups Migration (Detailed Analysis)',NULL,NULL),(76,1,1,'Demo',12,'Risk Groups',1,1,'5e63f667-efd6-4277-ae67-32d219a74516/page/mJprF','Risk Groups Migration (Summary)',NULL,NULL),(77,1,1,'Demo',12,'Risk Groups',1,1,'73c33d5c-576f-47fe-845b-892af7a851da/page/vFirF','Risk Groups Stratification Overview',NULL,NULL);
/*!40000 ALTER TABLE `looker_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `looker_groups`
--

DROP TABLE IF EXISTS `looker_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `looker_groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `looker_groups`
--

LOCK TABLES `looker_groups` WRITE;
/*!40000 ALTER TABLE `looker_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `looker_groups` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `role` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'SA'),(2,'ADMIN'),(3,'EDITOR'),(4,'GUEST');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `is_active` int DEFAULT '1',
  `is_signup` int DEFAULT '0',
  `user_group_id` int DEFAULT NULL,
  `role` int DEFAULT NULL,
  `entity_id` int DEFAULT '1',
  `external_user_id` int DEFAULT '1',
  `permissions` text,
  `theme` varchar(50) DEFAULT 'default',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `is_admin` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Kairos','Admin','kershr@gmail.com','$2y$10$51xEzh7UiscV/jqHR5hC3uagSjIsB.hc9GDUxi3Z0jEBB7VSbeydu',1,0,1,1,1,1,'{\"looker\":1,\"clients\":1,\"users\":1,\"roles\":1,\"group_module\":1,\"reports\":1,\"generate_report\":1,\"invite_user\":1,\"referral\":1}','Navy Blue','2026-04-01 08:33:13',NULL,NULL,'2026-04-01 08:33:13',1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_dasboards_mapping`
--

DROP TABLE IF EXISTS `users_dasboards_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_dasboards_mapping` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `dashboard_id` int DEFAULT NULL,
  `looker_dash_id` varchar(255) DEFAULT NULL,
  `grp_usr_mapping_id` int DEFAULT NULL,
  `client_primary_id` int DEFAULT NULL,
  `sub_dashboard_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_dasboards_mapping`
--

LOCK TABLES `users_dasboards_mapping` WRITE;
/*!40000 ALTER TABLE `users_dasboards_mapping` DISABLE KEYS */;
INSERT INTO `users_dasboards_mapping` VALUES (1,NULL,1,'b8c07a7a-32db-4e62-8c43-c8d026f24198/page/GdgrF',1,1,'b8c07a7a-32db-4e62-8c43-c8d026f24198/page/GdgrF'),(2,NULL,1,'3e897e25-9aa2-43f3-ad7e-72efab31be26/page/6igsF',1,1,'3e897e25-9aa2-43f3-ad7e-72efab31be26/page/6igsF'),(3,NULL,1,'23ffd42b-3f34-4da9-a440-b6b44f243baa/page/wa6sF',1,1,'23ffd42b-3f34-4da9-a440-b6b44f243baa/page/wa6sF'),(4,NULL,1,'5ad3029b-4cc1-49b4-b455-76632543b112/page/uKjrF',1,1,'5ad3029b-4cc1-49b4-b455-76632543b112/page/uKjrF'),(5,NULL,1,'23558699-9a01-4645-b048-4f7a5815ecf0/page/6uZrF',1,1,'23558699-9a01-4645-b048-4f7a5815ecf0/page/6uZrF'),(6,NULL,1,'0fb3cdff-6fee-4baf-a3fa-8178b009ad09/page/LlcrF',1,1,'0fb3cdff-6fee-4baf-a3fa-8178b009ad09/page/LlcrF'),(7,NULL,1,'b7d03c29-c802-4cb8-b615-d81ab34f4123/page/8rYrF',1,1,'b7d03c29-c802-4cb8-b615-d81ab34f4123/page/8rYrF'),(8,NULL,2,'cd86b02c-55a4-44d1-a635-804848a969ed/page/qmSsF',1,1,'cd86b02c-55a4-44d1-a635-804848a969ed/page/qmSsF'),(9,NULL,2,'bb19f877-be8d-46d5-8f0a-9972afe8151f/page/ALxqF',1,1,'bb19f877-be8d-46d5-8f0a-9972afe8151f/page/ALxqF'),(10,NULL,2,'af862975-cca2-4457-8769-23570d13f40f/page/GdgrF',1,1,'af862975-cca2-4457-8769-23570d13f40f/page/GdgrF'),(11,NULL,2,'56378c09-07b3-42fb-b77b-212faeaf0236/page/GdgrF',1,1,'56378c09-07b3-42fb-b77b-212faeaf0236/page/GdgrF'),(12,NULL,2,'48eb81f4-b1ef-4053-b160-2e3e7e443d03/page/dAyrF',1,1,'48eb81f4-b1ef-4053-b160-2e3e7e443d03/page/dAyrF'),(13,NULL,2,'c5c9ece6-34af-46cd-96f6-57080cc988e3/page/N26rF',1,1,'c5c9ece6-34af-46cd-96f6-57080cc988e3/page/N26rF'),(14,NULL,3,'6b552946-9e2f-4057-b447-d6b3d8a1eecb/page/KJutF',1,1,'6b552946-9e2f-4057-b447-d6b3d8a1eecb/page/KJutF'),(15,NULL,3,'40a282cd-a638-4d9d-a118-1e8bb3177fcf/page/VxqrF',1,1,'40a282cd-a638-4d9d-a118-1e8bb3177fcf/page/VxqrF'),(16,NULL,3,'79d48e3b-2b09-4d39-a446-5ff85e4d6eed/page/DsorF',1,1,'79d48e3b-2b09-4d39-a446-5ff85e4d6eed/page/DsorF'),(17,NULL,3,'ff856ed9-9931-4748-8a69-e2bcb5c25ecc/page/DTErF',1,1,'ff856ed9-9931-4748-8a69-e2bcb5c25ecc/page/DTErF'),(18,NULL,3,'142ac897-91ad-4e2d-9e4c-f8904410da55/page/bLNqF',1,1,'142ac897-91ad-4e2d-9e4c-f8904410da55/page/bLNqF'),(19,NULL,3,'7a0b5df2-abf6-4be3-b8d2-f72c15cf5b4c/page/V3ntF',1,1,'7a0b5df2-abf6-4be3-b8d2-f72c15cf5b4c/page/V3ntF'),(20,NULL,3,'e8cc9825-ac8e-4be6-95eb-1c0715cb7a09/page/WivqF',1,1,'e8cc9825-ac8e-4be6-95eb-1c0715cb7a09/page/WivqF'),(21,NULL,3,'0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF',1,1,'0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF'),(22,NULL,3,'4a99e41e-559d-4ecc-af28-4d20df8b081e/page/FyDrF',1,1,'4a99e41e-559d-4ecc-af28-4d20df8b081e/page/FyDrF'),(23,NULL,3,'e64461ae-904c-4858-9c46-96416cd74629/page/W2ErF',1,1,'e64461ae-904c-4858-9c46-96416cd74629/page/W2ErF'),(24,NULL,3,'7267098f-c2c1-4d2e-a89a-eebb14bfdfba/page/OxYrF',1,1,'7267098f-c2c1-4d2e-a89a-eebb14bfdfba/page/OxYrF'),(25,NULL,4,'06081a4e-d5c3-4b5a-ac29-4640fcad03a4/page/LD3pF',1,1,'06081a4e-d5c3-4b5a-ac29-4640fcad03a4/page/LD3pF'),(26,NULL,4,'3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF',1,1,'3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF'),(27,NULL,4,'69ea3c08-c9d6-4931-9555-1f841b8d907a/page/Pa5rF',1,1,'69ea3c08-c9d6-4931-9555-1f841b8d907a/page/Pa5rF'),(28,NULL,4,'adbe15cb-290e-462f-ab97-2322c6a121ee/page/RI7rF',1,1,'adbe15cb-290e-462f-ab97-2322c6a121ee/page/RI7rF'),(29,NULL,4,'a447804d-7d9c-430d-ae13-e5b413f8f3ca/page/TGvsF?s=pNyj_yFAdrk',1,1,'a447804d-7d9c-430d-ae13-e5b413f8f3ca/page/TGvsF?s=pNyj_yFAdrk'),(30,NULL,4,'73c06507-70a6-45bc-a7fa-17e467109d4b/page/xzYrF',1,1,'73c06507-70a6-45bc-a7fa-17e467109d4b/page/xzYrF'),(31,NULL,4,'e5b4dcb1-b170-44e8-9ffd-a929cf3d2ee9/page/TRCrF',1,1,'e5b4dcb1-b170-44e8-9ffd-a929cf3d2ee9/page/TRCrF'),(32,NULL,4,'3e1b27d4-53b4-4b82-8259-b56c03a41cd2/page/277sF',1,1,'3e1b27d4-53b4-4b82-8259-b56c03a41cd2/page/277sF'),(33,NULL,5,'c147daa4-623c-4ed2-9d0a-cdacdf5624a4/page/jKbtF',1,1,'c147daa4-623c-4ed2-9d0a-cdacdf5624a4/page/jKbtF'),(34,NULL,6,'47063557-d4ce-4759-a35a-f3ad48e7fc3d/page/q2frF',1,1,'47063557-d4ce-4759-a35a-f3ad48e7fc3d/page/q2frF'),(35,NULL,6,'38a89819-13fd-4bdb-8798-065d8b192caa/page/q2frF',1,1,'38a89819-13fd-4bdb-8798-065d8b192caa/page/q2frF'),(36,NULL,7,'bfec70c9-7cb1-4854-9a1c-b656e9b9382d/page/wjvqF',1,1,'bfec70c9-7cb1-4854-9a1c-b656e9b9382d/page/wjvqF'),(37,NULL,7,'51d47db0-d0b9-480d-a45f-c01df0c8488e/page/eNoqF',1,1,'51d47db0-d0b9-480d-a45f-c01df0c8488e/page/eNoqF'),(38,NULL,7,'8758e0a2-e25e-46be-afee-841ae094954c/page/wQarF',1,1,'8758e0a2-e25e-46be-afee-841ae094954c/page/wQarF'),(39,NULL,7,'c9bb5a3b-19cd-41a2-88a6-48dce309150a/page/9TQsF',1,1,'c9bb5a3b-19cd-41a2-88a6-48dce309150a/page/9TQsF'),(40,NULL,7,'f8cb318d-3ed3-4fc2-aacd-07186782f951/page/QmpqF',1,1,'f8cb318d-3ed3-4fc2-aacd-07186782f951/page/QmpqF'),(41,NULL,7,'3a49c605-dbf8-41c1-b1dd-a85973ddfb37/page/4wvqF',1,1,'3a49c605-dbf8-41c1-b1dd-a85973ddfb37/page/4wvqF'),(42,NULL,7,'f0be7979-521b-43bb-b759-6d7f737ffd3b/page/hvrrF',1,1,'f0be7979-521b-43bb-b759-6d7f737ffd3b/page/hvrrF'),(43,NULL,7,'4a91a614-142d-4415-9b2f-6eb3ba9dc20e/page/vbqrF',1,1,'4a91a614-142d-4415-9b2f-6eb3ba9dc20e/page/vbqrF'),(44,NULL,7,'e58aba4a-2ff1-4b38-8d0b-4677eecafe72/page/xzorF',1,1,'e58aba4a-2ff1-4b38-8d0b-4677eecafe72/page/xzorF'),(45,NULL,8,'cdd12ab1-e2bf-4cee-b09f-226307ad758d/page/khDqF',1,1,'cdd12ab1-e2bf-4cee-b09f-226307ad758d/page/khDqF'),(46,NULL,8,'6f2243e3-016d-48b0-a7e8-aba8b78f2969/page/O3YrF',1,1,'6f2243e3-016d-48b0-a7e8-aba8b78f2969/page/O3YrF'),(47,NULL,8,'3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF',1,1,'3fe2bdf3-c121-4e19-9f36-dfdaf1648831/page/LodrF'),(48,NULL,8,'7c416038-6a2a-4871-bf0f-59dfb862f0ff/page/OZLrF',1,1,'7c416038-6a2a-4871-bf0f-59dfb862f0ff/page/OZLrF'),(49,NULL,8,'039a2081-96d4-4b1c-b5b9-838179aca217?s=mEGINX8eOXM',1,1,'039a2081-96d4-4b1c-b5b9-838179aca217?s=mEGINX8eOXM'),(50,NULL,8,'ce22cf0a-e84f-45a6-8e17-467df3a3f7b5?s=qu8LHQQoGRQ',1,1,'ce22cf0a-e84f-45a6-8e17-467df3a3f7b5?s=qu8LHQQoGRQ'),(51,NULL,8,'cb34fc42-7e4d-4543-aaed-4c927851650a/page/6lYrF?s=nrXqnsahTbA',1,1,'cb34fc42-7e4d-4543-aaed-4c927851650a/page/6lYrF?s=nrXqnsahTbA'),(52,NULL,8,'dd47879a-9083-4ef7-a32a-40c098f026ec/page/8xrsF',1,1,'dd47879a-9083-4ef7-a32a-40c098f026ec/page/8xrsF'),(53,NULL,8,'0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF',1,1,'0334398c-0f7d-4e3a-9481-1824b8d34ad7/page/HqCrF'),(54,NULL,8,'d0afceba-9d8b-4015-8e3a-f66c8e8da421/page/PcrrF?s=ltoHxOaGM9Y',1,1,'d0afceba-9d8b-4015-8e3a-f66c8e8da421/page/PcrrF?s=ltoHxOaGM9Y'),(55,NULL,8,'ad1f3cb4-cff1-4e05-8d9f-bc7daba14234/page/c0DrF',1,1,'ad1f3cb4-cff1-4e05-8d9f-bc7daba14234/page/c0DrF'),(56,NULL,9,'e8d44de7-523d-42bb-9768-36cd43daab40/page/O7grF',1,1,'e8d44de7-523d-42bb-9768-36cd43daab40/page/O7grF'),(57,NULL,9,'48ad9390-6e83-4437-8f26-c02cbebcb978/page/O7grF',1,1,'48ad9390-6e83-4437-8f26-c02cbebcb978/page/O7grF'),(58,NULL,9,'94febcad-6fc2-42c2-93ca-7bdc63412f16/page/DbErF',1,1,'94febcad-6fc2-42c2-93ca-7bdc63412f16/page/DbErF'),(59,NULL,9,'38def230-e743-48df-bb7f-761e7adbc89d/page/O7grF',1,1,'38def230-e743-48df-bb7f-761e7adbc89d/page/O7grF'),(60,NULL,9,'e187b01a-888a-4f91-9997-2a53ef6460ce/page/CDEqF',1,1,'e187b01a-888a-4f91-9997-2a53ef6460ce/page/CDEqF'),(61,NULL,9,'30854a03-9a10-43a7-aa7b-c31f7d8b0136/page/a5YrF',1,1,'30854a03-9a10-43a7-aa7b-c31f7d8b0136/page/a5YrF'),(62,NULL,9,'90f0e070-9b11-4034-8344-513e30d7ccd3/page/7RStF',1,1,'90f0e070-9b11-4034-8344-513e30d7ccd3/page/7RStF'),(63,NULL,9,'81bd2b0a-ed50-454a-a3e1-5098b2acfc50/page/O7grF',1,1,'81bd2b0a-ed50-454a-a3e1-5098b2acfc50/page/O7grF'),(64,NULL,9,'e6f97bb9-c053-4c83-b84f-d0e9b89b31c0/page/O7grF',1,1,'e6f97bb9-c053-4c83-b84f-d0e9b89b31c0/page/O7grF'),(65,NULL,9,'e5b9770f-7bdd-44b4-9120-ce576216b631/page/n5xrF',1,1,'e5b9770f-7bdd-44b4-9120-ce576216b631/page/n5xrF'),(66,NULL,9,'c4386014-392e-49e8-ba4d-c9cb69f168b9/page/O7grF',1,1,'c4386014-392e-49e8-ba4d-c9cb69f168b9/page/O7grF'),(67,NULL,10,'7b758ca9-c8d5-4a88-867b-b154ec0d5b5d/page/QPMrF',1,1,'7b758ca9-c8d5-4a88-867b-b154ec0d5b5d/page/QPMrF'),(68,NULL,10,'16986e3c-13e1-4318-8845-a333bcbfdd5d/page/aVxqF',1,1,'16986e3c-13e1-4318-8845-a333bcbfdd5d/page/aVxqF'),(69,NULL,11,'71d2743e-7002-4480-976c-262b83e22e08/page/uqgrF',1,1,'71d2743e-7002-4480-976c-262b83e22e08/page/uqgrF'),(70,NULL,11,'a19b0a75-102f-4f0e-a70a-d393e86422a4/page/5HYrF',1,1,'a19b0a75-102f-4f0e-a70a-d393e86422a4/page/5HYrF'),(71,NULL,11,'90e4a1d7-e3a7-4b8a-8f56-be45cff395ce',1,1,'90e4a1d7-e3a7-4b8a-8f56-be45cff395ce'),(72,NULL,11,'343d5047-005d-448f-8602-fa69251642b4/page/laxrF',1,1,'343d5047-005d-448f-8602-fa69251642b4/page/laxrF'),(73,NULL,11,'596c34ed-f288-4368-bcbe-ed0f41a59a3a/page/bCEqF',1,1,'596c34ed-f288-4368-bcbe-ed0f41a59a3a/page/bCEqF'),(74,NULL,12,'ae9843b1-4d25-4119-8038-9acf1c1ab235/page/8yZrF',1,1,'ae9843b1-4d25-4119-8038-9acf1c1ab235/page/8yZrF'),(75,NULL,12,'064898c2-6d58-4e31-8556-8befbd5f21e1/page/kIFrF',1,1,'064898c2-6d58-4e31-8556-8befbd5f21e1/page/kIFrF'),(76,NULL,12,'5e63f667-efd6-4277-ae67-32d219a74516/page/mJprF',1,1,'5e63f667-efd6-4277-ae67-32d219a74516/page/mJprF'),(77,NULL,12,'73c33d5c-576f-47fe-845b-892af7a851da/page/vFirF',1,1,'73c33d5c-576f-47fe-845b-892af7a851da/page/vFirF');
/*!40000 ALTER TABLE `users_dasboards_mapping` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-01 14:58:15
