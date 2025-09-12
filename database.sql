-- Create the database
CREATE DATABASE IF NOT EXISTS `my_dottorci`;

-- Use the created database
USE `my_dottorci`;

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('teacher','student') NOT NULL,
  `status` enum('pending','active','disabled') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `classe` varchar(10) DEFAULT NULL,
  `corso` varchar(255) DEFAULT NULL,
  `anno_scolastico` varchar(9) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `exercises`
--

CREATE TABLE `exercises` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` enum('multiple_choice','open_answer','fill_in_the_blanks') NOT NULL,
  `content` text NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `exercises_chk_1` CHECK (json_valid(`options`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `student_exercise_answers`
--

CREATE TABLE `student_exercise_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `exercise_id` int(11) NOT NULL,
  `answer` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `corrected_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `exercise_id` (`exercise_id`),
  KEY `corrected_by` (`corrected_by`),
  CONSTRAINT `student_exercise_answers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_exercise_answers_ibfk_2` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_exercise_answers_ibfk_3` FOREIGN KEY (`corrected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_exercise_answers_chk_1` CHECK (json_valid(`answer`)),
  UNIQUE KEY `user_exercise_unique` (`user_id`,`exercise_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella per le discipline
CREATE TABLE `discipline` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome_unique` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `disciplina_id` int(11) DEFAULT NULL,
  `anno_corso` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `disciplina_id` (`disciplina_id`),
  CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`disciplina_id`) REFERENCES `discipline` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `udas`
--

CREATE TABLE `udas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`),
  CONSTRAINT `udas_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Nuove tabelle per la gestione di conoscenze, abilità e competenze

-- Tabella per le tipologie di competenze
CREATE TABLE `tipologie_competenze` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome_unique` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella per le conoscenze
CREATE TABLE `conoscenze` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `descrizione` TEXT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella per le abilità
CREATE TABLE `abilita` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `descrizione` TEXT,
  `tipo` ENUM('cognitiva', 'tecnico/pratica') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella per le competenze
CREATE TABLE `competenze` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `descrizione` TEXT,
  `tipologia_id` INT(11),
  PRIMARY KEY (`id`),
  KEY `tipologia_id` (`tipologia_id`),
  CONSTRAINT `competenze_ibfk_1` FOREIGN KEY (`tipologia_id`) REFERENCES `tipologie_competenze` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- TABELLE DI RELAZIONE (JOIN TABLES)
--

-- Relazione Abilità -> Conoscenze (N a N)
CREATE TABLE `abilita_conoscenze` (
  `abilita_id` INT(11) NOT NULL,
  `conoscenza_id` INT(11) NOT NULL,
  PRIMARY KEY (`abilita_id`, `conoscenza_id`),
  KEY `abilita_id` (`abilita_id`),
  KEY `conoscenza_id` (`conoscenza_id`),
  CONSTRAINT `abilita_conoscenze_ibfk_1` FOREIGN KEY (`abilita_id`) REFERENCES `abilita` (`id`) ON DELETE CASCADE,
  CONSTRAINT `abilita_conoscenze_ibfk_2` FOREIGN KEY (`conoscenza_id`) REFERENCES `conoscenze` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relazione Competenze -> Conoscenze (N a N)
CREATE TABLE `competenza_conoscenze` (
  `competenza_id` INT(11) NOT NULL,
  `conoscenza_id` INT(11) NOT NULL,
  PRIMARY KEY (`competenza_id`, `conoscenza_id`),
  KEY `competenza_id` (`competenza_id`),
  KEY `conoscenza_id` (`conoscenza_id`),
  CONSTRAINT `competenza_conoscenze_ibfk_1` FOREIGN KEY (`competenza_id`) REFERENCES `competenze` (`id`) ON DELETE CASCADE,
  CONSTRAINT `competenza_conoscenze_ibfk_2` FOREIGN KEY (`conoscenza_id`) REFERENCES `conoscenze` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relazione Competenze -> Abilità (N a N)
CREATE TABLE `competenza_abilita` (
  `competenza_id` INT(11) NOT NULL,
  `abilita_id` INT(11) NOT NULL,
  PRIMARY KEY (`competenza_id`, `abilita_id`),
  KEY `competenza_id` (`competenza_id`),
  KEY `abilita_id` (`abilita_id`),
  CONSTRAINT `competenza_abilita_ibfk_1` FOREIGN KEY (`competenza_id`) REFERENCES `competenze` (`id`) ON DELETE CASCADE,
  CONSTRAINT `competenza_abilita_ibfk_2` FOREIGN KEY (`abilita_id`) REFERENCES `abilita` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Nuove tabelle per la gestione delle Verifiche
--

-- Tabella per le verifiche
CREATE TABLE `verifiche` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `titolo` VARCHAR(255) NOT NULL,
  `descrizione` TEXT,
  `tipo` ENUM('scritto', 'orale') NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relazione Verifiche -> Abilità (N a N)
CREATE TABLE `verifica_abilita` (
  `verifica_id` INT(11) NOT NULL,
  `abilita_id` INT(11) NOT NULL,
  PRIMARY KEY (`verifica_id`, `abilita_id`),
  FOREIGN KEY (`verifica_id`) REFERENCES `verifiche`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`abilita_id`) REFERENCES `abilita`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relazione Verifiche -> Competenze (N a N)
-- Relazione Verifiche -> Conoscenze (N a N)
CREATE TABLE `verifica_conoscenze` (
  `verifica_id` INT(11) NOT NULL,
  `conoscenza_id` INT(11) NOT NULL,
  PRIMARY KEY (`verifica_id`, `conoscenza_id`),
  FOREIGN KEY (`verifica_id`) REFERENCES `verifiche`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`conoscenza_id`) REFERENCES `conoscenze`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella per le griglie di valutazione
CREATE TABLE `griglie_valutazione` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `verifica_id` INT(11) NOT NULL,
  `nome` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`verifica_id`) REFERENCES `verifiche`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella per i descrittori delle griglie
CREATE TABLE `griglia_descrittori` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `griglia_id` INT(11) NOT NULL,
  `descrittore` TEXT NOT NULL,
  `punteggio_max` DECIMAL(4,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`griglia_id`) REFERENCES `griglie_valutazione`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella per il registro delle verifiche (valutazioni degli studenti)
CREATE TABLE `registri_verifiche` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `verifica_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `data_svolgimento` DATE NOT NULL,
  `punteggio_totale` DECIMAL(4,2) NOT NULL,
  `note` TEXT,
  `corretto_da` INT(11),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`verifica_id`) REFERENCES `verifiche`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`corretto_da`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `verifica_studente_data_unique` (`verifica_id`, `user_id`, `data_svolgimento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `uda_id` int(11) DEFAULT NULL,
  `previous_lesson_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `previous_lesson_id` (`previous_lesson_id`),
  KEY `uda_id` (`uda_id`),
  CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`previous_lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lessons_ibfk_2` FOREIGN KEY (`uda_id`) REFERENCES `udas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `student_lesson_data`
--

CREATE TABLE `student_lesson_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `student_lesson_data_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_lesson_data_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_lesson_data_chk_1` CHECK (json_valid(`data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `exercise_lesson`
--

CREATE TABLE `exercise_lesson` (
  `exercise_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  PRIMARY KEY (`exercise_id`,`lesson_id`),
  KEY `exercise_id` (`exercise_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `exercise_lesson_ibfk_1` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exercise_lesson_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `uda_lessons`
--

CREATE TABLE `uda_lessons` (
  `uda_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  PRIMARY KEY (`uda_id`, `lesson_id`),
  KEY `uda_id` (`uda_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `uda_lessons_ibfk_1` FOREIGN KEY (`uda_id`) REFERENCES `udas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `uda_lessons_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relazione Lezioni -> Conoscenze (N a N)
CREATE TABLE `lezione_conoscenze` (
  `lezione_id` INT(11) NOT NULL,
  `conoscenza_id` INT(11) NOT NULL,
  PRIMARY KEY (`lezione_id`, `conoscenza_id`),
  KEY `lezione_id` (`lezione_id`),
  KEY `conoscenza_id` (`conoscenza_id`),
  CONSTRAINT `lezione_conoscenze_ibfk_1` FOREIGN KEY (`lezione_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lezione_conoscenze_ibfk_2` FOREIGN KEY (`conoscenza_id`) REFERENCES `conoscenze` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relazione Lezioni -> Abilità (N a N)
CREATE TABLE `lezione_abilita` (
  `lezione_id` INT(11) NOT NULL,
  `abilita_id` INT(11) NOT NULL,
  PRIMARY KEY (`lezione_id`, `abilita_id`),
  KEY `lezione_id` (`lezione_id`),
  KEY `abilita_id` (`abilita_id`),
  CONSTRAINT `lezione_abilita_ibfk_1` FOREIGN KEY (`lezione_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lezione_abilita_ibfk_2` FOREIGN KEY (`abilita_id`) REFERENCES `abilita` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relazione Conoscenze -> Anno Corso (N a N)
CREATE TABLE `conoscenza_anni_corso` (
  `conoscenza_id` INT(11) NOT NULL,
  `anno_corso` TINYINT NOT NULL,
  PRIMARY KEY (`conoscenza_id`, `anno_corso`),
  FOREIGN KEY (`conoscenza_id`) REFERENCES `conoscenze`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relazione Abilità -> Anno Corso (N a N)
CREATE TABLE `abilita_anni_corso` (
  `abilita_id` INT(11) NOT NULL,
  `anno_corso` TINYINT NOT NULL,
  PRIMARY KEY (`abilita_id`, `anno_corso`),
  FOREIGN KEY (`abilita_id`) REFERENCES `abilita`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relazione Competenze -> Anno Corso (N a N)
CREATE TABLE `competenza_anni_corso` (
  `competenza_id` INT(11) NOT NULL,
  `anno_corso` TINYINT NOT NULL,
  PRIMARY KEY (`competenza_id`, `anno_corso`),
  FOREIGN KEY (`competenza_id`) REFERENCES `competenze`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
