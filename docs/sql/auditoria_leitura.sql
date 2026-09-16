-- Consulta da auditoria unificada com cadeia de integridade.
SELECT id, created_at AS quando, user_id, user_name AS quem,
       action, operation, module, entity_type, entity_id,
       old_data AS antes, new_data AS depois, result, http_status,
       ip_address, user_agent, request_id, correlation_id, http_method, route,
       context_data, hash_version, chain_id, previous_hash, row_hash
FROM audit_logs ORDER BY id DESC LIMIT 100;
