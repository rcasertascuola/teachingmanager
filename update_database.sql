-- Tabella per i contenuti
CREATE TABLE `contenuti` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NOT NULL,
  `descrizione` TEXT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relazione Contenuti -> Conoscenze (N a N)
CREATE TABLE `contenuto_conoscenze` (
  `contenuto_id` INT(11) NOT NULL,
  `conoscenza_id` INT(11) NOT NULL,
  PRIMARY KEY (`contenuto_id`, `conoscenza_id`),
  FOREIGN KEY (`contenuto_id`) REFERENCES `contenuti`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`conoscenza_id`) REFERENCES `conoscenze`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relazione Contenuti -> Abilità (N a N)
CREATE TABLE `contenuto_abilita` (
  `contenuto_id` INT(11) NOT NULL,
  `abilita_id` INT(11) NOT NULL,
  PRIMARY KEY (`contenuto_id`, `abilita_id`),
  FOREIGN KEY (`contenuto_id`) REFERENCES `contenuti`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`abilita_id`) REFERENCES `abilita`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relazione Lezioni -> Contenuti (N a N)
CREATE TABLE `lezione_contenuti` (
  `lezione_id` INT(11) NOT NULL,
  `contenuto_id` INT(11) NOT NULL,
  PRIMARY KEY (`lezione_id`, `contenuto_id`),
  FOREIGN KEY (`lezione_id`) REFERENCES `lessons`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`contenuto_id`) REFERENCES `contenuti`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
