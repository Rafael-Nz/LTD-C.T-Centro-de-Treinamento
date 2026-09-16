-- Estrutura e triggers da auditoria. Aplicar no banco existente, sem recria-lo.
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED PRIMARY KEY,
    hash_version TINYINT UNSIGNED NOT NULL DEFAULT 1,
    chain_id CHAR(32) NOT NULL DEFAULT '',
    previous_hash CHAR(64) NOT NULL DEFAULT '',
    row_hash CHAR(64) NOT NULL DEFAULT '',
    user_id BIGINT UNSIGNED NULL,
    user_name VARCHAR(201) NULL,
    action VARCHAR(50) NOT NULL,
    operation VARCHAR(10) NOT NULL,
    module VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NULL,
    entity_id BIGINT UNSIGNED NULL,
    old_data JSON NULL,
    new_data JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(512) NULL,
    request_id CHAR(32) NULL,
    correlation_id CHAR(32) NULL,
    http_method VARCHAR(10) NULL,
    route VARCHAR(255) NULL,
    context_data JSON NULL,
    result VARCHAR(20) NOT NULL DEFAULT 'success',
    http_status SMALLINT UNSIGNED NULL,
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    INDEX idx_audit_user (user_id, created_at),
    INDEX idx_audit_action (action, created_at),
    INDEX idx_audit_module (module, created_at),
    INDEX idx_audit_entity (entity_type, entity_id, created_at),
    INDEX idx_audit_created (created_at),
    INDEX idx_audit_request (request_id),
    INDEX idx_audit_correlation (correlation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_chain_state (
    id TINYINT UNSIGNED PRIMARY KEY,
    chain_id CHAR(32) NOT NULL,
    last_id BIGINT UNSIGNED NOT NULL,
    last_hash CHAR(64) NOT NULL,
    CHECK (id = 1)
) ENGINE=InnoDB;
INSERT IGNORE INTO audit_chain_state (id, chain_id, last_id, last_hash)
VALUES (1, REPLACE(UUID(), '-', ''), 0, REPEAT('0', 64));

DELIMITER $$
DROP TRIGGER IF EXISTS audit_logs_chain_insert$$
CREATE TRIGGER audit_logs_chain_insert BEFORE INSERT ON audit_logs FOR EACH ROW
BEGIN
    DECLARE v_id BIGINT UNSIGNED DEFAULT NULL;
    DECLARE v_hash CHAR(64);
    DECLARE v_chain CHAR(32);
    -- Bloqueio exclusivo transacional: nenhuma bifurcacao, mesmo entre conexoes.
    SELECT last_id, last_hash, chain_id INTO v_id, v_hash, v_chain
    FROM audit_chain_state WHERE id = 1 FOR UPDATE;
    IF v_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cabeca da auditoria ausente';
    END IF;
    SET NEW.action = LOWER(NEW.action);
    SET NEW.operation = LOWER(NEW.operation);
    SET NEW.id = v_id + 1;
    SET NEW.hash_version = 1;
    SET NEW.chain_id = v_chain;
    SET NEW.previous_hash = v_hash;
    SET NEW.created_at = UTC_TIMESTAMP(6);
    SET NEW.request_id = COALESCE(NEW.request_id, @audit_request_id, REPLACE(UUID(), '-', ''));
    SET NEW.correlation_id = COALESCE(NEW.correlation_id, @audit_correlation_id, NEW.request_id);
    SET NEW.http_method = COALESCE(NEW.http_method, @audit_http_method);
    SET NEW.route = COALESCE(NEW.route, @audit_route);
    SET NEW.context_data = COALESCE(NEW.context_data, @audit_context_data);
    SET NEW.row_hash = SHA2(CONCAT('audit-v1|', IF(NEW.`hash_version` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`hash_version` AS BINARY), 256))), IF(NEW.`chain_id` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`chain_id` AS BINARY), 256))), IF(NEW.`id` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`id` AS BINARY), 256))), IF(NEW.`previous_hash` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`previous_hash` AS BINARY), 256))), IF(NEW.`user_id` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`user_id` AS BINARY), 256))), IF(NEW.`user_name` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`user_name` AS BINARY), 256))), IF(NEW.`action` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`action` AS BINARY), 256))), IF(NEW.`operation` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`operation` AS BINARY), 256))), IF(NEW.`module` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`module` AS BINARY), 256))), IF(NEW.`entity_type` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`entity_type` AS BINARY), 256))), IF(NEW.`entity_id` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`entity_id` AS BINARY), 256))), IF(NEW.`old_data` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`old_data` AS BINARY), 256))), IF(NEW.`new_data` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`new_data` AS BINARY), 256))), IF(NEW.`ip_address` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`ip_address` AS BINARY), 256))), IF(NEW.`user_agent` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`user_agent` AS BINARY), 256))), IF(NEW.`request_id` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`request_id` AS BINARY), 256))), IF(NEW.`correlation_id` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`correlation_id` AS BINARY), 256))), IF(NEW.`http_method` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`http_method` AS BINARY), 256))), IF(NEW.`route` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`route` AS BINARY), 256))), IF(NEW.`context_data` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`context_data` AS BINARY), 256))), IF(NEW.`result` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`result` AS BINARY), 256))), IF(NEW.`http_status` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`http_status` AS BINARY), 256))), IF(NEW.`created_at` IS NULL, 'N', CONCAT('S', SHA2(CAST(NEW.`created_at` AS BINARY), 256)))), 256);
    IF NEW.row_hash IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Hash de auditoria indisponivel';
    END IF;
    UPDATE audit_chain_state SET last_id = NEW.id, last_hash = NEW.row_hash WHERE id = 1;
END$$

DROP TRIGGER IF EXISTS audit_logs_no_update$$
CREATE TRIGGER audit_logs_no_update BEFORE UPDATE ON audit_logs FOR EACH ROW
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Auditoria permite apenas insercao'$$

DROP TRIGGER IF EXISTS audit_logs_no_delete$$
CREATE TRIGGER audit_logs_no_delete BEFORE DELETE ON audit_logs FOR EACH ROW
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Auditoria permite apenas insercao'$$
DROP TRIGGER IF EXISTS audit_usuario_insert$$
CREATE TRIGGER audit_usuario_insert AFTER INSERT ON `usuario` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'user_created', 'insert', 'usuarios', 'usuario', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'nome', NEW.`nome`, 'sobrenome', NEW.`sobrenome`, 'email', NEW.`email`, 'tipo_usuario', NEW.`tipo_usuario`, 'ativo', NEW.`ativo`, 'endereco_id', NEW.`endereco_id`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_usuario_update$$
CREATE TRIGGER audit_usuario_update AFTER UPDATE ON `usuario` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`nome` <=> BINARY NEW.`nome`) OR NOT (BINARY OLD.`sobrenome` <=> BINARY NEW.`sobrenome`) OR NOT (BINARY OLD.`email` <=> BINARY NEW.`email`) OR NOT (BINARY OLD.`tipo_usuario` <=> BINARY NEW.`tipo_usuario`) OR NOT (BINARY OLD.`ativo` <=> BINARY NEW.`ativo`) OR NOT (BINARY OLD.`endereco_id` <=> BINARY NEW.`endereco_id`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, IF(NEW.tipo_usuario = 'aluno', REPLACE(IF(NOT (OLD.tipo_usuario <=> NEW.tipo_usuario), 'role_changed', IF(NOT (OLD.ativo <=> NEW.ativo), 'user_status_changed', 'user_updated')), 'user_', 'student_'), IF(NOT (OLD.tipo_usuario <=> NEW.tipo_usuario), 'role_changed', IF(NOT (OLD.ativo <=> NEW.ativo), 'user_status_changed', 'user_updated'))), 'update', 'usuarios', 'usuario', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'nome', OLD.`nome`, 'sobrenome', OLD.`sobrenome`, 'email', OLD.`email`, 'tipo_usuario', OLD.`tipo_usuario`, 'ativo', OLD.`ativo`, 'endereco_id', OLD.`endereco_id`), JSON_OBJECT('id', NEW.`id`, 'nome', NEW.`nome`, 'sobrenome', NEW.`sobrenome`, 'email', NEW.`email`, 'tipo_usuario', NEW.`tipo_usuario`, 'ativo', NEW.`ativo`, 'endereco_id', NEW.`endereco_id`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
IF NOT (BINARY OLD.senha <=> BINARY NEW.senha) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'password_changed', 'update', 'autenticacao', 'usuario', NEW.id, NULL, NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_usuario_delete$$
CREATE TRIGGER audit_usuario_delete AFTER DELETE ON `usuario` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'user_deleted', 'delete', 'usuarios', 'usuario', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'nome', OLD.`nome`, 'sobrenome', OLD.`sobrenome`, 'email', OLD.`email`, 'tipo_usuario', OLD.`tipo_usuario`, 'ativo', OLD.`ativo`, 'endereco_id', OLD.`endereco_id`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_aluno_insert$$
CREATE TRIGGER audit_aluno_insert AFTER INSERT ON `aluno` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'student_created', 'insert', 'alunos', 'aluno', NEW.`usuario_id`, NULL, JSON_OBJECT('usuario_id', NEW.`usuario_id`, 'data_matricula', NEW.`data_matricula`, 'cadastrado_por', NEW.`cadastrado_por`, 'codigo_matricula', NEW.`codigo_matricula`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_aluno_update$$
CREATE TRIGGER audit_aluno_update AFTER UPDATE ON `aluno` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`usuario_id` <=> BINARY NEW.`usuario_id`) OR NOT (BINARY OLD.`data_matricula` <=> BINARY NEW.`data_matricula`) OR NOT (BINARY OLD.`cadastrado_por` <=> BINARY NEW.`cadastrado_por`) OR NOT (BINARY OLD.`codigo_matricula` <=> BINARY NEW.`codigo_matricula`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'student_updated', 'update', 'alunos', 'aluno', NEW.`usuario_id`, JSON_OBJECT('usuario_id', OLD.`usuario_id`, 'data_matricula', OLD.`data_matricula`, 'cadastrado_por', OLD.`cadastrado_por`, 'codigo_matricula', OLD.`codigo_matricula`), JSON_OBJECT('usuario_id', NEW.`usuario_id`, 'data_matricula', NEW.`data_matricula`, 'cadastrado_por', NEW.`cadastrado_por`, 'codigo_matricula', NEW.`codigo_matricula`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_aluno_delete$$
CREATE TRIGGER audit_aluno_delete AFTER DELETE ON `aluno` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'student_deleted', 'delete', 'alunos', 'aluno', OLD.`usuario_id`, JSON_OBJECT('usuario_id', OLD.`usuario_id`, 'data_matricula', OLD.`data_matricula`, 'cadastrado_por', OLD.`cadastrado_por`, 'codigo_matricula', OLD.`codigo_matricula`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_funcionario_insert$$
CREATE TRIGGER audit_funcionario_insert AFTER INSERT ON `funcionario` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'employee_created', 'insert', 'usuarios', 'funcionario', NEW.`usuario_id`, NULL, JSON_OBJECT('usuario_id', NEW.`usuario_id`, 'cargo_id', NEW.`cargo_id`, 'registro_profissional', NEW.`registro_profissional`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_funcionario_update$$
CREATE TRIGGER audit_funcionario_update AFTER UPDATE ON `funcionario` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`usuario_id` <=> BINARY NEW.`usuario_id`) OR NOT (BINARY OLD.`cargo_id` <=> BINARY NEW.`cargo_id`) OR NOT (BINARY OLD.`registro_profissional` <=> BINARY NEW.`registro_profissional`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, IF(NOT (OLD.cargo_id <=> NEW.cargo_id), 'role_changed', 'employee_updated'), 'update', 'usuarios', 'funcionario', NEW.`usuario_id`, JSON_OBJECT('usuario_id', OLD.`usuario_id`, 'cargo_id', OLD.`cargo_id`, 'registro_profissional', OLD.`registro_profissional`), JSON_OBJECT('usuario_id', NEW.`usuario_id`, 'cargo_id', NEW.`cargo_id`, 'registro_profissional', NEW.`registro_profissional`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_funcionario_delete$$
CREATE TRIGGER audit_funcionario_delete AFTER DELETE ON `funcionario` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'employee_deleted', 'delete', 'usuarios', 'funcionario', OLD.`usuario_id`, JSON_OBJECT('usuario_id', OLD.`usuario_id`, 'cargo_id', OLD.`cargo_id`, 'registro_profissional', OLD.`registro_profissional`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_endereco_insert$$
CREATE TRIGGER audit_endereco_insert AFTER INSERT ON `endereco` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'address_created', 'insert', 'usuarios', 'endereco', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'logradouro', NEW.`logradouro`, 'numero', NEW.`numero`, 'cidade', NEW.`cidade`, 'bairro', NEW.`bairro`, 'cep', NEW.`cep`, 'complemento', NEW.`complemento`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_endereco_update$$
CREATE TRIGGER audit_endereco_update AFTER UPDATE ON `endereco` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`logradouro` <=> BINARY NEW.`logradouro`) OR NOT (BINARY OLD.`numero` <=> BINARY NEW.`numero`) OR NOT (BINARY OLD.`cidade` <=> BINARY NEW.`cidade`) OR NOT (BINARY OLD.`bairro` <=> BINARY NEW.`bairro`) OR NOT (BINARY OLD.`cep` <=> BINARY NEW.`cep`) OR NOT (BINARY OLD.`complemento` <=> BINARY NEW.`complemento`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'address_updated', 'update', 'usuarios', 'endereco', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'logradouro', OLD.`logradouro`, 'numero', OLD.`numero`, 'cidade', OLD.`cidade`, 'bairro', OLD.`bairro`, 'cep', OLD.`cep`, 'complemento', OLD.`complemento`), JSON_OBJECT('id', NEW.`id`, 'logradouro', NEW.`logradouro`, 'numero', NEW.`numero`, 'cidade', NEW.`cidade`, 'bairro', NEW.`bairro`, 'cep', NEW.`cep`, 'complemento', NEW.`complemento`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_endereco_delete$$
CREATE TRIGGER audit_endereco_delete AFTER DELETE ON `endereco` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'address_deleted', 'delete', 'usuarios', 'endereco', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'logradouro', OLD.`logradouro`, 'numero', OLD.`numero`, 'cidade', OLD.`cidade`, 'bairro', OLD.`bairro`, 'cep', OLD.`cep`, 'complemento', OLD.`complemento`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_contato_insert$$
CREATE TRIGGER audit_contato_insert AFTER INSERT ON `contato` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'contact_created', 'insert', 'usuarios', 'contato', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'usuario_id', NEW.`usuario_id`, 'tipo', NEW.`tipo`, 'valor', NEW.`valor`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_contato_update$$
CREATE TRIGGER audit_contato_update AFTER UPDATE ON `contato` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`usuario_id` <=> BINARY NEW.`usuario_id`) OR NOT (BINARY OLD.`tipo` <=> BINARY NEW.`tipo`) OR NOT (BINARY OLD.`valor` <=> BINARY NEW.`valor`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'contact_updated', 'update', 'usuarios', 'contato', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'usuario_id', OLD.`usuario_id`, 'tipo', OLD.`tipo`, 'valor', OLD.`valor`), JSON_OBJECT('id', NEW.`id`, 'usuario_id', NEW.`usuario_id`, 'tipo', NEW.`tipo`, 'valor', NEW.`valor`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_contato_delete$$
CREATE TRIGGER audit_contato_delete AFTER DELETE ON `contato` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'contact_deleted', 'delete', 'usuarios', 'contato', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'usuario_id', OLD.`usuario_id`, 'tipo', OLD.`tipo`, 'valor', OLD.`valor`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_cargo_insert$$
CREATE TRIGGER audit_cargo_insert AFTER INSERT ON `cargo` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'role_created', 'insert', 'usuarios', 'cargo', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'nome', NEW.`nome`, 'descricao', NEW.`descricao`, 'ativo', NEW.`ativo`, 'salario_base', NEW.`salario_base`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_cargo_update$$
CREATE TRIGGER audit_cargo_update AFTER UPDATE ON `cargo` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`nome` <=> BINARY NEW.`nome`) OR NOT (BINARY OLD.`descricao` <=> BINARY NEW.`descricao`) OR NOT (BINARY OLD.`ativo` <=> BINARY NEW.`ativo`) OR NOT (BINARY OLD.`salario_base` <=> BINARY NEW.`salario_base`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, IF(NOT (OLD.ativo <=> NEW.ativo), 'role_status_changed', 'role_updated'), 'update', 'usuarios', 'cargo', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'nome', OLD.`nome`, 'descricao', OLD.`descricao`, 'ativo', OLD.`ativo`, 'salario_base', OLD.`salario_base`), JSON_OBJECT('id', NEW.`id`, 'nome', NEW.`nome`, 'descricao', NEW.`descricao`, 'ativo', NEW.`ativo`, 'salario_base', NEW.`salario_base`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_cargo_delete$$
CREATE TRIGGER audit_cargo_delete AFTER DELETE ON `cargo` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'role_deleted', 'delete', 'usuarios', 'cargo', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'nome', OLD.`nome`, 'descricao', OLD.`descricao`, 'ativo', OLD.`ativo`, 'salario_base', OLD.`salario_base`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_permissao_insert$$
CREATE TRIGGER audit_permissao_insert AFTER INSERT ON `permissao` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'permission_created', 'insert', 'usuarios', 'permissao', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'slug', NEW.`slug`, 'descricao', NEW.`descricao`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_permissao_update$$
CREATE TRIGGER audit_permissao_update AFTER UPDATE ON `permissao` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`slug` <=> BINARY NEW.`slug`) OR NOT (BINARY OLD.`descricao` <=> BINARY NEW.`descricao`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'permission_updated', 'update', 'usuarios', 'permissao', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'slug', OLD.`slug`, 'descricao', OLD.`descricao`), JSON_OBJECT('id', NEW.`id`, 'slug', NEW.`slug`, 'descricao', NEW.`descricao`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_permissao_delete$$
CREATE TRIGGER audit_permissao_delete AFTER DELETE ON `permissao` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'permission_deleted', 'delete', 'usuarios', 'permissao', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'slug', OLD.`slug`, 'descricao', OLD.`descricao`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_cargo_permissao_insert$$
CREATE TRIGGER audit_cargo_permissao_insert AFTER INSERT ON `cargo_permissao` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'permission_changed', 'insert', 'usuarios', 'cargo_permissao', NEW.`cargo_id`, NULL, JSON_OBJECT('cargo_id', NEW.`cargo_id`, 'permissao_id', NEW.`permissao_id`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_cargo_permissao_update$$
CREATE TRIGGER audit_cargo_permissao_update AFTER UPDATE ON `cargo_permissao` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`cargo_id` <=> BINARY NEW.`cargo_id`) OR NOT (BINARY OLD.`permissao_id` <=> BINARY NEW.`permissao_id`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'permission_changed', 'update', 'usuarios', 'cargo_permissao', NEW.`cargo_id`, JSON_OBJECT('cargo_id', OLD.`cargo_id`, 'permissao_id', OLD.`permissao_id`), JSON_OBJECT('cargo_id', NEW.`cargo_id`, 'permissao_id', NEW.`permissao_id`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_cargo_permissao_delete$$
CREATE TRIGGER audit_cargo_permissao_delete AFTER DELETE ON `cargo_permissao` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'permission_changed', 'delete', 'usuarios', 'cargo_permissao', OLD.`cargo_id`, JSON_OBJECT('cargo_id', OLD.`cargo_id`, 'permissao_id', OLD.`permissao_id`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_aluno_turma_insert$$
CREATE TRIGGER audit_aluno_turma_insert AFTER INSERT ON `aluno_turma` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'enrollment_created', 'insert', 'matriculas', 'aluno_turma', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'aluno_id', NEW.`aluno_id`, 'turma_id', NEW.`turma_id`, 'data_inscricao', NEW.`data_inscricao`, 'ativo', NEW.`ativo`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_aluno_turma_update$$
CREATE TRIGGER audit_aluno_turma_update AFTER UPDATE ON `aluno_turma` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`aluno_id` <=> BINARY NEW.`aluno_id`) OR NOT (BINARY OLD.`turma_id` <=> BINARY NEW.`turma_id`) OR NOT (BINARY OLD.`data_inscricao` <=> BINARY NEW.`data_inscricao`) OR NOT (BINARY OLD.`ativo` <=> BINARY NEW.`ativo`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, IF(OLD.ativo = 1 AND NEW.ativo = 0, 'enrollment_cancelled', IF(NOT (OLD.ativo <=> NEW.ativo), 'enrollment_status_changed', 'enrollment_updated')), 'update', 'matriculas', 'aluno_turma', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'aluno_id', OLD.`aluno_id`, 'turma_id', OLD.`turma_id`, 'data_inscricao', OLD.`data_inscricao`, 'ativo', OLD.`ativo`), JSON_OBJECT('id', NEW.`id`, 'aluno_id', NEW.`aluno_id`, 'turma_id', NEW.`turma_id`, 'data_inscricao', NEW.`data_inscricao`, 'ativo', NEW.`ativo`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_aluno_turma_delete$$
CREATE TRIGGER audit_aluno_turma_delete AFTER DELETE ON `aluno_turma` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'enrollment_deleted', 'delete', 'matriculas', 'aluno_turma', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'aluno_id', OLD.`aluno_id`, 'turma_id', OLD.`turma_id`, 'data_inscricao', OLD.`data_inscricao`, 'ativo', OLD.`ativo`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_servico_insert$$
CREATE TRIGGER audit_servico_insert AFTER INSERT ON `servico` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'service_created', 'insert', 'financeiro', 'servico', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'nome', NEW.`nome`, 'tipo', NEW.`tipo`, 'valor_base', NEW.`valor_base`, 'recorrente', NEW.`recorrente`, 'ativo', NEW.`ativo`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_servico_update$$
CREATE TRIGGER audit_servico_update AFTER UPDATE ON `servico` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`nome` <=> BINARY NEW.`nome`) OR NOT (BINARY OLD.`tipo` <=> BINARY NEW.`tipo`) OR NOT (BINARY OLD.`valor_base` <=> BINARY NEW.`valor_base`) OR NOT (BINARY OLD.`recorrente` <=> BINARY NEW.`recorrente`) OR NOT (BINARY OLD.`ativo` <=> BINARY NEW.`ativo`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, IF(NOT (OLD.ativo <=> NEW.ativo), 'service_status_changed', 'service_updated'), 'update', 'financeiro', 'servico', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'nome', OLD.`nome`, 'tipo', OLD.`tipo`, 'valor_base', OLD.`valor_base`, 'recorrente', OLD.`recorrente`, 'ativo', OLD.`ativo`), JSON_OBJECT('id', NEW.`id`, 'nome', NEW.`nome`, 'tipo', NEW.`tipo`, 'valor_base', NEW.`valor_base`, 'recorrente', NEW.`recorrente`, 'ativo', NEW.`ativo`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_servico_delete$$
CREATE TRIGGER audit_servico_delete AFTER DELETE ON `servico` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'service_deleted', 'delete', 'financeiro', 'servico', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'nome', OLD.`nome`, 'tipo', OLD.`tipo`, 'valor_base', OLD.`valor_base`, 'recorrente', OLD.`recorrente`, 'ativo', OLD.`ativo`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_plano_insert$$
CREATE TRIGGER audit_plano_insert AFTER INSERT ON `plano` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'plan_created', 'insert', 'configuracao', 'plano', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'nome', NEW.`nome`, 'periodicidade', NEW.`periodicidade`, 'valor', NEW.`valor`, 'ativo', NEW.`ativo`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_plano_update$$
CREATE TRIGGER audit_plano_update AFTER UPDATE ON `plano` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`nome` <=> BINARY NEW.`nome`) OR NOT (BINARY OLD.`periodicidade` <=> BINARY NEW.`periodicidade`) OR NOT (BINARY OLD.`valor` <=> BINARY NEW.`valor`) OR NOT (BINARY OLD.`ativo` <=> BINARY NEW.`ativo`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, IF(NOT (OLD.ativo <=> NEW.ativo), 'plan_status_changed', 'plan_updated'), 'update', 'configuracao', 'plano', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'nome', OLD.`nome`, 'periodicidade', OLD.`periodicidade`, 'valor', OLD.`valor`, 'ativo', OLD.`ativo`), JSON_OBJECT('id', NEW.`id`, 'nome', NEW.`nome`, 'periodicidade', NEW.`periodicidade`, 'valor', NEW.`valor`, 'ativo', NEW.`ativo`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_plano_delete$$
CREATE TRIGGER audit_plano_delete AFTER DELETE ON `plano` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'plan_deleted', 'delete', 'configuracao', 'plano', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'nome', OLD.`nome`, 'periodicidade', OLD.`periodicidade`, 'valor', OLD.`valor`, 'ativo', OLD.`ativo`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_plano_item_insert$$
CREATE TRIGGER audit_plano_item_insert AFTER INSERT ON `plano_item` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'plan_item_created', 'insert', 'configuracao', 'plano_item', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'plano_id', NEW.`plano_id`, 'servico_id', NEW.`servico_id`, 'quantidade', NEW.`quantidade`, 'valor_unitario', NEW.`valor_unitario`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_plano_item_update$$
CREATE TRIGGER audit_plano_item_update AFTER UPDATE ON `plano_item` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`plano_id` <=> BINARY NEW.`plano_id`) OR NOT (BINARY OLD.`servico_id` <=> BINARY NEW.`servico_id`) OR NOT (BINARY OLD.`quantidade` <=> BINARY NEW.`quantidade`) OR NOT (BINARY OLD.`valor_unitario` <=> BINARY NEW.`valor_unitario`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'plan_item_updated', 'update', 'configuracao', 'plano_item', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'plano_id', OLD.`plano_id`, 'servico_id', OLD.`servico_id`, 'quantidade', OLD.`quantidade`, 'valor_unitario', OLD.`valor_unitario`), JSON_OBJECT('id', NEW.`id`, 'plano_id', NEW.`plano_id`, 'servico_id', NEW.`servico_id`, 'quantidade', NEW.`quantidade`, 'valor_unitario', NEW.`valor_unitario`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_plano_item_delete$$
CREATE TRIGGER audit_plano_item_delete AFTER DELETE ON `plano_item` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'plan_item_deleted', 'delete', 'configuracao', 'plano_item', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'plano_id', OLD.`plano_id`, 'servico_id', OLD.`servico_id`, 'quantidade', OLD.`quantidade`, 'valor_unitario', OLD.`valor_unitario`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

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

DROP TRIGGER IF EXISTS audit_contrato_item_insert$$
CREATE TRIGGER audit_contrato_item_insert AFTER INSERT ON `contrato_item` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'contract_item_created', 'insert', 'financeiro', 'contrato_item', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'contrato_id', NEW.`contrato_id`, 'servico_id', NEW.`servico_id`, 'descricao', NEW.`descricao`, 'quantidade', NEW.`quantidade`, 'valor_unitario', NEW.`valor_unitario`, 'valor_desconto', NEW.`valor_desconto`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_contrato_item_update$$
CREATE TRIGGER audit_contrato_item_update AFTER UPDATE ON `contrato_item` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`contrato_id` <=> BINARY NEW.`contrato_id`) OR NOT (BINARY OLD.`servico_id` <=> BINARY NEW.`servico_id`) OR NOT (BINARY OLD.`descricao` <=> BINARY NEW.`descricao`) OR NOT (BINARY OLD.`quantidade` <=> BINARY NEW.`quantidade`) OR NOT (BINARY OLD.`valor_unitario` <=> BINARY NEW.`valor_unitario`) OR NOT (BINARY OLD.`valor_desconto` <=> BINARY NEW.`valor_desconto`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'contract_item_updated', 'update', 'financeiro', 'contrato_item', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'contrato_id', OLD.`contrato_id`, 'servico_id', OLD.`servico_id`, 'descricao', OLD.`descricao`, 'quantidade', OLD.`quantidade`, 'valor_unitario', OLD.`valor_unitario`, 'valor_desconto', OLD.`valor_desconto`), JSON_OBJECT('id', NEW.`id`, 'contrato_id', NEW.`contrato_id`, 'servico_id', NEW.`servico_id`, 'descricao', NEW.`descricao`, 'quantidade', NEW.`quantidade`, 'valor_unitario', NEW.`valor_unitario`, 'valor_desconto', NEW.`valor_desconto`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_contrato_item_delete$$
CREATE TRIGGER audit_contrato_item_delete AFTER DELETE ON `contrato_item` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'contract_item_deleted', 'delete', 'financeiro', 'contrato_item', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'contrato_id', OLD.`contrato_id`, 'servico_id', OLD.`servico_id`, 'descricao', OLD.`descricao`, 'quantidade', OLD.`quantidade`, 'valor_unitario', OLD.`valor_unitario`, 'valor_desconto', OLD.`valor_desconto`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
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

DROP TRIGGER IF EXISTS audit_modalidade_insert$$
CREATE TRIGGER audit_modalidade_insert AFTER INSERT ON `modalidade` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'modality_created', 'insert', 'configuracao', 'modalidade', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'nome', NEW.`nome`, 'descricao', NEW.`descricao`, 'ativo', NEW.`ativo`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_modalidade_update$$
CREATE TRIGGER audit_modalidade_update AFTER UPDATE ON `modalidade` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`nome` <=> BINARY NEW.`nome`) OR NOT (BINARY OLD.`descricao` <=> BINARY NEW.`descricao`) OR NOT (BINARY OLD.`ativo` <=> BINARY NEW.`ativo`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, IF(NOT (OLD.ativo <=> NEW.ativo), 'modality_status_changed', 'modality_updated'), 'update', 'configuracao', 'modalidade', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'nome', OLD.`nome`, 'descricao', OLD.`descricao`, 'ativo', OLD.`ativo`), JSON_OBJECT('id', NEW.`id`, 'nome', NEW.`nome`, 'descricao', NEW.`descricao`, 'ativo', NEW.`ativo`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_modalidade_delete$$
CREATE TRIGGER audit_modalidade_delete AFTER DELETE ON `modalidade` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'modality_deleted', 'delete', 'configuracao', 'modalidade', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'nome', OLD.`nome`, 'descricao', OLD.`descricao`, 'ativo', OLD.`ativo`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_treino_insert$$
CREATE TRIGGER audit_treino_insert AFTER INSERT ON `treino` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'workout_created', 'insert', 'treinos', 'treino', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'nome', NEW.`nome`, 'modalidade_id', NEW.`modalidade_id`, 'descricao', NEW.`descricao`, 'ativo', NEW.`ativo`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_treino_update$$
CREATE TRIGGER audit_treino_update AFTER UPDATE ON `treino` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`nome` <=> BINARY NEW.`nome`) OR NOT (BINARY OLD.`modalidade_id` <=> BINARY NEW.`modalidade_id`) OR NOT (BINARY OLD.`descricao` <=> BINARY NEW.`descricao`) OR NOT (BINARY OLD.`ativo` <=> BINARY NEW.`ativo`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, IF(NOT (OLD.ativo <=> NEW.ativo), 'workout_status_changed', 'workout_updated'), 'update', 'treinos', 'treino', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'nome', OLD.`nome`, 'modalidade_id', OLD.`modalidade_id`, 'descricao', OLD.`descricao`, 'ativo', OLD.`ativo`), JSON_OBJECT('id', NEW.`id`, 'nome', NEW.`nome`, 'modalidade_id', NEW.`modalidade_id`, 'descricao', NEW.`descricao`, 'ativo', NEW.`ativo`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_treino_delete$$
CREATE TRIGGER audit_treino_delete AFTER DELETE ON `treino` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'workout_deleted', 'delete', 'treinos', 'treino', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'nome', OLD.`nome`, 'modalidade_id', OLD.`modalidade_id`, 'descricao', OLD.`descricao`, 'ativo', OLD.`ativo`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_espaco_treino_insert$$
CREATE TRIGGER audit_espaco_treino_insert AFTER INSERT ON `espaco_treino` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'location_created', 'insert', 'configuracao', 'espaco_treino', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'nome', NEW.`nome`, 'capacidade_minima', NEW.`capacidade_minima`, 'capacidade_maxima', NEW.`capacidade_maxima`, 'ativo', NEW.`ativo`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_espaco_treino_update$$
CREATE TRIGGER audit_espaco_treino_update AFTER UPDATE ON `espaco_treino` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`nome` <=> BINARY NEW.`nome`) OR NOT (BINARY OLD.`capacidade_minima` <=> BINARY NEW.`capacidade_minima`) OR NOT (BINARY OLD.`capacidade_maxima` <=> BINARY NEW.`capacidade_maxima`) OR NOT (BINARY OLD.`ativo` <=> BINARY NEW.`ativo`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, IF(NOT (OLD.ativo <=> NEW.ativo), 'location_status_changed', 'location_updated'), 'update', 'configuracao', 'espaco_treino', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'nome', OLD.`nome`, 'capacidade_minima', OLD.`capacidade_minima`, 'capacidade_maxima', OLD.`capacidade_maxima`, 'ativo', OLD.`ativo`), JSON_OBJECT('id', NEW.`id`, 'nome', NEW.`nome`, 'capacidade_minima', NEW.`capacidade_minima`, 'capacidade_maxima', NEW.`capacidade_maxima`, 'ativo', NEW.`ativo`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_espaco_treino_delete$$
CREATE TRIGGER audit_espaco_treino_delete AFTER DELETE ON `espaco_treino` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'location_deleted', 'delete', 'configuracao', 'espaco_treino', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'nome', OLD.`nome`, 'capacidade_minima', OLD.`capacidade_minima`, 'capacidade_maxima', OLD.`capacidade_maxima`, 'ativo', OLD.`ativo`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_turma_insert$$
CREATE TRIGGER audit_turma_insert AFTER INSERT ON `turma` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'class_created', 'insert', 'turmas', 'turma', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'nome', NEW.`nome`, 'instrutor_id', NEW.`instrutor_id`, 'capacidade_minima', NEW.`capacidade_minima`, 'capacidade_maxima', NEW.`capacidade_maxima`, 'ativo', NEW.`ativo`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_turma_update$$
CREATE TRIGGER audit_turma_update AFTER UPDATE ON `turma` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`nome` <=> BINARY NEW.`nome`) OR NOT (BINARY OLD.`instrutor_id` <=> BINARY NEW.`instrutor_id`) OR NOT (BINARY OLD.`capacidade_minima` <=> BINARY NEW.`capacidade_minima`) OR NOT (BINARY OLD.`capacidade_maxima` <=> BINARY NEW.`capacidade_maxima`) OR NOT (BINARY OLD.`ativo` <=> BINARY NEW.`ativo`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, IF(NOT (OLD.ativo <=> NEW.ativo), 'class_status_changed', 'class_updated'), 'update', 'turmas', 'turma', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'nome', OLD.`nome`, 'instrutor_id', OLD.`instrutor_id`, 'capacidade_minima', OLD.`capacidade_minima`, 'capacidade_maxima', OLD.`capacidade_maxima`, 'ativo', OLD.`ativo`), JSON_OBJECT('id', NEW.`id`, 'nome', NEW.`nome`, 'instrutor_id', NEW.`instrutor_id`, 'capacidade_minima', NEW.`capacidade_minima`, 'capacidade_maxima', NEW.`capacidade_maxima`, 'ativo', NEW.`ativo`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_turma_delete$$
CREATE TRIGGER audit_turma_delete AFTER DELETE ON `turma` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'class_deleted', 'delete', 'turmas', 'turma', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'nome', OLD.`nome`, 'instrutor_id', OLD.`instrutor_id`, 'capacidade_minima', OLD.`capacidade_minima`, 'capacidade_maxima', OLD.`capacidade_maxima`, 'ativo', OLD.`ativo`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_turma_config_horario_insert$$
CREATE TRIGGER audit_turma_config_horario_insert AFTER INSERT ON `turma_config_horario` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'schedule_created', 'insert', 'turmas', 'turma_config_horario', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'turma_id', NEW.`turma_id`, 'dia_semana', NEW.`dia_semana`, 'hora_inicio', NEW.`hora_inicio`, 'hora_fim', NEW.`hora_fim`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_turma_config_horario_update$$
CREATE TRIGGER audit_turma_config_horario_update AFTER UPDATE ON `turma_config_horario` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`turma_id` <=> BINARY NEW.`turma_id`) OR NOT (BINARY OLD.`dia_semana` <=> BINARY NEW.`dia_semana`) OR NOT (BINARY OLD.`hora_inicio` <=> BINARY NEW.`hora_inicio`) OR NOT (BINARY OLD.`hora_fim` <=> BINARY NEW.`hora_fim`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'schedule_updated', 'update', 'turmas', 'turma_config_horario', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'turma_id', OLD.`turma_id`, 'dia_semana', OLD.`dia_semana`, 'hora_inicio', OLD.`hora_inicio`, 'hora_fim', OLD.`hora_fim`), JSON_OBJECT('id', NEW.`id`, 'turma_id', NEW.`turma_id`, 'dia_semana', NEW.`dia_semana`, 'hora_inicio', NEW.`hora_inicio`, 'hora_fim', NEW.`hora_fim`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_turma_config_horario_delete$$
CREATE TRIGGER audit_turma_config_horario_delete AFTER DELETE ON `turma_config_horario` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'schedule_deleted', 'delete', 'turmas', 'turma_config_horario', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'turma_id', OLD.`turma_id`, 'dia_semana', OLD.`dia_semana`, 'hora_inicio', OLD.`hora_inicio`, 'hora_fim', OLD.`hora_fim`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_treino_agenda_insert$$
CREATE TRIGGER audit_treino_agenda_insert AFTER INSERT ON `treino_agenda` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'workout_session_created', 'insert', 'treinos', 'treino_agenda', NEW.`id`, NULL, JSON_OBJECT('id', NEW.`id`, 'treino_id', NEW.`treino_id`, 'turma_id', NEW.`turma_id`, 'espaco_id', NEW.`espaco_id`, 'instrutor_id', NEW.`instrutor_id`, 'data_hora_inicio', NEW.`data_hora_inicio`, 'data_hora_fim', NEW.`data_hora_fim`, 'status', NEW.`status`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_treino_agenda_update$$
CREATE TRIGGER audit_treino_agenda_update AFTER UPDATE ON `treino_agenda` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`id` <=> BINARY NEW.`id`) OR NOT (BINARY OLD.`treino_id` <=> BINARY NEW.`treino_id`) OR NOT (BINARY OLD.`turma_id` <=> BINARY NEW.`turma_id`) OR NOT (BINARY OLD.`espaco_id` <=> BINARY NEW.`espaco_id`) OR NOT (BINARY OLD.`instrutor_id` <=> BINARY NEW.`instrutor_id`) OR NOT (BINARY OLD.`data_hora_inicio` <=> BINARY NEW.`data_hora_inicio`) OR NOT (BINARY OLD.`data_hora_fim` <=> BINARY NEW.`data_hora_fim`) OR NOT (BINARY OLD.`status` <=> BINARY NEW.`status`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, IF(NOT (OLD.status <=> NEW.status), IF(NEW.status IN ('cancelado', 'cancelada'), 'workout_session_cancelled', 'workout_session_status_changed'), 'workout_session_updated'), 'update', 'treinos', 'treino_agenda', NEW.`id`, JSON_OBJECT('id', OLD.`id`, 'treino_id', OLD.`treino_id`, 'turma_id', OLD.`turma_id`, 'espaco_id', OLD.`espaco_id`, 'instrutor_id', OLD.`instrutor_id`, 'data_hora_inicio', OLD.`data_hora_inicio`, 'data_hora_fim', OLD.`data_hora_fim`, 'status', OLD.`status`), JSON_OBJECT('id', NEW.`id`, 'treino_id', NEW.`treino_id`, 'turma_id', NEW.`turma_id`, 'espaco_id', NEW.`espaco_id`, 'instrutor_id', NEW.`instrutor_id`, 'data_hora_inicio', NEW.`data_hora_inicio`, 'data_hora_fim', NEW.`data_hora_fim`, 'status', NEW.`status`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_treino_agenda_delete$$
CREATE TRIGGER audit_treino_agenda_delete AFTER DELETE ON `treino_agenda` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'workout_session_deleted', 'delete', 'treinos', 'treino_agenda', OLD.`id`, JSON_OBJECT('id', OLD.`id`, 'treino_id', OLD.`treino_id`, 'turma_id', OLD.`turma_id`, 'espaco_id', OLD.`espaco_id`, 'instrutor_id', OLD.`instrutor_id`, 'data_hora_inicio', OLD.`data_hora_inicio`, 'data_hora_fim', OLD.`data_hora_fim`, 'status', OLD.`status`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_presenca_treino_insert$$
CREATE TRIGGER audit_presenca_treino_insert AFTER INSERT ON `presenca_treino` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'attendance_created', 'insert', 'presencas', 'presenca_treino', NEW.`treino_id`, NULL, JSON_OBJECT('treino_id', NEW.`treino_id`, 'aluno_id', NEW.`aluno_id`, 'situacao', NEW.`situacao`, 'checkin_time', NEW.`checkin_time`), @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_presenca_treino_update$$
CREATE TRIGGER audit_presenca_treino_update AFTER UPDATE ON `presenca_treino` FOR EACH ROW
BEGIN
IF NOT (BINARY OLD.`treino_id` <=> BINARY NEW.`treino_id`) OR NOT (BINARY OLD.`aluno_id` <=> BINARY NEW.`aluno_id`) OR NOT (BINARY OLD.`situacao` <=> BINARY NEW.`situacao`) OR NOT (BINARY OLD.`checkin_time` <=> BINARY NEW.`checkin_time`) THEN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'attendance_updated', 'update', 'presencas', 'presenca_treino', NEW.`treino_id`, JSON_OBJECT('treino_id', OLD.`treino_id`, 'aluno_id', OLD.`aluno_id`, 'situacao', OLD.`situacao`, 'checkin_time', OLD.`checkin_time`), JSON_OBJECT('treino_id', NEW.`treino_id`, 'aluno_id', NEW.`aluno_id`, 'situacao', NEW.`situacao`, 'checkin_time', NEW.`checkin_time`), @audit_ip, @audit_user_agent, @audit_request_id);
END IF;
END$$

DROP TRIGGER IF EXISTS audit_presenca_treino_delete$$
CREATE TRIGGER audit_presenca_treino_delete AFTER DELETE ON `presenca_treino` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
    VALUES (@audit_user_id, @audit_user_name, 'attendance_deleted', 'delete', 'presencas', 'presenca_treino', OLD.`treino_id`, JSON_OBJECT('treino_id', OLD.`treino_id`, 'aluno_id', OLD.`aluno_id`, 'situacao', OLD.`situacao`, 'checkin_time', OLD.`checkin_time`), NULL, @audit_ip, @audit_user_agent, @audit_request_id);
END$$

DROP TRIGGER IF EXISTS audit_usuario_cascade$$
CREATE TRIGGER audit_usuario_cascade BEFORE DELETE ON `usuario` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'employee_deleted', 'delete', 'usuarios', 'funcionario', c0.`usuario_id`, JSON_OBJECT('usuario_id', c0.`usuario_id`, 'cargo_id', c0.`cargo_id`, 'registro_profissional', c0.`registro_profissional`), NULL, @audit_ip, @audit_user_agent, @audit_request_id FROM `funcionario` c0 WHERE c0.`usuario_id` = OLD.`id`;
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'workout_session_updated', 'update', 'treinos', 'treino_agenda', c1.`id`, JSON_OBJECT('id', c1.`id`, 'treino_id', c1.`treino_id`, 'turma_id', c1.`turma_id`, 'espaco_id', c1.`espaco_id`, 'instrutor_id', c1.`instrutor_id`, 'data_hora_inicio', c1.`data_hora_inicio`, 'data_hora_fim', c1.`data_hora_fim`, 'status', c1.`status`), JSON_OBJECT('id', c1.`id`, 'treino_id', c1.`treino_id`, 'turma_id', c1.`turma_id`, 'espaco_id', c1.`espaco_id`, 'instrutor_id', NULL, 'data_hora_inicio', c1.`data_hora_inicio`, 'data_hora_fim', c1.`data_hora_fim`, 'status', c1.`status`), @audit_ip, @audit_user_agent, @audit_request_id FROM `funcionario` c0 INNER JOIN `treino_agenda` c1 ON c1.`instrutor_id` = c0.`usuario_id` WHERE c0.`usuario_id` = OLD.`id`;
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'student_deleted', 'delete', 'alunos', 'aluno', c0.`usuario_id`, JSON_OBJECT('usuario_id', c0.`usuario_id`, 'data_matricula', c0.`data_matricula`, 'cadastrado_por', c0.`cadastrado_por`, 'codigo_matricula', c0.`codigo_matricula`), NULL, @audit_ip, @audit_user_agent, @audit_request_id FROM `aluno` c0 WHERE c0.`usuario_id` = OLD.`id`;
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'enrollment_deleted', 'delete', 'matriculas', 'aluno_turma', c1.`id`, JSON_OBJECT('id', c1.`id`, 'aluno_id', c1.`aluno_id`, 'turma_id', c1.`turma_id`, 'data_inscricao', c1.`data_inscricao`, 'ativo', c1.`ativo`), NULL, @audit_ip, @audit_user_agent, @audit_request_id FROM `aluno` c0 INNER JOIN `aluno_turma` c1 ON c1.`aluno_id` = c0.`usuario_id` WHERE c0.`usuario_id` = OLD.`id`;
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'attendance_deleted', 'delete', 'presencas', 'presenca_treino', c1.`treino_id`, JSON_OBJECT('treino_id', c1.`treino_id`, 'aluno_id', c1.`aluno_id`, 'situacao', c1.`situacao`, 'checkin_time', c1.`checkin_time`), NULL, @audit_ip, @audit_user_agent, @audit_request_id FROM `aluno` c0 INNER JOIN `presenca_treino` c1 ON c1.`aluno_id` = c0.`usuario_id` WHERE c0.`usuario_id` = OLD.`id`;
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'contact_deleted', 'delete', 'usuarios', 'contato', c0.`id`, JSON_OBJECT('id', c0.`id`, 'usuario_id', c0.`usuario_id`, 'tipo', c0.`tipo`, 'valor', c0.`valor`), NULL, @audit_ip, @audit_user_agent, @audit_request_id FROM `contato` c0 WHERE c0.`usuario_id` = OLD.`id`;
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'payment_updated', 'update', 'financeiro', 'pagamento', c0.`id`, JSON_OBJECT('id', c0.`id`, 'cobranca_id', c0.`cobranca_id`, 'valor_pago', c0.`valor_pago`, 'data_pagamento', c0.`data_pagamento`, 'forma_pagamento', c0.`forma_pagamento`, 'registrado_por', c0.`registrado_por`), JSON_OBJECT('id', c0.`id`, 'cobranca_id', c0.`cobranca_id`, 'valor_pago', c0.`valor_pago`, 'data_pagamento', c0.`data_pagamento`, 'forma_pagamento', c0.`forma_pagamento`, 'registrado_por', NULL), @audit_ip, @audit_user_agent, @audit_request_id FROM `pagamento` c0 WHERE c0.`registrado_por` = OLD.`id`;
END$$

DROP TRIGGER IF EXISTS audit_aluno_cascade$$
CREATE TRIGGER audit_aluno_cascade BEFORE DELETE ON `aluno` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'enrollment_deleted', 'delete', 'matriculas', 'aluno_turma', c0.`id`, JSON_OBJECT('id', c0.`id`, 'aluno_id', c0.`aluno_id`, 'turma_id', c0.`turma_id`, 'data_inscricao', c0.`data_inscricao`, 'ativo', c0.`ativo`), NULL, @audit_ip, @audit_user_agent, @audit_request_id FROM `aluno_turma` c0 WHERE c0.`aluno_id` = OLD.`usuario_id`;
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'attendance_deleted', 'delete', 'presencas', 'presenca_treino', c0.`treino_id`, JSON_OBJECT('treino_id', c0.`treino_id`, 'aluno_id', c0.`aluno_id`, 'situacao', c0.`situacao`, 'checkin_time', c0.`checkin_time`), NULL, @audit_ip, @audit_user_agent, @audit_request_id FROM `presenca_treino` c0 WHERE c0.`aluno_id` = OLD.`usuario_id`;
END$$

DROP TRIGGER IF EXISTS audit_funcionario_cascade$$
CREATE TRIGGER audit_funcionario_cascade BEFORE DELETE ON `funcionario` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'workout_session_updated', 'update', 'treinos', 'treino_agenda', c0.`id`, JSON_OBJECT('id', c0.`id`, 'treino_id', c0.`treino_id`, 'turma_id', c0.`turma_id`, 'espaco_id', c0.`espaco_id`, 'instrutor_id', c0.`instrutor_id`, 'data_hora_inicio', c0.`data_hora_inicio`, 'data_hora_fim', c0.`data_hora_fim`, 'status', c0.`status`), JSON_OBJECT('id', c0.`id`, 'treino_id', c0.`treino_id`, 'turma_id', c0.`turma_id`, 'espaco_id', c0.`espaco_id`, 'instrutor_id', NULL, 'data_hora_inicio', c0.`data_hora_inicio`, 'data_hora_fim', c0.`data_hora_fim`, 'status', c0.`status`), @audit_ip, @audit_user_agent, @audit_request_id FROM `treino_agenda` c0 WHERE c0.`instrutor_id` = OLD.`usuario_id`;
END$$

DROP TRIGGER IF EXISTS audit_cargo_cascade$$
CREATE TRIGGER audit_cargo_cascade BEFORE DELETE ON `cargo` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'permission_changed', 'delete', 'usuarios', 'cargo_permissao', c0.`cargo_id`, JSON_OBJECT('cargo_id', c0.`cargo_id`, 'permissao_id', c0.`permissao_id`), NULL, @audit_ip, @audit_user_agent, @audit_request_id FROM `cargo_permissao` c0 WHERE c0.`cargo_id` = OLD.`id`;
END$$

DROP TRIGGER IF EXISTS audit_permissao_cascade$$
CREATE TRIGGER audit_permissao_cascade BEFORE DELETE ON `permissao` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'permission_changed', 'delete', 'usuarios', 'cargo_permissao', c0.`cargo_id`, JSON_OBJECT('cargo_id', c0.`cargo_id`, 'permissao_id', c0.`permissao_id`), NULL, @audit_ip, @audit_user_agent, @audit_request_id FROM `cargo_permissao` c0 WHERE c0.`permissao_id` = OLD.`id`;
END$$

DROP TRIGGER IF EXISTS audit_servico_cascade$$
CREATE TRIGGER audit_servico_cascade BEFORE DELETE ON `servico` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'contract_item_updated', 'update', 'financeiro', 'contrato_item', c0.`id`, JSON_OBJECT('id', c0.`id`, 'contrato_id', c0.`contrato_id`, 'servico_id', c0.`servico_id`, 'descricao', c0.`descricao`, 'quantidade', c0.`quantidade`, 'valor_unitario', c0.`valor_unitario`, 'valor_desconto', c0.`valor_desconto`), JSON_OBJECT('id', c0.`id`, 'contrato_id', c0.`contrato_id`, 'servico_id', NULL, 'descricao', c0.`descricao`, 'quantidade', c0.`quantidade`, 'valor_unitario', c0.`valor_unitario`, 'valor_desconto', c0.`valor_desconto`), @audit_ip, @audit_user_agent, @audit_request_id FROM `contrato_item` c0 WHERE c0.`servico_id` = OLD.`id`;
END$$

DROP TRIGGER IF EXISTS audit_plano_cascade$$
CREATE TRIGGER audit_plano_cascade BEFORE DELETE ON `plano` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'plan_item_deleted', 'delete', 'configuracao', 'plano_item', c0.`id`, JSON_OBJECT('id', c0.`id`, 'plano_id', c0.`plano_id`, 'servico_id', c0.`servico_id`, 'quantidade', c0.`quantidade`, 'valor_unitario', c0.`valor_unitario`), NULL, @audit_ip, @audit_user_agent, @audit_request_id FROM `plano_item` c0 WHERE c0.`plano_id` = OLD.`id`;
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'contract_updated', 'update', 'financeiro', 'contrato', c0.`id`, JSON_OBJECT('id', c0.`id`, 'aluno_id', c0.`aluno_id`, 'plano_id', c0.`plano_id`, 'data_inicio', c0.`data_inicio`, 'data_fim', c0.`data_fim`, 'dia_vencimento', c0.`dia_vencimento`, 'valor_contratado', c0.`valor_contratado`, 'desconto', c0.`desconto`, 'multa_percentual', c0.`multa_percentual`, 'juros_percentual', c0.`juros_percentual`, 'status', c0.`status`), JSON_OBJECT('id', c0.`id`, 'aluno_id', c0.`aluno_id`, 'plano_id', NULL, 'data_inicio', c0.`data_inicio`, 'data_fim', c0.`data_fim`, 'dia_vencimento', c0.`dia_vencimento`, 'valor_contratado', c0.`valor_contratado`, 'desconto', c0.`desconto`, 'multa_percentual', c0.`multa_percentual`, 'juros_percentual', c0.`juros_percentual`, 'status', c0.`status`), @audit_ip, @audit_user_agent, @audit_request_id FROM `contrato` c0 WHERE c0.`plano_id` = OLD.`id`;
END$$

DROP TRIGGER IF EXISTS audit_contrato_cascade$$
CREATE TRIGGER audit_contrato_cascade BEFORE DELETE ON `contrato` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'contract_item_deleted', 'delete', 'financeiro', 'contrato_item', c0.`id`, JSON_OBJECT('id', c0.`id`, 'contrato_id', c0.`contrato_id`, 'servico_id', c0.`servico_id`, 'descricao', c0.`descricao`, 'quantidade', c0.`quantidade`, 'valor_unitario', c0.`valor_unitario`, 'valor_desconto', c0.`valor_desconto`), NULL, @audit_ip, @audit_user_agent, @audit_request_id FROM `contrato_item` c0 WHERE c0.`contrato_id` = OLD.`id`;
END$$

DROP TRIGGER IF EXISTS audit_turma_cascade$$
CREATE TRIGGER audit_turma_cascade BEFORE DELETE ON `turma` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'enrollment_deleted', 'delete', 'matriculas', 'aluno_turma', c0.`id`, JSON_OBJECT('id', c0.`id`, 'aluno_id', c0.`aluno_id`, 'turma_id', c0.`turma_id`, 'data_inscricao', c0.`data_inscricao`, 'ativo', c0.`ativo`), NULL, @audit_ip, @audit_user_agent, @audit_request_id FROM `aluno_turma` c0 WHERE c0.`turma_id` = OLD.`id`;
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'workout_session_updated', 'update', 'treinos', 'treino_agenda', c0.`id`, JSON_OBJECT('id', c0.`id`, 'treino_id', c0.`treino_id`, 'turma_id', c0.`turma_id`, 'espaco_id', c0.`espaco_id`, 'instrutor_id', c0.`instrutor_id`, 'data_hora_inicio', c0.`data_hora_inicio`, 'data_hora_fim', c0.`data_hora_fim`, 'status', c0.`status`), JSON_OBJECT('id', c0.`id`, 'treino_id', c0.`treino_id`, 'turma_id', NULL, 'espaco_id', c0.`espaco_id`, 'instrutor_id', c0.`instrutor_id`, 'data_hora_inicio', c0.`data_hora_inicio`, 'data_hora_fim', c0.`data_hora_fim`, 'status', c0.`status`), @audit_ip, @audit_user_agent, @audit_request_id FROM `treino_agenda` c0 WHERE c0.`turma_id` = OLD.`id`;
END$$

DROP TRIGGER IF EXISTS audit_treino_agenda_cascade$$
CREATE TRIGGER audit_treino_agenda_cascade BEFORE DELETE ON `treino_agenda` FOR EACH ROW
BEGIN
INSERT INTO audit_logs (user_id, user_name, action, operation, module, entity_type, entity_id, old_data, new_data, ip_address, user_agent, request_id)
SELECT @audit_user_id, @audit_user_name, 'attendance_deleted', 'delete', 'presencas', 'presenca_treino', c0.`treino_id`, JSON_OBJECT('treino_id', c0.`treino_id`, 'aluno_id', c0.`aluno_id`, 'situacao', c0.`situacao`, 'checkin_time', c0.`checkin_time`), NULL, @audit_ip, @audit_user_agent, @audit_request_id FROM `presenca_treino` c0 WHERE c0.`treino_id` = OLD.`id`;
END$$

DELIMITER ;
