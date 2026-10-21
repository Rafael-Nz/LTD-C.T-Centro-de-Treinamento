-- ==============================================================================
-- Migração: Triggers de Atualização Dinâmica de Cobranças
-- Sistema CTT - Centro de Treinamento
-- ==============================================================================

DROP TRIGGER IF EXISTS trg_cobranca_vencida_insert;
DELIMITER $$
CREATE TRIGGER trg_cobranca_vencida_insert
BEFORE INSERT ON cobranca
FOR EACH ROW
BEGIN
    IF NEW.status = 'aberta' AND NEW.data_vencimento < CURDATE() THEN
        SET NEW.status = 'vencida';
    END IF;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_cobranca_vencida_update;
DELIMITER $$
CREATE TRIGGER trg_cobranca_vencida_update
BEFORE UPDATE ON cobranca
FOR EACH ROW
BEGIN
    IF NEW.status = 'aberta' AND NEW.data_vencimento < CURDATE() THEN
        SET NEW.status = 'vencida';
    END IF;
END$$
DELIMITER ;
