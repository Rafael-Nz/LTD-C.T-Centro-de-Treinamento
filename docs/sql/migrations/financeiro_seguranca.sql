-- Aplicar uma vez em banco existente, antes de usar o novo modulo.
-- Se houver transacoes bancarias duplicadas, resolver manualmente antes de aplicar.
-- Nenhum pagamento ou cobranca e excluido por esta migracao.
DELIMITER $$
DROP PROCEDURE IF EXISTS financeiro_validar_migracao$$
CREATE PROCEDURE financeiro_validar_migracao()
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'pagamento' AND column_name = 'chave_idempotencia') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Migracao financeira ja aplicada ou parcial. Verifique antes de continuar.';
    END IF;
    IF EXISTS (SELECT NULLIF(TRIM(transacao_id), '') FROM pagamento WHERE NULLIF(TRIM(transacao_id), '') IS NOT NULL GROUP BY NULLIF(TRIM(transacao_id), '') HAVING COUNT(*) > 1) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Existem referencias bancarias duplicadas. Revise antes da migracao.';
    END IF;
END$$
CALL financeiro_validar_migracao()$$
DROP PROCEDURE financeiro_validar_migracao$$
DELIMITER ;
ALTER TABLE contrato
    ADD COLUMN periodicidade ENUM('mensal','trimestral','semestral','anual','avulso') NOT NULL DEFAULT 'avulso',
    ADD COLUMN geracao_automatica BOOLEAN NOT NULL DEFAULT FALSE;
UPDATE contrato c JOIN plano p ON p.id = c.plano_id SET c.periodicidade = p.periodicidade;
ALTER TABLE cobranca
    ADD COLUMN cancelada_em DATETIME NULL,
    ADD COLUMN cancelada_por INT NULL,
    ADD COLUMN motivo_cancelamento VARCHAR(500) NULL;
ALTER TABLE pagamento
    ADD COLUMN transacao_unica VARCHAR(120) COLLATE utf8mb4_bin GENERATED ALWAYS AS (NULLIF(TRIM(transacao_id), '')) STORED,
    ADD COLUMN chave_idempotencia VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL,
    ADD COLUMN requisicao_hash CHAR(64) NULL,
    ADD COLUMN estornado_em DATETIME NULL,
    ADD COLUMN estornado_por INT NULL,
    ADD COLUMN motivo_estorno VARCHAR(500) NULL,
    ADD UNIQUE KEY uq_pagamento_operacao (chave_idempotencia),
    ADD UNIQUE KEY uq_pagamento_transacao (transacao_unica);

-- Gatilhos financeiros
DELIMITER $$
DROP TRIGGER IF EXISTS audit_contrato_insert$$
CREATE TRIGGER audit_contrato_insert AFTER INSERT ON `contrato` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'contract_created', 'insert', 'financeiro', 'contrato', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'aluno_id', NEW.`aluno_id`, 'plano_id', NEW.`plano_id`, 'data_inicio', NEW.`data_inicio`, 'data_fim', NEW.`data_fim`, 'dia_vencimento', NEW.`dia_vencimento`, 'valor_contratado', NEW.`valor_contratado`, 'desconto', NEW.`desconto`, 'multa_percentual', NEW.`multa_percentual`, 'juros_percentual', NEW.`juros_percentual`, 'status', NEW.`status`, 'periodicidade', NEW.`periodicidade`, 'geracao_automatica', NEW.`geracao_automatica`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_contrato_update$$
CREATE TRIGGER audit_contrato_update AFTER UPDATE ON `contrato` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`aluno_id` <=> BINARY NEW.`aluno_id`) OR NOT (BINARY OLD.`plano_id` <=> BINARY NEW.`plano_id`) OR NOT (BINARY OLD.`data_inicio` <=> BINARY NEW.`data_inicio`) OR NOT (BINARY OLD.`data_fim` <=> BINARY NEW.`data_fim`) OR NOT (BINARY OLD.`dia_vencimento` <=> BINARY NEW.`dia_vencimento`) OR NOT (BINARY OLD.`valor_contratado` <=> BINARY NEW.`valor_contratado`) OR NOT (BINARY OLD.`desconto` <=> BINARY NEW.`desconto`) OR NOT (BINARY OLD.`multa_percentual` <=> BINARY NEW.`multa_percentual`) OR NOT (BINARY OLD.`juros_percentual` <=> BINARY NEW.`juros_percentual`) OR NOT (BINARY OLD.`status` <=> BINARY NEW.`status`) OR NOT (BINARY OLD.`periodicidade` <=> BINARY NEW.`periodicidade`) OR NOT (BINARY OLD.`geracao_automatica` <=> BINARY NEW.`geracao_automatica`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, IF(NOT (OLD.status <=> NEW.status), IF(NEW.status IN ('cancelado', 'cancelada'), 'contract_cancelled', 'contract_status_changed'), 'contract_updated'), 'update', 'financeiro', 'contrato', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'aluno_id', OLD.`aluno_id`, 'plano_id', OLD.`plano_id`, 'data_inicio', OLD.`data_inicio`, 'data_fim', OLD.`data_fim`, 'dia_vencimento', OLD.`dia_vencimento`, 'valor_contratado', OLD.`valor_contratado`, 'desconto', OLD.`desconto`, 'multa_percentual', OLD.`multa_percentual`, 'juros_percentual', OLD.`juros_percentual`, 'status', OLD.`status`, 'periodicidade', OLD.`periodicidade`, 'geracao_automatica', OLD.`geracao_automatica`), JSON_OBJECT('id', NEW.`id`, 'aluno_id', NEW.`aluno_id`, 'plano_id', NEW.`plano_id`, 'data_inicio', NEW.`data_inicio`, 'data_fim', NEW.`data_fim`, 'dia_vencimento', NEW.`dia_vencimento`, 'valor_contratado', NEW.`valor_contratado`, 'desconto', NEW.`desconto`, 'multa_percentual', NEW.`multa_percentual`, 'juros_percentual', NEW.`juros_percentual`, 'status', NEW.`status`, 'periodicidade', NEW.`periodicidade`, 'geracao_automatica', NEW.`geracao_automatica`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_contrato_delete$$
CREATE TRIGGER audit_contrato_delete AFTER DELETE ON `contrato` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'contract_deleted', 'delete', 'financeiro', 'contrato', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'aluno_id', OLD.`aluno_id`, 'plano_id', OLD.`plano_id`, 'data_inicio', OLD.`data_inicio`, 'data_fim', OLD.`data_fim`, 'dia_vencimento', OLD.`dia_vencimento`, 'valor_contratado', OLD.`valor_contratado`, 'desconto', OLD.`desconto`, 'multa_percentual', OLD.`multa_percentual`, 'juros_percentual', OLD.`juros_percentual`, 'status', OLD.`status`, 'periodicidade', OLD.`periodicidade`, 'geracao_automatica', OLD.`geracao_automatica`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_cobranca_insert$$
CREATE TRIGGER audit_cobranca_insert AFTER INSERT ON `cobranca` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'invoice_created', 'insert', 'financeiro', 'cobranca', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'contrato_id', NEW.`contrato_id`, 'aluno_id', NEW.`aluno_id`, 'competencia', NEW.`competencia`, 'descricao', NEW.`descricao`, 'valor_original', NEW.`valor_original`, 'desconto', NEW.`desconto`, 'multa', NEW.`multa`, 'juros', NEW.`juros`, 'valor_final', NEW.`valor_final`, 'data_vencimento', NEW.`data_vencimento`, 'status', NEW.`status`, 'cancelada_em', NEW.`cancelada_em`, 'cancelada_por', NEW.`cancelada_por`, 'motivo_cancelamento', NEW.`motivo_cancelamento`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_cobranca_update$$
CREATE TRIGGER audit_cobranca_update AFTER UPDATE ON `cobranca` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`contrato_id` <=> BINARY NEW.`contrato_id`) OR NOT (BINARY OLD.`aluno_id` <=> BINARY NEW.`aluno_id`) OR NOT (BINARY OLD.`competencia` <=> BINARY NEW.`competencia`) OR NOT (BINARY OLD.`descricao` <=> BINARY NEW.`descricao`) OR NOT (BINARY OLD.`valor_original` <=> BINARY NEW.`valor_original`) OR NOT (BINARY OLD.`desconto` <=> BINARY NEW.`desconto`) OR NOT (BINARY OLD.`multa` <=> BINARY NEW.`multa`) OR NOT (BINARY OLD.`juros` <=> BINARY NEW.`juros`) OR NOT (BINARY OLD.`valor_final` <=> BINARY NEW.`valor_final`) OR NOT (BINARY OLD.`data_vencimento` <=> BINARY NEW.`data_vencimento`) OR NOT (BINARY OLD.`status` <=> BINARY NEW.`status`) OR NOT (BINARY OLD.`cancelada_em` <=> BINARY NEW.`cancelada_em`) OR NOT (BINARY OLD.`cancelada_por` <=> BINARY NEW.`cancelada_por`) OR NOT (BINARY OLD.`motivo_cancelamento` <=> BINARY NEW.`motivo_cancelamento`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, IF(NOT (OLD.status <=> NEW.status), IF(NEW.status IN ('cancelado', 'cancelada'), 'invoice_cancelled', IF(NEW.status = 'paga', 'invoice_paid', 'invoice_status_changed')), 'invoice_updated'), 'update', 'financeiro', 'cobranca', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'contrato_id', OLD.`contrato_id`, 'aluno_id', OLD.`aluno_id`, 'competencia', OLD.`competencia`, 'descricao', OLD.`descricao`, 'valor_original', OLD.`valor_original`, 'desconto', OLD.`desconto`, 'multa', OLD.`multa`, 'juros', OLD.`juros`, 'valor_final', OLD.`valor_final`, 'data_vencimento', OLD.`data_vencimento`, 'status', OLD.`status`, 'cancelada_em', OLD.`cancelada_em`, 'cancelada_por', OLD.`cancelada_por`, 'motivo_cancelamento', OLD.`motivo_cancelamento`), JSON_OBJECT('id', NEW.`id`, 'contrato_id', NEW.`contrato_id`, 'aluno_id', NEW.`aluno_id`, 'competencia', NEW.`competencia`, 'descricao', NEW.`descricao`, 'valor_original', NEW.`valor_original`, 'desconto', NEW.`desconto`, 'multa', NEW.`multa`, 'juros', NEW.`juros`, 'valor_final', NEW.`valor_final`, 'data_vencimento', NEW.`data_vencimento`, 'status', NEW.`status`, 'cancelada_em', NEW.`cancelada_em`, 'cancelada_por', NEW.`cancelada_por`, 'motivo_cancelamento', NEW.`motivo_cancelamento`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_cobranca_delete$$
CREATE TRIGGER audit_cobranca_delete AFTER DELETE ON `cobranca` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'invoice_deleted', 'delete', 'financeiro', 'cobranca', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'contrato_id', OLD.`contrato_id`, 'aluno_id', OLD.`aluno_id`, 'competencia', OLD.`competencia`, 'descricao', OLD.`descricao`, 'valor_original', OLD.`valor_original`, 'desconto', OLD.`desconto`, 'multa', OLD.`multa`, 'juros', OLD.`juros`, 'valor_final', OLD.`valor_final`, 'data_vencimento', OLD.`data_vencimento`, 'status', OLD.`status`, 'cancelada_em', OLD.`cancelada_em`, 'cancelada_por', OLD.`cancelada_por`, 'motivo_cancelamento', OLD.`motivo_cancelamento`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_pagamento_insert$$
CREATE TRIGGER audit_pagamento_insert AFTER INSERT ON `pagamento` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'payment_created', 'insert', 'financeiro', 'pagamento', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'cobranca_id', NEW.`cobranca_id`, 'valor_pago', NEW.`valor_pago`, 'data_pagamento', NEW.`data_pagamento`, 'forma_pagamento', NEW.`forma_pagamento`, 'registrado_por', NEW.`registrado_por`, 'estornado_em', NEW.`estornado_em`, 'estornado_por', NEW.`estornado_por`, 'motivo_estorno', NEW.`motivo_estorno`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_pagamento_update$$
CREATE TRIGGER audit_pagamento_update AFTER UPDATE ON `pagamento` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`cobranca_id` <=> BINARY NEW.`cobranca_id`) OR NOT (BINARY OLD.`valor_pago` <=> BINARY NEW.`valor_pago`) OR NOT (BINARY OLD.`data_pagamento` <=> BINARY NEW.`data_pagamento`) OR NOT (BINARY OLD.`forma_pagamento` <=> BINARY NEW.`forma_pagamento`) OR NOT (BINARY OLD.`registrado_por` <=> BINARY NEW.`registrado_por`) OR NOT (BINARY OLD.`estornado_em` <=> BINARY NEW.`estornado_em`) OR NOT (BINARY OLD.`estornado_por` <=> BINARY NEW.`estornado_por`) OR NOT (BINARY OLD.`motivo_estorno` <=> BINARY NEW.`motivo_estorno`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, IF(OLD.estornado_em IS NULL AND NEW.estornado_em IS NOT NULL, 'payment_reversed', 'payment_updated'), 'update', 'financeiro', 'pagamento', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'cobranca_id', OLD.`cobranca_id`, 'valor_pago', OLD.`valor_pago`, 'data_pagamento', OLD.`data_pagamento`, 'forma_pagamento', OLD.`forma_pagamento`, 'registrado_por', OLD.`registrado_por`, 'estornado_em', OLD.`estornado_em`, 'estornado_por', OLD.`estornado_por`, 'motivo_estorno', OLD.`motivo_estorno`), JSON_OBJECT('id', NEW.`id`, 'cobranca_id', NEW.`cobranca_id`, 'valor_pago', NEW.`valor_pago`, 'data_pagamento', NEW.`data_pagamento`, 'forma_pagamento', NEW.`forma_pagamento`, 'registrado_por', NEW.`registrado_por`, 'estornado_em', NEW.`estornado_em`, 'estornado_por', NEW.`estornado_por`, 'motivo_estorno', NEW.`motivo_estorno`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_pagamento_delete$$
CREATE TRIGGER audit_pagamento_delete AFTER DELETE ON `pagamento` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'payment_deleted', 'delete', 'financeiro', 'pagamento', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'cobranca_id', OLD.`cobranca_id`, 'valor_pago', OLD.`valor_pago`, 'data_pagamento', OLD.`data_pagamento`, 'forma_pagamento', OLD.`forma_pagamento`, 'registrado_por', OLD.`registrado_por`, 'estornado_em', OLD.`estornado_em`, 'estornado_por', OLD.`estornado_por`, 'motivo_estorno', OLD.`motivo_estorno`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$
DELIMITER ;
