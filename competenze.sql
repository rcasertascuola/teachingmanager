-- Inserimento delle tipologie di competenze (ignora se già presenti)
INSERT IGNORE INTO `tipologie_competenze` (`nome`) VALUES
('Apprendimento permanente'),
('Di indirizzo'),
('Disciplinari (sistemi e reti)'),
('Disciplinari (tep)'),
('Disciplinari (ia)');

-- Inserimento delle competenze - Apprendimento permanente
INSERT INTO `competenze` (`nome`, `tipologia_id`) VALUES
('Competenza alfabetica funzionale', (SELECT id FROM tipologie_competenze WHERE nome = 'Apprendimento permanente')),
('Competenza multilinguistica', (SELECT id FROM tipologie_competenze WHERE nome = 'Apprendimento permanente')),
('Competenza matematica e competenza in scienze, tecnologie e ingegneria', (SELECT id FROM tipologie_competenze WHERE nome = 'Apprendimento permanente')),
('Competenza digitale', (SELECT id FROM tipologie_competenze WHERE nome = 'Apprendimento permanente')),
('Competenza personale, sociale e capacità di imparare a imparare', (SELECT id FROM tipologie_competenze WHERE nome = 'Apprendimento permanente')),
('Competenza in materia di cittadinanza', (SELECT id FROM tipologie_competenze WHERE nome = 'Apprendimento permanente')),
('Competenza imprenditoriale', (SELECT id FROM tipologie_competenze WHERE nome = 'Apprendimento permanente')),
('Competenza in materia di consapevolezza ed espressione culturali', (SELECT id FROM tipologie_competenze WHERE nome = 'Apprendimento permanente')),
('Competenze green', (SELECT id FROM tipologie_competenze WHERE nome = 'Apprendimento permanente'));

-- Inserimento delle competenze - Di indirizzo
INSERT INTO `competenze` (`nome`, `tipologia_id`) VALUES
('Sviluppare applicazioni informatiche', (SELECT id FROM tipologie_competenze WHERE nome = 'Di indirizzo')),
('Sviluppare applicazioni informatiche per reti locali o servizi a distanza', (SELECT id FROM tipologie_competenze WHERE nome = 'Di indirizzo')),
('Descrivere e comparare il funzionamento di dispositivi e strumenti informatici; scegliere dispositivi e strumenti in base alle loro caratteristiche funzionali', (SELECT id FROM tipologie_competenze WHERE nome = 'Di indirizzo')),
('Gestire progetti secondo le procedure e gli standard previsti dai sistemi aziendali di gestione della qualità e della sicurezza', (SELECT id FROM tipologie_competenze WHERE nome = 'Di indirizzo'));

-- Inserimento delle competenze - Disciplinari (Sistemi e reti)
INSERT INTO `competenze` (`nome`, `tipologia_id`) VALUES
('Configurare, installare e gestire sistemi di elaborazione dati e reti', (SELECT id FROM tipologie_competenze WHERE nome = 'Disciplinari (sistemi e reti)')),
('Scegliere dispositivi e strumenti in base alle loro caratteristiche funzionali', (SELECT id FROM tipologie_competenze WHERE nome = 'Disciplinari (sistemi e reti)')),
('Descrivere e comparare il funzionamento di dispositivi e strumenti elettronici e di telecomunicazione', (SELECT id FROM tipologie_competenze WHERE nome = 'Disciplinari (sistemi e reti)')),
('Gestire progetti secondo le procedure e gli standard previsti dai sistemi aziendali di gestione della qualità e della sicurezza', (SELECT id FROM tipologie_competenze WHERE nome = 'Disciplinari (sistemi e reti)')),
('Utilizzare le reti e gli strumenti informatici nelle attività di studio, ricerca e approfondimento disciplinare', (SELECT id FROM tipologie_competenze WHERE nome = 'Disciplinari (sistemi e reti)')),
('Analizzare il valore, i limiti e i rischi delle varie soluzioni tecniche per la vita sociale e culturale con particolare attenzione alla sicurezza nei luoghi di vita e di lavoro, alla tutela della persona, dell’ambiente e del territorio', (SELECT id FROM tipologie_competenze WHERE nome = 'Disciplinari (sistemi e reti)'));

-- Inserimento delle competenze - Disciplinari (TeP)
INSERT INTO `competenze` (`nome`, `tipologia_id`) VALUES
('Sviluppare applicazioni informatiche per reti locali o servizi a distanza', (SELECT id FROM tipologie_competenze WHERE nome = 'Disciplinari (tep)')),
('Scegliere dispositivi e strumenti in base alle loro caratteristiche funzionali', (SELECT id FROM tipologie_competenze WHERE nome = 'Disciplinari (tep)')),
('Gestire progetti secondo le procedure e gli standard previsti dai sistemi aziendali di gestione della qualità e della sicurezza', (SELECT id FROM tipologie_competenze WHERE nome = 'Disciplinari (tep)')),
('Gestire processi produttivi correlati a funzioni aziendali', (SELECT id FROM tipologie_competenze WHERE nome = 'Disciplinari (tep)')),
('Configurare, installare e gestire sistemi di elaborazione dati e reti', (SELECT id FROM tipologie_competenze WHERE nome = 'Disciplinari (tep)')),
('Redigere relazioni tecniche e documentare le attività individuali e di gruppo relative a situazioni professionali', (SELECT id FROM tipologie_competenze WHERE nome = 'Disciplinari (tep)'));

-- Inserimento delle competenze - Disciplinari (IA)
INSERT INTO `competenze` (`nome`, `tipologia_id`) VALUES
('Sviluppare applicazioni informatiche per reti locali o servizi a distanza', (SELECT id FROM tipologie_competenze WHERE nome = 'Disciplinari (ia)')),
('Configurare, installare e gestire sistemi di elaborazione dati e reti', (SELECT id FROM tipologie_competenze WHERE nome = 'Disciplinari (ia)')),
('Redigere relazioni tecniche e documentare le attività individuali e di gruppo relative a situazioni professionali', (SELECT id FROM tipologie_competenze WHERE nome = 'Disciplinari (ia)'));
