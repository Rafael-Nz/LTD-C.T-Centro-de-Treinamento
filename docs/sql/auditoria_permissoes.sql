-- Executar como administrador, depois de criar a conta EXCLUSIVA ctt_app@localhost.
-- Nao usar a conta da aplicacao como DEFINER dos triggers.
-- Nao altera cadastros nem logs. Remove privilegios anteriores desta conta.
-- Reaplicar apos alterar o schema; tabelas novas nao recebem privilegios automaticamente.
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'ctt_app'@'localhost';

GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`aluno` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`aluno_turma` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`anamnese_formulario` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`anamnese_opcao` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`anamnese_pergunta` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`anamnese_resposta` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`auth_rate_limits` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`avaliacao_fisica` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`cargo` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`cargo_permissao` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`cobranca` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`contato` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`contrato` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`contrato_item` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`endereco` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`espaco_treino` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`funcionario` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`modalidade` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`pagamento` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`password_resets` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`permissao` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`plano` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`plano_item` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`presenca_treino` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`sequencia_matricula` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`servico` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`student_account_activations` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`treino` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`treino_agenda` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`turma` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`turma_config_horario` TO 'ctt_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `db_centro_treinamento`.`usuario` TO 'ctt_app'@'localhost';

-- A API consulta o historico e insere apenas o envelope dos eventos HTTP.
-- ID, hashes, horario e snapshots de negocio sao responsabilidade dos triggers.
GRANT SELECT ON `db_centro_treinamento`.`audit_logs` TO 'ctt_app'@'localhost';
GRANT INSERT (
    user_id, user_name, action, operation, module, entity_type, entity_id,
    ip_address, user_agent, request_id, correlation_id, http_method, route,
    context_data, result, http_status
) ON `db_centro_treinamento`.`audit_logs` TO 'ctt_app'@'localhost';

-- SELECT permite ao servico obter SELECT FOR UPDATE no MariaDB.
-- A conta nao pode alterar last_id/last_hash ou remover o chain head.
GRANT SELECT ON `db_centro_treinamento`.`audit_chain_state` TO 'ctt_app'@'localhost';
