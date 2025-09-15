-- Migration for calendar feature

-- Tabella per gli appuntamenti
CREATE TABLE `appuntamenti` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `titolo` VARCHAR(255) NOT NULL,
  `tipo` VARCHAR(50) NOT NULL,
  `data_inizio` DATETIME NOT NULL,
  `data_fine` DATETIME NOT NULL,
  `descrizione` TEXT,
  `disciplina_id` INT(11),
  `user_id` INT(11),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`disciplina_id`) REFERENCES `discipline`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabella per l'orario delle lezioni
CREATE TABLE `orari_lezioni` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `disciplina_id` INT(11) NOT NULL,
  `giorno_settimana` TINYINT NOT NULL,
  `ora_inizio` TIME NOT NULL,
  `ora_fine` TIME NOT NULL,
  `validita_inizio` DATE NOT NULL,
  `validita_fine` DATE NOT NULL,
  `user_id` INT(11),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`disciplina_id`) REFERENCES `discipline`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
