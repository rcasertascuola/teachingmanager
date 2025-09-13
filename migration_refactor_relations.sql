-- SQL script to refactor database relationships for modules, knowledge, skills, and competencies.

-- Step 1: Drop the old relationship tables that linked knowledge, skills, and competencies together.
DROP TABLE IF EXISTS `abilita_conoscenze`;
DROP TABLE IF EXISTS `competenza_conoscenze`;
DROP TABLE IF EXISTS `competenza_abilita`;

-- Step 2: Drop the old relationship tables for 'verifiche' (assessments).
DROP TABLE IF EXISTS `verifica_abilita`;
DROP TABLE IF EXISTS `verifica_conoscenze`;

-- Step 3: Create new join tables to link modules directly with knowledge, skills, and competencies.
CREATE TABLE `module_conoscenze` (
  `module_id` INT(11) NOT NULL,
  `conoscenza_id` INT(11) NOT NULL,
  PRIMARY KEY (`module_id`, `conoscenza_id`),
  FOREIGN KEY (`module_id`) REFERENCES `modules`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`conoscenza_id`) REFERENCES `conoscenze`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `module_abilita` (
  `module_id` INT(11) NOT NULL,
  `abilita_id` INT(11) NOT NULL,
  PRIMARY KEY (`module_id`, `abilita_id`),
  FOREIGN KEY (`module_id`) REFERENCES `modules`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`abilita_id`) REFERENCES `abilita`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `module_competenze` (
  `module_id` INT(11) NOT NULL,
  `competenza_id` INT(11) NOT NULL,
  PRIMARY KEY (`module_id`, `competenza_id`),
  FOREIGN KEY (`module_id`) REFERENCES `modules`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`competenza_id`) REFERENCES `competenze`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Step 4: Alter the 'verifiche' table to add a foreign key to the 'modules' table.
-- This will link each assessment to a module, from which it will inherit knowledge, skills, and competencies.
ALTER TABLE `verifiche`
ADD COLUMN `module_id` INT(11) NULL AFTER `id`,
ADD CONSTRAINT `fk_verifiche_module`
  FOREIGN KEY (`module_id`)
  REFERENCES `modules`(`id`)
  ON DELETE SET NULL;
