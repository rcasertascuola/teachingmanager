<?php

class AnniCorsoManager
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Recalculates and updates all course year associations for every knowledge and skill.
     * This method is expensive and should be called within a transaction when other data is being modified.
     * @return bool True on success, false on failure.
     */
    public function updateAll()
    {
        try {
            // No transaction here, assuming it's managed by the calling save() method.

            // --- YEARS ---

            // 1. Clear existing associations for years
            $this->conn->exec('DELETE FROM competenza_anni_corso');
            $this->conn->exec('DELETE FROM conoscenza_anni_corso');
            $this->conn->exec('DELETE FROM abilita_anni_corso');

            // 2. Recalculate and insert for Conoscenze (years)
            $sql_conoscenze_anni = "
                INSERT INTO conoscenza_anni_corso (conoscenza_id, anno_corso)
                SELECT DISTINCT lc.conoscenza_id, m.anno_corso
                FROM lezione_conoscenze lc
                JOIN lessons l ON lc.lezione_id = l.id
                JOIN udas u ON l.uda_id = u.id
                JOIN modules m ON u.module_id = m.id
                WHERE m.anno_corso IS NOT NULL
                ON DUPLICATE KEY UPDATE conoscenza_id = VALUES(conoscenza_id), anno_corso = VALUES(anno_corso);
            ";
            $this->conn->exec($sql_conoscenze_anni);

            // 3. Recalculate and insert for Abilità (years)
            $sql_abilita_anni = "
                INSERT INTO abilita_anni_corso (abilita_id, anno_corso)
                SELECT DISTINCT la.abilita_id, m.anno_corso
                FROM lezione_abilita la
                JOIN lessons l ON la.lezione_id = l.id
                JOIN udas u ON l.uda_id = u.id
                JOIN modules m ON u.module_id = m.id
                WHERE m.anno_corso IS NOT NULL
                ON DUPLICATE KEY UPDATE abilita_id = VALUES(abilita_id), anno_corso = VALUES(anno_corso);
            ";
            $this->conn->exec($sql_abilita_anni);

            // 4. Recalculate and insert for Competenze (years)
            $sql_competenze_anni = "
                INSERT INTO competenza_anni_corso (competenza_id, anno_corso)
                SELECT DISTINCT competenza_id, anno_corso FROM (
                    SELECT cc.competenza_id, cac.anno_corso
                    FROM competenza_conoscenze cc
                    JOIN conoscenza_anni_corso cac ON cc.conoscenza_id = cac.conoscenza_id
                    UNION
                    SELECT ca.competenza_id, aac.anno_corso
                    FROM competenza_abilita ca
                    JOIN abilita_anni_corso aac ON ca.abilita_id = aac.abilita_id
                ) as anni_ereditati
                ON DUPLICATE KEY UPDATE competenza_id = VALUES(competenza_id), anno_corso = VALUES(anno_corso);
            ";
            $this->conn->exec($sql_competenze_anni);

            // --- DISCIPLINES ---

            // 1. Clear existing associations for disciplines
            $this->conn->exec('DELETE FROM competenza_discipline');
            $this->conn->exec('DELETE FROM conoscenza_discipline');
            $this->conn->exec('DELETE FROM abilita_discipline');

            // 2. Recalculate and insert for Conoscenze (disciplines)
            $sql_conoscenze_disc = "
                INSERT INTO conoscenza_discipline (conoscenza_id, disciplina_id)
                SELECT DISTINCT lc.conoscenza_id, m.disciplina_id
                FROM lezione_conoscenze lc
                JOIN lessons l ON lc.lezione_id = l.id
                JOIN udas u ON l.uda_id = u.id
                JOIN modules m ON u.module_id = m.id
                WHERE m.disciplina_id IS NOT NULL
                ON DUPLICATE KEY UPDATE conoscenza_id = VALUES(conoscenza_id), disciplina_id = VALUES(disciplina_id);
            ";
            $this->conn->exec($sql_conoscenze_disc);

            // 3. Recalculate and insert for Abilità (disciplines)
            $sql_abilita_disc = "
                INSERT INTO abilita_discipline (abilita_id, disciplina_id)
                SELECT DISTINCT la.abilita_id, m.disciplina_id
                FROM lezione_abilita la
                JOIN lessons l ON la.lezione_id = l.id
                JOIN udas u ON l.uda_id = u.id
                JOIN modules m ON u.module_id = m.id
                WHERE m.disciplina_id IS NOT NULL
                ON DUPLICATE KEY UPDATE abilita_id = VALUES(abilita_id), disciplina_id = VALUES(disciplina_id);
            ";
            $this->conn->exec($sql_abilita_disc);

            // 4. Recalculate and insert for Competenze (disciplines)
            $sql_competenze_disc = "
                INSERT INTO competenza_discipline (competenza_id, disciplina_id)
                SELECT DISTINCT competenza_id, disciplina_id FROM (
                    SELECT cc.competenza_id, cd.disciplina_id
                    FROM competenza_conoscenze cc
                    JOIN conoscenza_discipline cd ON cc.conoscenza_id = cd.conoscenza_id
                    UNION
                    SELECT ca.competenza_id, ad.disciplina_id
                    FROM competenza_abilita ca
                    JOIN abilita_discipline ad ON ca.abilita_id = ad.abilita_id
                ) as discipline_ereditate
                ON DUPLICATE KEY UPDATE competenza_id = VALUES(competenza_id), disciplina_id = VALUES(disciplina_id);
            ";
            $this->conn->exec($sql_competenze_disc);


            return true;
        } catch (Exception $e) {
            // In a real app, you would log the error message.
            // error_log("AnniCorsoManager Error: " . $e->getMessage());
            return false;
        }
    }
}
