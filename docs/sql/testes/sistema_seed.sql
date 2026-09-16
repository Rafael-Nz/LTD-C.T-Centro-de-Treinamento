-- Seed completo para testar o sistema apos docs/sql/setup/banco.sql
--
-- Como usar:
-- 1. Execute docs/sql/setup/banco.sql (apaga e recria o banco)
-- 2. Execute este arquivo
--
-- Pode ser rerodado sem duplicar os mesmos registros.
--
-- Login de teste (todos os colaboradores ativos):
--   Senha: Senha@Teste123
--   Admin:       admin@centrotreinamento.com
--   Gerente:     roberto.gerente@ctt.teste
--   Instrutor:   paulo.instrutor@ctt.teste
--   Instrutora:  mariana.instrutora@ctt.teste
--   Estagiaria:  larissa.estagiaria@ctt.teste
--   Atendente:   camila.atendente@ctt.teste
-- Alunos nao entram no painel administrativo.

USE db_centro_treinamento;

START TRANSACTION;

SET @senha_teste = '$argon2id$v=19$m=65536,t=4,p=1$Uk5pTmFnSk02ckovTERsbQ$kloiHpXCLDoQ9XsEfo7cGenKDCrCRMJ1ODizht6OGMA';

-- ---------------------------------------------------------------------------
-- Admin padrao do banco.sql passa a usar a senha de teste
-- ---------------------------------------------------------------------------
UPDATE usuario
SET senha = @senha_teste
WHERE email = 'admin@centrotreinamento.com';

SET @admin_id = (SELECT id FROM usuario WHERE email = 'admin@centrotreinamento.com' LIMIT 1);

-- ---------------------------------------------------------------------------
-- Permissoes financeiras por cargo
-- ---------------------------------------------------------------------------
INSERT INTO cargo_permissao (cargo_id, permissao_id)
SELECT c.id, p.id
FROM cargo c
INNER JOIN permissao p ON p.slug IN ('financeiro.visualizar', 'financeiro.gerenciar', 'financeiro.registrar_pagamento')
WHERE c.nome IN ('Administrador', 'Gerente')
  AND NOT EXISTS (
      SELECT 1 FROM cargo_permissao cp
      WHERE cp.cargo_id = c.id AND cp.permissao_id = p.id
  );

INSERT INTO cargo_permissao (cargo_id, permissao_id)
SELECT c.id, p.id
FROM cargo c
INNER JOIN permissao p ON p.slug IN ('financeiro.visualizar', 'financeiro.registrar_pagamento')
WHERE c.nome = 'Atendente'
  AND NOT EXISTS (
      SELECT 1 FROM cargo_permissao cp
      WHERE cp.cargo_id = c.id AND cp.permissao_id = p.id
  );

-- ---------------------------------------------------------------------------
-- Enderecos
-- ---------------------------------------------------------------------------
INSERT INTO endereco (logradouro, numero, cidade, bairro, cep, complemento)
SELECT 'Rua das Palmeiras', '120', 'Sao Paulo', 'Pinheiros', '05422010', 'Seed sistema'
WHERE NOT EXISTS (SELECT 1 FROM endereco WHERE logradouro = 'Rua das Palmeiras' AND numero = '120' AND cep = '05422010');

INSERT INTO endereco (logradouro, numero, cidade, bairro, cep, complemento)
SELECT 'Av. Reboucas', '3500', 'Sao Paulo', 'Itaim Bibi', '04534020', 'Seed sistema'
WHERE NOT EXISTS (SELECT 1 FROM endereco WHERE logradouro = 'Av. Reboucas' AND numero = '3500' AND cep = '04534020');

INSERT INTO endereco (logradouro, numero, cidade, bairro, cep, complemento)
SELECT 'Rua Augusta', '890', 'Sao Paulo', 'Consolacao', '01305000', 'Seed sistema'
WHERE NOT EXISTS (SELECT 1 FROM endereco WHERE logradouro = 'Rua Augusta' AND numero = '890' AND cep = '01305000');

INSERT INTO endereco (logradouro, numero, cidade, bairro, cep, complemento)
SELECT 'Rua Harmonia', '45', 'Sao Paulo', 'Vila Madalena', '05435000', 'Seed sistema'
WHERE NOT EXISTS (SELECT 1 FROM endereco WHERE logradouro = 'Rua Harmonia' AND numero = '45' AND cep = '05435000');

SET @end_pinheiros = (SELECT id FROM endereco WHERE logradouro = 'Rua das Palmeiras' AND numero = '120' LIMIT 1);
SET @end_itaim = (SELECT id FROM endereco WHERE logradouro = 'Av. Reboucas' AND numero = '3500' LIMIT 1);
SET @end_augusta = (SELECT id FROM endereco WHERE logradouro = 'Rua Augusta' AND numero = '890' LIMIT 1);
SET @end_harmonia = (SELECT id FROM endereco WHERE logradouro = 'Rua Harmonia' AND numero = '45' LIMIT 1);

SET @cargo_admin = (SELECT id FROM cargo WHERE nome = 'Administrador' LIMIT 1);
SET @cargo_instrutor = (SELECT id FROM cargo WHERE nome = 'Instrutor' LIMIT 1);
SET @cargo_estagiario = (SELECT id FROM cargo WHERE nome = 'Estagiário' LIMIT 1);
SET @cargo_gerente = (SELECT id FROM cargo WHERE nome = 'Gerente' LIMIT 1);
SET @cargo_atendente = (SELECT id FROM cargo WHERE nome = 'Atendente' LIMIT 1);

-- ---------------------------------------------------------------------------
-- Funcionarios
-- ---------------------------------------------------------------------------
INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario, ativo)
SELECT 'Roberto', 'Gerente', '80000000001', 'roberto.gerente@ctt.teste', @senha_teste, '1982-03-14', 'M', @end_itaim, 'funcionario', TRUE
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE cpf = '80000000001' OR email = 'roberto.gerente@ctt.teste');

INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario, ativo)
SELECT 'Paulo', 'Instrutor', '80000000002', 'paulo.instrutor@ctt.teste', @senha_teste, '1988-04-10', 'M', @end_pinheiros, 'funcionario', TRUE
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE cpf = '80000000002' OR email = 'paulo.instrutor@ctt.teste');

INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario, ativo)
SELECT 'Mariana', 'Instrutora', '80000000003', 'mariana.instrutora@ctt.teste', @senha_teste, '1992-07-22', 'F', @end_harmonia, 'funcionario', TRUE
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE cpf = '80000000003' OR email = 'mariana.instrutora@ctt.teste');

INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario, ativo)
SELECT 'Larissa', 'Estagiaria', '80000000004', 'larissa.estagiaria@ctt.teste', @senha_teste, '2002-09-18', 'F', @end_augusta, 'funcionario', TRUE
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE cpf = '80000000004' OR email = 'larissa.estagiaria@ctt.teste');

INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario, ativo)
SELECT 'Camila', 'Atendente', '80000000005', 'camila.atendente@ctt.teste', @senha_teste, '1996-11-05', 'F', @end_augusta, 'funcionario', TRUE
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE cpf = '80000000005' OR email = 'camila.atendente@ctt.teste');

INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario, ativo)
SELECT 'Felipe', 'Inativo', '80000000006', 'felipe.inativo@ctt.teste', @senha_teste, '1985-01-30', 'M', @end_pinheiros, 'funcionario', FALSE
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE cpf = '80000000006' OR email = 'felipe.inativo@ctt.teste');

SET @id_gerente = (SELECT id FROM usuario WHERE cpf = '80000000001' LIMIT 1);
SET @id_paulo = (SELECT id FROM usuario WHERE cpf = '80000000002' LIMIT 1);
SET @id_mariana = (SELECT id FROM usuario WHERE cpf = '80000000003' LIMIT 1);
SET @id_larissa = (SELECT id FROM usuario WHERE cpf = '80000000004' LIMIT 1);
SET @id_camila = (SELECT id FROM usuario WHERE cpf = '80000000005' LIMIT 1);
SET @id_felipe = (SELECT id FROM usuario WHERE cpf = '80000000006' LIMIT 1);

INSERT INTO funcionario (usuario_id, cargo_id, registro_profissional, observacoes)
SELECT @id_gerente, @cargo_gerente, 'GER-001', 'Gerente operacional de teste'
WHERE @id_gerente IS NOT NULL AND NOT EXISTS (SELECT 1 FROM funcionario WHERE usuario_id = @id_gerente);

INSERT INTO funcionario (usuario_id, cargo_id, registro_profissional, observacoes)
SELECT @id_paulo, @cargo_instrutor, 'CREF-SP-1001', 'Instrutor de Crossfit e mobilidade'
WHERE @id_paulo IS NOT NULL AND NOT EXISTS (SELECT 1 FROM funcionario WHERE usuario_id = @id_paulo);

INSERT INTO funcionario (usuario_id, cargo_id, registro_profissional, observacoes)
SELECT @id_mariana, @cargo_instrutor, 'CREF-SP-1002', 'Instrutora de funcional'
WHERE @id_mariana IS NOT NULL AND NOT EXISTS (SELECT 1 FROM funcionario WHERE usuario_id = @id_mariana);

INSERT INTO funcionario (usuario_id, cargo_id, registro_profissional, observacoes)
SELECT @id_larissa, @cargo_estagiario, NULL, 'Estagiaria de apoio nas aulas'
WHERE @id_larissa IS NOT NULL AND NOT EXISTS (SELECT 1 FROM funcionario WHERE usuario_id = @id_larissa);

INSERT INTO funcionario (usuario_id, cargo_id, registro_profissional, observacoes)
SELECT @id_camila, @cargo_atendente, 'ATD-001', 'Recepcao e financeiro operacional'
WHERE @id_camila IS NOT NULL AND NOT EXISTS (SELECT 1 FROM funcionario WHERE usuario_id = @id_camila);

INSERT INTO funcionario (usuario_id, cargo_id, registro_profissional, observacoes)
SELECT @id_felipe, @cargo_instrutor, 'CREF-SP-0999', 'Ex-instrutor inativo para testes de filtro'
WHERE @id_felipe IS NOT NULL AND NOT EXISTS (SELECT 1 FROM funcionario WHERE usuario_id = @id_felipe);

INSERT INTO contato (usuario_id, tipo, valor)
SELECT @id_paulo, 'telefone', '11988880002'
WHERE NOT EXISTS (SELECT 1 FROM contato WHERE usuario_id = @id_paulo AND tipo = 'telefone');

INSERT INTO contato (usuario_id, tipo, valor)
SELECT @id_paulo, 'whatsapp', '11988880002'
WHERE NOT EXISTS (SELECT 1 FROM contato WHERE usuario_id = @id_paulo AND tipo = 'whatsapp');

INSERT INTO contato (usuario_id, tipo, valor)
SELECT @id_mariana, 'telefone', '11988880003'
WHERE NOT EXISTS (SELECT 1 FROM contato WHERE usuario_id = @id_mariana AND tipo = 'telefone');

INSERT INTO contato (usuario_id, tipo, valor)
SELECT @id_camila, 'telefone', '11988880005'
WHERE NOT EXISTS (SELECT 1 FROM contato WHERE usuario_id = @id_camila AND tipo = 'telefone');

-- ---------------------------------------------------------------------------
-- Modalidades, treinos-base e espacos
-- ---------------------------------------------------------------------------
INSERT INTO modalidade (nome, descricao, ativo)
SELECT 'Crossfit', 'Treinamento de alta intensidade com movimentos variados.', TRUE
WHERE NOT EXISTS (SELECT 1 FROM modalidade WHERE nome = 'Crossfit');

INSERT INTO modalidade (nome, descricao, ativo)
SELECT 'Funcional', 'Treinos funcionais para condicionamento, mobilidade e resistencia.', TRUE
WHERE NOT EXISTS (SELECT 1 FROM modalidade WHERE nome = 'Funcional');

INSERT INTO modalidade (nome, descricao, ativo)
SELECT 'Mobilidade', 'Sessao voltada para mobilidade articular, alongamento e recuperacao.', TRUE
WHERE NOT EXISTS (SELECT 1 FROM modalidade WHERE nome = 'Mobilidade');

INSERT INTO modalidade (nome, descricao, ativo)
SELECT 'Yoga', 'Modalidade inativa para testar filtros de cadastro.', FALSE
WHERE NOT EXISTS (SELECT 1 FROM modalidade WHERE nome = 'Yoga');

SET @mod_crossfit = (SELECT id FROM modalidade WHERE nome = 'Crossfit' LIMIT 1);
SET @mod_funcional = (SELECT id FROM modalidade WHERE nome = 'Funcional' LIMIT 1);
SET @mod_mobilidade = (SELECT id FROM modalidade WHERE nome = 'Mobilidade' LIMIT 1);

INSERT INTO treino (nome, modalidade_id, descricao, ativo)
SELECT 'WOD Iniciante', @mod_crossfit, 'Treino-base introdutorio para alunos iniciantes.', TRUE
WHERE @mod_crossfit IS NOT NULL AND NOT EXISTS (SELECT 1 FROM treino WHERE nome = 'WOD Iniciante' AND modalidade_id = @mod_crossfit);

INSERT INTO treino (nome, modalidade_id, descricao, ativo)
SELECT 'WOD Intermediario', @mod_crossfit, 'Treino-base com progressao tecnica e intensidade moderada.', TRUE
WHERE @mod_crossfit IS NOT NULL AND NOT EXISTS (SELECT 1 FROM treino WHERE nome = 'WOD Intermediario' AND modalidade_id = @mod_crossfit);

INSERT INTO treino (nome, modalidade_id, descricao, ativo)
SELECT 'Circuito Funcional Base', @mod_funcional, 'Treino-base em circuito para condicionamento geral.', TRUE
WHERE @mod_funcional IS NOT NULL AND NOT EXISTS (SELECT 1 FROM treino WHERE nome = 'Circuito Funcional Base' AND modalidade_id = @mod_funcional);

INSERT INTO treino (nome, modalidade_id, descricao, ativo)
SELECT 'Sessao de Mobilidade', @mod_mobilidade, 'Treino-base leve para aquecimento e recuperacao ativa.', TRUE
WHERE @mod_mobilidade IS NOT NULL AND NOT EXISTS (SELECT 1 FROM treino WHERE nome = 'Sessao de Mobilidade' AND modalidade_id = @mod_mobilidade);

SET @treino_wod = (SELECT id FROM treino WHERE nome = 'WOD Iniciante' LIMIT 1);
SET @treino_wod2 = (SELECT id FROM treino WHERE nome = 'WOD Intermediario' LIMIT 1);
SET @treino_func = (SELECT id FROM treino WHERE nome = 'Circuito Funcional Base' LIMIT 1);
SET @treino_mob = (SELECT id FROM treino WHERE nome = 'Sessao de Mobilidade' LIMIT 1);

INSERT INTO espaco_treino (nome, capacidade_minima, capacidade_maxima, equipamentos, ativo)
SELECT 'Box Principal', 5, 30, 'Barras, anilhas, remos, cordas e caixas', TRUE
WHERE NOT EXISTS (SELECT 1 FROM espaco_treino WHERE nome = 'Box Principal');

INSERT INTO espaco_treino (nome, capacidade_minima, capacidade_maxima, equipamentos, ativo)
SELECT 'Sala Funcional', 4, 20, 'Kettlebells, elasticos, cones e colchonetes', TRUE
WHERE NOT EXISTS (SELECT 1 FROM espaco_treino WHERE nome = 'Sala Funcional');

INSERT INTO espaco_treino (nome, capacidade_minima, capacidade_maxima, equipamentos, ativo)
SELECT 'Sala 2 - Manutencao', 2, 10, 'Espaco temporariamente fora de uso', FALSE
WHERE NOT EXISTS (SELECT 1 FROM espaco_treino WHERE nome = 'Sala 2 - Manutencao');

SET @espaco_box = (SELECT id FROM espaco_treino WHERE nome = 'Box Principal' LIMIT 1);
SET @espaco_sala = (SELECT id FROM espaco_treino WHERE nome = 'Sala Funcional' LIMIT 1);

-- ---------------------------------------------------------------------------
-- Turmas e horarios
-- ---------------------------------------------------------------------------
INSERT INTO turma (nome, instrutor_id, capacidade_minima, capacidade_maxima, ativo)
SELECT 'Crossfit Matutino', @id_paulo, 5, 20, TRUE
WHERE NOT EXISTS (SELECT 1 FROM turma WHERE nome = 'Crossfit Matutino');

INSERT INTO turma (nome, instrutor_id, capacidade_minima, capacidade_maxima, ativo)
SELECT 'Funcional Noturno', @id_mariana, 4, 16, TRUE
WHERE NOT EXISTS (SELECT 1 FROM turma WHERE nome = 'Funcional Noturno');

INSERT INTO turma (nome, instrutor_id, capacidade_minima, capacidade_maxima, ativo)
SELECT 'Mobilidade Sabado', @id_paulo, 3, 12, TRUE
WHERE NOT EXISTS (SELECT 1 FROM turma WHERE nome = 'Mobilidade Sabado');

INSERT INTO turma (nome, instrutor_id, capacidade_minima, capacidade_maxima, ativo)
SELECT 'Turma Encerrada Teste', @id_felipe, 4, 10, FALSE
WHERE NOT EXISTS (SELECT 1 FROM turma WHERE nome = 'Turma Encerrada Teste');

SET @turma_cross = (SELECT id FROM turma WHERE nome = 'Crossfit Matutino' LIMIT 1);
SET @turma_func = (SELECT id FROM turma WHERE nome = 'Funcional Noturno' LIMIT 1);
SET @turma_mob = (SELECT id FROM turma WHERE nome = 'Mobilidade Sabado' LIMIT 1);

INSERT INTO turma_config_horario (turma_id, dia_semana, hora_inicio, hora_fim)
SELECT @turma_cross, 'segunda', '06:00:00', '07:00:00'
WHERE @turma_cross IS NOT NULL AND NOT EXISTS (SELECT 1 FROM turma_config_horario WHERE turma_id = @turma_cross AND dia_semana = 'segunda');

INSERT INTO turma_config_horario (turma_id, dia_semana, hora_inicio, hora_fim)
SELECT @turma_cross, 'quarta', '06:00:00', '07:00:00'
WHERE @turma_cross IS NOT NULL AND NOT EXISTS (SELECT 1 FROM turma_config_horario WHERE turma_id = @turma_cross AND dia_semana = 'quarta');

INSERT INTO turma_config_horario (turma_id, dia_semana, hora_inicio, hora_fim)
SELECT @turma_cross, 'sexta', '06:00:00', '07:00:00'
WHERE @turma_cross IS NOT NULL AND NOT EXISTS (SELECT 1 FROM turma_config_horario WHERE turma_id = @turma_cross AND dia_semana = 'sexta');

INSERT INTO turma_config_horario (turma_id, dia_semana, hora_inicio, hora_fim)
SELECT @turma_func, 'terca', '19:00:00', '20:00:00'
WHERE @turma_func IS NOT NULL AND NOT EXISTS (SELECT 1 FROM turma_config_horario WHERE turma_id = @turma_func AND dia_semana = 'terca');

INSERT INTO turma_config_horario (turma_id, dia_semana, hora_inicio, hora_fim)
SELECT @turma_func, 'quinta', '19:00:00', '20:00:00'
WHERE @turma_func IS NOT NULL AND NOT EXISTS (SELECT 1 FROM turma_config_horario WHERE turma_id = @turma_func AND dia_semana = 'quinta');

INSERT INTO turma_config_horario (turma_id, dia_semana, hora_inicio, hora_fim)
SELECT @turma_mob, 'sabado', '09:00:00', '10:00:00'
WHERE @turma_mob IS NOT NULL AND NOT EXISTS (SELECT 1 FROM turma_config_horario WHERE turma_id = @turma_mob AND dia_semana = 'sabado');

-- ---------------------------------------------------------------------------
-- Alunos
-- ---------------------------------------------------------------------------
INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario, ativo)
SELECT 'Ana', 'Souza', '81000000001', 'ana.souza@ctt.teste', @senha_teste, '1995-02-12', 'F', @end_pinheiros, 'aluno', TRUE
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE cpf = '81000000001');

INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario, ativo)
SELECT 'Bruno', 'Lima', '81000000002', 'bruno.lima@ctt.teste', @senha_teste, '1990-08-03', 'M', @end_itaim, 'aluno', TRUE
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE cpf = '81000000002');

INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario, ativo)
SELECT 'Carla', 'Mendes', '81000000003', 'carla.mendes@ctt.teste', @senha_teste, '1987-12-19', 'F', @end_harmonia, 'aluno', TRUE
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE cpf = '81000000003');

INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario, ativo)
SELECT 'Diego', 'Alves', '81000000004', 'diego.alves@ctt.teste', @senha_teste, '1998-05-27', 'M', @end_augusta, 'aluno', FALSE
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE cpf = '81000000004');

INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario, ativo)
SELECT 'Elisa', 'Rocha', '81000000005', 'elisa.rocha@ctt.teste', @senha_teste, '2001-04-09', 'F', @end_pinheiros, 'aluno', TRUE
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE cpf = '81000000005');

INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario, ativo)
SELECT 'Fabio', 'Nunes', '81000000006', 'fabio.nunes@ctt.teste', @senha_teste, '1984-10-15', 'M', @end_itaim, 'aluno', TRUE
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE cpf = '81000000006');

INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario, ativo)
SELECT 'Gabriela', 'Pinto', '81000000007', 'gabriela.pinto@ctt.teste', @senha_teste, '1993-06-21', 'F', @end_harmonia, 'aluno', TRUE
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE cpf = '81000000007');

INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario, ativo)
SELECT 'Henrique', 'Costa', '81000000008', 'henrique.costa@ctt.teste', @senha_teste, '1991-01-08', 'M', @end_augusta, 'aluno', TRUE
WHERE NOT EXISTS (SELECT 1 FROM usuario WHERE cpf = '81000000008');

SET @aluno_ana = (SELECT id FROM usuario WHERE cpf = '81000000001' LIMIT 1);
SET @aluno_bruno = (SELECT id FROM usuario WHERE cpf = '81000000002' LIMIT 1);
SET @aluno_carla = (SELECT id FROM usuario WHERE cpf = '81000000003' LIMIT 1);
SET @aluno_diego = (SELECT id FROM usuario WHERE cpf = '81000000004' LIMIT 1);
SET @aluno_elisa = (SELECT id FROM usuario WHERE cpf = '81000000005' LIMIT 1);
SET @aluno_fabio = (SELECT id FROM usuario WHERE cpf = '81000000006' LIMIT 1);
SET @aluno_gabi = (SELECT id FROM usuario WHERE cpf = '81000000007' LIMIT 1);
SET @aluno_henrique = (SELECT id FROM usuario WHERE cpf = '81000000008' LIMIT 1);

INSERT INTO aluno (usuario_id, data_matricula, cadastrado_por, codigo_matricula)
SELECT @aluno_ana, '2026-03-02', @id_camila, '202603000001'
WHERE @aluno_ana IS NOT NULL AND NOT EXISTS (SELECT 1 FROM aluno WHERE usuario_id = @aluno_ana);

INSERT INTO aluno (usuario_id, data_matricula, cadastrado_por, codigo_matricula)
SELECT @aluno_bruno, '2026-04-10', @id_camila, '202604000002'
WHERE @aluno_bruno IS NOT NULL AND NOT EXISTS (SELECT 1 FROM aluno WHERE usuario_id = @aluno_bruno);

INSERT INTO aluno (usuario_id, data_matricula, cadastrado_por, codigo_matricula)
SELECT @aluno_carla, '2026-05-05', @admin_id, '202605000003'
WHERE @aluno_carla IS NOT NULL AND NOT EXISTS (SELECT 1 FROM aluno WHERE usuario_id = @aluno_carla);

INSERT INTO aluno (usuario_id, data_matricula, cadastrado_por, codigo_matricula)
SELECT @aluno_diego, '2026-01-20', @id_camila, '202601000004'
WHERE @aluno_diego IS NOT NULL AND NOT EXISTS (SELECT 1 FROM aluno WHERE usuario_id = @aluno_diego);

INSERT INTO aluno (usuario_id, data_matricula, cadastrado_por, codigo_matricula)
SELECT @aluno_elisa, CURDATE(), @id_camila, '202609000005'
WHERE @aluno_elisa IS NOT NULL AND NOT EXISTS (SELECT 1 FROM aluno WHERE usuario_id = @aluno_elisa);

INSERT INTO aluno (usuario_id, data_matricula, cadastrado_por, codigo_matricula)
SELECT @aluno_fabio, '2025-11-12', @admin_id, '202511000006'
WHERE @aluno_fabio IS NOT NULL AND NOT EXISTS (SELECT 1 FROM aluno WHERE usuario_id = @aluno_fabio);

INSERT INTO aluno (usuario_id, data_matricula, cadastrado_por, codigo_matricula)
SELECT @aluno_gabi, '2026-06-18', @id_camila, '202606000007'
WHERE @aluno_gabi IS NOT NULL AND NOT EXISTS (SELECT 1 FROM aluno WHERE usuario_id = @aluno_gabi);

INSERT INTO aluno (usuario_id, data_matricula, cadastrado_por, codigo_matricula)
SELECT @aluno_henrique, '2026-02-28', @id_camila, '202602000008'
WHERE @aluno_henrique IS NOT NULL AND NOT EXISTS (SELECT 1 FROM aluno WHERE usuario_id = @aluno_henrique);

INSERT IGNORE INTO sequencia_matricula (id)
SELECT n FROM (
    SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
    UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8
) seq;

INSERT INTO contato (usuario_id, tipo, valor)
SELECT @aluno_ana, 'telefone', '11970000001'
WHERE NOT EXISTS (SELECT 1 FROM contato WHERE usuario_id = @aluno_ana AND tipo = 'telefone');
INSERT INTO contato (usuario_id, tipo, valor)
SELECT @aluno_ana, 'whatsapp', '11970000001'
WHERE NOT EXISTS (SELECT 1 FROM contato WHERE usuario_id = @aluno_ana AND tipo = 'whatsapp');
INSERT INTO contato (usuario_id, tipo, valor)
SELECT @aluno_bruno, 'telefone', '11970000002'
WHERE NOT EXISTS (SELECT 1 FROM contato WHERE usuario_id = @aluno_bruno AND tipo = 'telefone');
INSERT INTO contato (usuario_id, tipo, valor)
SELECT @aluno_carla, 'whatsapp', '11970000003'
WHERE NOT EXISTS (SELECT 1 FROM contato WHERE usuario_id = @aluno_carla AND tipo = 'whatsapp');
INSERT INTO contato (usuario_id, tipo, valor)
SELECT @aluno_gabi, 'telefone', '11970000007'
WHERE NOT EXISTS (SELECT 1 FROM contato WHERE usuario_id = @aluno_gabi AND tipo = 'telefone');
INSERT INTO contato (usuario_id, tipo, valor)
SELECT @aluno_henrique, 'telefone', '11970000008'
WHERE NOT EXISTS (SELECT 1 FROM contato WHERE usuario_id = @aluno_henrique AND tipo = 'telefone');

INSERT INTO aluno_turma (aluno_id, turma_id, data_inscricao, ativo)
SELECT @aluno_ana, @turma_cross, '2026-03-02', TRUE
WHERE NOT EXISTS (SELECT 1 FROM aluno_turma WHERE aluno_id = @aluno_ana AND turma_id = @turma_cross);

INSERT INTO aluno_turma (aluno_id, turma_id, data_inscricao, ativo)
SELECT @aluno_ana, @turma_mob, '2026-03-10', TRUE
WHERE NOT EXISTS (SELECT 1 FROM aluno_turma WHERE aluno_id = @aluno_ana AND turma_id = @turma_mob);

INSERT INTO aluno_turma (aluno_id, turma_id, data_inscricao, ativo)
SELECT @aluno_bruno, @turma_cross, '2026-04-10', TRUE
WHERE NOT EXISTS (SELECT 1 FROM aluno_turma WHERE aluno_id = @aluno_bruno AND turma_id = @turma_cross);

INSERT INTO aluno_turma (aluno_id, turma_id, data_inscricao, ativo)
SELECT @aluno_carla, @turma_func, '2026-05-05', TRUE
WHERE NOT EXISTS (SELECT 1 FROM aluno_turma WHERE aluno_id = @aluno_carla AND turma_id = @turma_func);

INSERT INTO aluno_turma (aluno_id, turma_id, data_inscricao, ativo)
SELECT @aluno_diego, @turma_cross, '2026-01-20', FALSE
WHERE NOT EXISTS (SELECT 1 FROM aluno_turma WHERE aluno_id = @aluno_diego AND turma_id = @turma_cross);

INSERT INTO aluno_turma (aluno_id, turma_id, data_inscricao, ativo)
SELECT @aluno_gabi, @turma_func, '2026-06-18', TRUE
WHERE NOT EXISTS (SELECT 1 FROM aluno_turma WHERE aluno_id = @aluno_gabi AND turma_id = @turma_func);

INSERT INTO aluno_turma (aluno_id, turma_id, data_inscricao, ativo)
SELECT @aluno_henrique, @turma_cross, '2026-02-28', TRUE
WHERE NOT EXISTS (SELECT 1 FROM aluno_turma WHERE aluno_id = @aluno_henrique AND turma_id = @turma_cross);

INSERT INTO aluno_turma (aluno_id, turma_id, data_inscricao, ativo)
SELECT @aluno_henrique, @turma_func, '2026-07-01', TRUE
WHERE NOT EXISTS (SELECT 1 FROM aluno_turma WHERE aluno_id = @aluno_henrique AND turma_id = @turma_func);

-- ---------------------------------------------------------------------------
-- Anamnese (preenche se o formulario padrao ja existir)
-- ---------------------------------------------------------------------------
INSERT INTO anamnese_resposta (aluno_id, pergunta_id, valor, observacao)
SELECT @aluno_ana, p.id, JSON_QUOTE('nao'), NULL
FROM anamnese_pergunta p
WHERE p.slug IN (
    'problema_cardiaco', 'dor_peito', 'desmaio_tontura', 'pressao_arterial',
    'problema_osseo', 'outro_problema', 'medicamentos', 'cirurgia', 'gravida',
    'fumante', 'historico_familiar'
)
AND NOT EXISTS (
    SELECT 1 FROM anamnese_resposta r WHERE r.aluno_id = @aluno_ana AND r.pergunta_id = p.id
);

INSERT INTO anamnese_resposta (aluno_id, pergunta_id, valor, observacao)
SELECT @aluno_ana, p.id, JSON_QUOTE('sim'), NULL
FROM anamnese_pergunta p
WHERE p.slug IN ('consumo_alcool', 'atividade_fisica')
AND NOT EXISTS (
    SELECT 1 FROM anamnese_resposta r WHERE r.aluno_id = @aluno_ana AND r.pergunta_id = p.id
);

INSERT INTO anamnese_resposta (aluno_id, pergunta_id, valor, observacao)
SELECT @aluno_ana, p.id, JSON_QUOTE('Caminhada e musculacao'), NULL
FROM anamnese_pergunta p
WHERE p.slug = 'tipo_atividade'
AND NOT EXISTS (
    SELECT 1 FROM anamnese_resposta r WHERE r.aluno_id = @aluno_ana AND r.pergunta_id = p.id
);

INSERT INTO anamnese_resposta (aluno_id, pergunta_id, valor, observacao)
SELECT @aluno_ana, p.id, JSON_ARRAY('nenhum'), NULL
FROM anamnese_pergunta p
WHERE p.slug = 'sintomas'
AND NOT EXISTS (
    SELECT 1 FROM anamnese_resposta r WHERE r.aluno_id = @aluno_ana AND r.pergunta_id = p.id
);

INSERT INTO anamnese_resposta (aluno_id, pergunta_id, valor, observacao)
SELECT @aluno_ana, p.id, JSON_ARRAY('perder_peso', 'condicionamento', 'qualidade_vida'), NULL
FROM anamnese_pergunta p
WHERE p.slug = 'objetivos'
AND NOT EXISTS (
    SELECT 1 FROM anamnese_resposta r WHERE r.aluno_id = @aluno_ana AND r.pergunta_id = p.id
);

INSERT INTO anamnese_resposta (aluno_id, pergunta_id, valor, observacao)
SELECT @aluno_bruno, p.id, JSON_QUOTE('sim'), 'Tendinite no ombro direito'
FROM anamnese_pergunta p
WHERE p.slug = 'problema_osseo'
AND NOT EXISTS (
    SELECT 1 FROM anamnese_resposta r WHERE r.aluno_id = @aluno_bruno AND r.pergunta_id = p.id
);

INSERT INTO anamnese_resposta (aluno_id, pergunta_id, valor, observacao)
SELECT @aluno_bruno, p.id, JSON_QUOTE('Tendinite no ombro direito'), NULL
FROM anamnese_pergunta p
WHERE p.slug = 'problema_osseo_obs'
AND NOT EXISTS (
    SELECT 1 FROM anamnese_resposta r WHERE r.aluno_id = @aluno_bruno AND r.pergunta_id = p.id
);

INSERT INTO anamnese_resposta (aluno_id, pergunta_id, valor, observacao)
SELECT @aluno_bruno, p.id, JSON_ARRAY('dor_articular'), NULL
FROM anamnese_pergunta p
WHERE p.slug = 'sintomas'
AND NOT EXISTS (
    SELECT 1 FROM anamnese_resposta r WHERE r.aluno_id = @aluno_bruno AND r.pergunta_id = p.id
);

-- ---------------------------------------------------------------------------
-- Avaliacoes fisicas
-- ---------------------------------------------------------------------------
INSERT INTO avaliacao_fisica (
    aluno_id, avaliador_id, data_avaliacao, peso, altura, imc, cintura, torax,
    braco_dc, braco_d, braco_ec, braco_e, coxa_d, coxa_e, panturrilha_d, panturrilha_e,
    percentual_gordura, percentual_musculo, metabolismo_repouso, idade_biologica, gordura_visceral, observacoes
)
SELECT @aluno_ana, @id_paulo, '2026-03-10', 68.40, 1.65, 25.12, 78.00, 92.00,
       29.00, 27.50, 28.80, 27.20, 56.00, 55.50, 36.00, 35.80,
       28.40, 34.10, 1420, 32, 7.00, 'Avaliacao inicial da Ana.'
WHERE @aluno_ana IS NOT NULL AND NOT EXISTS (
    SELECT 1 FROM avaliacao_fisica WHERE aluno_id = @aluno_ana AND data_avaliacao = '2026-03-10'
);

INSERT INTO avaliacao_fisica (
    aluno_id, avaliador_id, data_avaliacao, peso, altura, imc, cintura, torax,
    braco_dc, braco_d, braco_ec, braco_e, coxa_d, coxa_e, panturrilha_d, panturrilha_e,
    percentual_gordura, percentual_musculo, metabolismo_repouso, idade_biologica, gordura_visceral, observacoes
)
SELECT @aluno_ana, @id_paulo, CURDATE(), 65.10, 1.65, 23.91, 74.00, 91.00,
       29.40, 28.00, 29.10, 27.70, 56.80, 56.20, 36.40, 36.10,
       24.80, 36.20, 1450, 30, 5.00, 'Evolucao positiva apos 6 meses.'
WHERE @aluno_ana IS NOT NULL AND NOT EXISTS (
    SELECT 1 FROM avaliacao_fisica WHERE aluno_id = @aluno_ana AND data_avaliacao = CURDATE()
);

INSERT INTO avaliacao_fisica (
    aluno_id, avaliador_id, data_avaliacao, peso, altura, imc, cintura, torax,
    braco_dc, braco_d, braco_ec, braco_e, coxa_d, coxa_e, panturrilha_d, panturrilha_e,
    percentual_gordura, percentual_musculo, metabolismo_repouso, idade_biologica, gordura_visceral, observacoes
)
SELECT @aluno_bruno, @id_paulo, '2026-04-20', 82.40, 1.76, 26.60, 89.00, 102.00,
       37.00, 34.80, 36.80, 34.50, 58.00, 57.80, 39.00, 38.70,
       21.80, 39.40, 1710, 30, 9.00, 'Retorno aos treinos apos periodo sedentario.'
WHERE @aluno_bruno IS NOT NULL AND NOT EXISTS (
    SELECT 1 FROM avaliacao_fisica WHERE aluno_id = @aluno_bruno AND data_avaliacao = '2026-04-20'
);

INSERT INTO avaliacao_fisica (
    aluno_id, avaliador_id, data_avaliacao, peso, altura, imc, cintura, torax,
    braco_dc, braco_d, braco_ec, braco_e, coxa_d, coxa_e, panturrilha_d, panturrilha_e,
    percentual_gordura, percentual_musculo, metabolismo_repouso, idade_biologica, gordura_visceral, observacoes
)
SELECT @aluno_gabi, @id_mariana, DATE_SUB(CURDATE(), INTERVAL 8 DAY), 61.20, 1.62, 23.34, 70.00, 88.00,
       27.00, 25.80, 26.80, 25.50, 54.00, 53.70, 34.00, 33.80,
       22.10, 37.80, 1380, 28, 4.00, 'Avaliacao recente no mes corrente.'
WHERE @aluno_gabi IS NOT NULL AND NOT EXISTS (
    SELECT 1 FROM avaliacao_fisica WHERE aluno_id = @aluno_gabi AND data_avaliacao = DATE_SUB(CURDATE(), INTERVAL 8 DAY)
);

-- ---------------------------------------------------------------------------
-- Agenda de treinos e presencas
-- ---------------------------------------------------------------------------
INSERT INTO treino_agenda (treino_id, turma_id, espaco_id, instrutor_id, data_hora_inicio, data_hora_fim, status, observacoes)
SELECT @treino_wod, @turma_cross, @espaco_box, @id_paulo,
       TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 10 DAY), '06:00:00'),
       TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 10 DAY), '07:00:00'),
       'concluido', 'Aula concluida para metricas de presenca'
WHERE NOT EXISTS (
    SELECT 1 FROM treino_agenda
    WHERE turma_id = @turma_cross
      AND data_hora_inicio = TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 10 DAY), '06:00:00')
);

INSERT INTO treino_agenda (treino_id, turma_id, espaco_id, instrutor_id, data_hora_inicio, data_hora_fim, status, observacoes)
SELECT @treino_wod2, @turma_cross, @espaco_box, @id_paulo,
       TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '06:00:00'),
       TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '07:00:00'),
       'concluido', NULL
WHERE NOT EXISTS (
    SELECT 1 FROM treino_agenda
    WHERE turma_id = @turma_cross
      AND data_hora_inicio = TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '06:00:00')
);

INSERT INTO treino_agenda (treino_id, turma_id, espaco_id, instrutor_id, data_hora_inicio, data_hora_fim, status, observacoes)
SELECT @treino_func, @turma_func, @espaco_sala, @id_mariana,
       TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '19:00:00'),
       TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '20:00:00'),
       'concluido', NULL
WHERE NOT EXISTS (
    SELECT 1 FROM treino_agenda
    WHERE turma_id = @turma_func
      AND data_hora_inicio = TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '19:00:00')
);

INSERT INTO treino_agenda (treino_id, turma_id, espaco_id, instrutor_id, data_hora_inicio, data_hora_fim, status, observacoes)
SELECT @treino_wod, @turma_cross, @espaco_box, @id_paulo,
       TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '06:00:00'),
       TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '07:00:00'),
       'agendado', 'Proxima aula aberta para gerenciar turma'
WHERE NOT EXISTS (
    SELECT 1 FROM treino_agenda
    WHERE turma_id = @turma_cross
      AND data_hora_inicio = TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '06:00:00')
);

INSERT INTO treino_agenda (treino_id, turma_id, espaco_id, instrutor_id, data_hora_inicio, data_hora_fim, status, observacoes)
SELECT @treino_mob, @turma_mob, @espaco_sala, @id_paulo,
       TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '09:00:00'),
       TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '10:00:00'),
       'agendado', NULL
WHERE NOT EXISTS (
    SELECT 1 FROM treino_agenda
    WHERE turma_id = @turma_mob
      AND data_hora_inicio = TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '09:00:00')
);

INSERT INTO treino_agenda (treino_id, turma_id, espaco_id, instrutor_id, data_hora_inicio, data_hora_fim, status, observacoes)
SELECT @treino_func, @turma_func, @espaco_sala, @id_mariana,
       TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 12 DAY), '19:00:00'),
       TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 12 DAY), '20:00:00'),
       'cancelado', 'Cancelado por falta de energia no box'
WHERE NOT EXISTS (
    SELECT 1 FROM treino_agenda
    WHERE turma_id = @turma_func
      AND data_hora_inicio = TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 12 DAY), '19:00:00')
);

SET @agenda_cross_10 = (
    SELECT id FROM treino_agenda
    WHERE turma_id = @turma_cross
      AND data_hora_inicio = TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 10 DAY), '06:00:00')
    LIMIT 1
);
SET @agenda_cross_3 = (
    SELECT id FROM treino_agenda
    WHERE turma_id = @turma_cross
      AND data_hora_inicio = TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '06:00:00')
    LIMIT 1
);
SET @agenda_func_5 = (
    SELECT id FROM treino_agenda
    WHERE turma_id = @turma_func
      AND data_hora_inicio = TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '19:00:00')
    LIMIT 1
);

INSERT INTO presenca_treino (treino_id, aluno_id, situacao, checkin_time)
SELECT @agenda_cross_10, @aluno_ana, 'presente', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 10 DAY), '05:55:00')
WHERE @agenda_cross_10 IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM presenca_treino WHERE treino_id = @agenda_cross_10 AND aluno_id = @aluno_ana);

INSERT INTO presenca_treino (treino_id, aluno_id, situacao, checkin_time)
SELECT @agenda_cross_10, @aluno_bruno, 'ausente', NULL
WHERE @agenda_cross_10 IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM presenca_treino WHERE treino_id = @agenda_cross_10 AND aluno_id = @aluno_bruno);

INSERT INTO presenca_treino (treino_id, aluno_id, situacao, checkin_time)
SELECT @agenda_cross_10, @aluno_henrique, 'presente', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 10 DAY), '06:02:00')
WHERE @agenda_cross_10 IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM presenca_treino WHERE treino_id = @agenda_cross_10 AND aluno_id = @aluno_henrique);

INSERT INTO presenca_treino (treino_id, aluno_id, situacao, checkin_time)
SELECT @agenda_cross_3, @aluno_ana, 'presente', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 3 DAY), '05:58:00')
WHERE @agenda_cross_3 IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM presenca_treino WHERE treino_id = @agenda_cross_3 AND aluno_id = @aluno_ana);

INSERT INTO presenca_treino (treino_id, aluno_id, situacao, checkin_time)
SELECT @agenda_cross_3, @aluno_bruno, 'justificado', NULL
WHERE @agenda_cross_3 IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM presenca_treino WHERE treino_id = @agenda_cross_3 AND aluno_id = @aluno_bruno);

INSERT INTO presenca_treino (treino_id, aluno_id, situacao, checkin_time)
SELECT @agenda_func_5, @aluno_carla, 'presente', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '18:55:00')
WHERE @agenda_func_5 IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM presenca_treino WHERE treino_id = @agenda_func_5 AND aluno_id = @aluno_carla);

INSERT INTO presenca_treino (treino_id, aluno_id, situacao, checkin_time)
SELECT @agenda_func_5, @aluno_gabi, 'presente', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '19:01:00')
WHERE @agenda_func_5 IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM presenca_treino WHERE treino_id = @agenda_func_5 AND aluno_id = @aluno_gabi);

INSERT INTO presenca_treino (treino_id, aluno_id, situacao, checkin_time)
SELECT @agenda_func_5, @aluno_henrique, 'ausente', NULL
WHERE @agenda_func_5 IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM presenca_treino WHERE treino_id = @agenda_func_5 AND aluno_id = @aluno_henrique);

-- ---------------------------------------------------------------------------
-- Financeiro
-- ---------------------------------------------------------------------------
INSERT INTO servico (nome, descricao, tipo, valor_base, recorrente, ativo)
SELECT 'Mensalidade Crossfit', 'Acesso ilimitado as turmas de Crossfit', 'mensalidade', 189.90, TRUE, TRUE
WHERE NOT EXISTS (SELECT 1 FROM servico WHERE nome = 'Mensalidade Crossfit');

INSERT INTO servico (nome, descricao, tipo, valor_base, recorrente, ativo)
SELECT 'Mensalidade Funcional', 'Acesso as turmas de funcional', 'mensalidade', 149.90, TRUE, TRUE
WHERE NOT EXISTS (SELECT 1 FROM servico WHERE nome = 'Mensalidade Funcional');

INSERT INTO servico (nome, descricao, tipo, valor_base, recorrente, ativo)
SELECT 'Avaliacao Fisica', 'Avaliacao corporal completa', 'avaliacao', 80.00, FALSE, TRUE
WHERE NOT EXISTS (SELECT 1 FROM servico WHERE nome = 'Avaliacao Fisica');

INSERT INTO servico (nome, descricao, tipo, valor_base, recorrente, ativo)
SELECT 'Personal Training', 'Sessao avulsa de personal', 'personal', 120.00, FALSE, TRUE
WHERE NOT EXISTS (SELECT 1 FROM servico WHERE nome = 'Personal Training');

INSERT INTO servico (nome, descricao, tipo, valor_base, recorrente, ativo)
SELECT 'Taxa de matricula', 'Taxa unica de adesao', 'taxa', 50.00, FALSE, TRUE
WHERE NOT EXISTS (SELECT 1 FROM servico WHERE nome = 'Taxa de matricula');

INSERT INTO servico (nome, descricao, tipo, valor_base, recorrente, ativo)
SELECT 'Servico Inativo Teste', 'Nao deve aparecer nas listagens ativas', 'outro', 10.00, FALSE, FALSE
WHERE NOT EXISTS (SELECT 1 FROM servico WHERE nome = 'Servico Inativo Teste');

SET @svc_cross = (SELECT id FROM servico WHERE nome = 'Mensalidade Crossfit' LIMIT 1);
SET @svc_func = (SELECT id FROM servico WHERE nome = 'Mensalidade Funcional' LIMIT 1);
SET @svc_aval = (SELECT id FROM servico WHERE nome = 'Avaliacao Fisica' LIMIT 1);
SET @svc_pers = (SELECT id FROM servico WHERE nome = 'Personal Training' LIMIT 1);
SET @svc_taxa = (SELECT id FROM servico WHERE nome = 'Taxa de matricula' LIMIT 1);

INSERT INTO plano (nome, descricao, periodicidade, valor, ativo)
SELECT 'Plano Crossfit Mensal', 'Mensalidade de Crossfit', 'mensal', 189.90, TRUE
WHERE NOT EXISTS (SELECT 1 FROM plano WHERE nome = 'Plano Crossfit Mensal');

INSERT INTO plano (nome, descricao, periodicidade, valor, ativo)
SELECT 'Plano Funcional Mensal', 'Mensalidade de funcional', 'mensal', 149.90, TRUE
WHERE NOT EXISTS (SELECT 1 FROM plano WHERE nome = 'Plano Funcional Mensal');

INSERT INTO plano (nome, descricao, periodicidade, valor, ativo)
SELECT 'Plano Completo Trimestral', 'Crossfit + funcional com desconto', 'trimestral', 479.70, TRUE
WHERE NOT EXISTS (SELECT 1 FROM plano WHERE nome = 'Plano Completo Trimestral');

INSERT INTO plano (nome, descricao, periodicidade, valor, ativo)
SELECT 'Plano Avulso Avaliacao', 'Pacote pontual de avaliacao', 'avulso', 80.00, TRUE
WHERE NOT EXISTS (SELECT 1 FROM plano WHERE nome = 'Plano Avulso Avaliacao');

SET @plano_cross = (SELECT id FROM plano WHERE nome = 'Plano Crossfit Mensal' LIMIT 1);
SET @plano_func = (SELECT id FROM plano WHERE nome = 'Plano Funcional Mensal' LIMIT 1);
SET @plano_completo = (SELECT id FROM plano WHERE nome = 'Plano Completo Trimestral' LIMIT 1);
SET @plano_aval = (SELECT id FROM plano WHERE nome = 'Plano Avulso Avaliacao' LIMIT 1);

INSERT INTO plano_item (plano_id, servico_id, quantidade, valor_unitario)
SELECT @plano_cross, @svc_cross, 1, 189.90
WHERE NOT EXISTS (SELECT 1 FROM plano_item WHERE plano_id = @plano_cross AND servico_id = @svc_cross);

INSERT INTO plano_item (plano_id, servico_id, quantidade, valor_unitario)
SELECT @plano_func, @svc_func, 1, 149.90
WHERE NOT EXISTS (SELECT 1 FROM plano_item WHERE plano_id = @plano_func AND servico_id = @svc_func);

INSERT INTO plano_item (plano_id, servico_id, quantidade, valor_unitario)
SELECT @plano_completo, @svc_cross, 1, 179.90
WHERE NOT EXISTS (SELECT 1 FROM plano_item WHERE plano_id = @plano_completo AND servico_id = @svc_cross);

INSERT INTO plano_item (plano_id, servico_id, quantidade, valor_unitario)
SELECT @plano_completo, @svc_func, 1, 139.90
WHERE NOT EXISTS (SELECT 1 FROM plano_item WHERE plano_id = @plano_completo AND servico_id = @svc_func);

INSERT INTO plano_item (plano_id, servico_id, quantidade, valor_unitario)
SELECT @plano_aval, @svc_aval, 1, 80.00
WHERE NOT EXISTS (SELECT 1 FROM plano_item WHERE plano_id = @plano_aval AND servico_id = @svc_aval);

-- Contrato ativo da Ana (pago + aberto)
INSERT INTO contrato (aluno_id, plano_id, data_inicio, data_fim, dia_vencimento, valor_contratado, desconto, multa_percentual, juros_percentual, status, observacoes)
SELECT @aluno_ana, @plano_cross, '2026-03-02', NULL, 10, 189.90, 0.00, 2.00, 1.00, 'ativo', 'Contrato vigente da Ana'
WHERE NOT EXISTS (SELECT 1 FROM contrato WHERE aluno_id = @aluno_ana AND plano_id = @plano_cross AND status = 'ativo');

-- Contrato ativo do Bruno (cobranca vencida)
INSERT INTO contrato (aluno_id, plano_id, data_inicio, data_fim, dia_vencimento, valor_contratado, desconto, multa_percentual, juros_percentual, status, observacoes)
SELECT @aluno_bruno, @plano_cross, '2026-04-10', NULL, 5, 189.90, 0.00, 2.00, 1.00, 'ativo', 'Em atraso para testar cobranca vencida'
WHERE NOT EXISTS (SELECT 1 FROM contrato WHERE aluno_id = @aluno_bruno AND plano_id = @plano_cross AND status = 'ativo');

-- Contrato pausado da Carla
INSERT INTO contrato (aluno_id, plano_id, data_inicio, data_fim, dia_vencimento, valor_contratado, desconto, multa_percentual, juros_percentual, status, observacoes)
SELECT @aluno_carla, @plano_func, '2026-05-05', NULL, 8, 149.90, 10.00, 2.00, 1.00, 'pausado', 'Pausa temporaria combinada com a aluna'
WHERE NOT EXISTS (SELECT 1 FROM contrato WHERE aluno_id = @aluno_carla AND plano_id = @plano_func);

-- Contrato encerrado do Fabio
INSERT INTO contrato (aluno_id, plano_id, data_inicio, data_fim, dia_vencimento, valor_contratado, desconto, multa_percentual, juros_percentual, status, observacoes)
SELECT @aluno_fabio, @plano_func, '2025-11-12', '2026-05-12', 12, 149.90, 0.00, 2.00, 1.00, 'encerrado', 'Contrato encerrado apos 6 meses'
WHERE NOT EXISTS (SELECT 1 FROM contrato WHERE aluno_id = @aluno_fabio AND plano_id = @plano_func);

-- Contrato cancelado do Diego
INSERT INTO contrato (aluno_id, plano_id, data_inicio, data_fim, dia_vencimento, valor_contratado, desconto, multa_percentual, juros_percentual, status, observacoes)
SELECT @aluno_diego, @plano_cross, '2026-01-20', '2026-03-01', 10, 189.90, 0.00, 2.00, 1.00, 'cancelado', 'Aluno inativo e contrato cancelado'
WHERE NOT EXISTS (SELECT 1 FROM contrato WHERE aluno_id = @aluno_diego AND plano_id = @plano_cross);

-- Contrato completo do Henrique
INSERT INTO contrato (aluno_id, plano_id, data_inicio, data_fim, dia_vencimento, valor_contratado, desconto, multa_percentual, juros_percentual, status, observacoes)
SELECT @aluno_henrique, @plano_completo, '2026-07-01', NULL, 15, 479.70, 20.00, 2.00, 1.00, 'ativo', 'Plano trimestral com desconto'
WHERE NOT EXISTS (SELECT 1 FROM contrato WHERE aluno_id = @aluno_henrique AND plano_id = @plano_completo);

-- Contrato da Gabi com pagamento parcial
INSERT INTO contrato (aluno_id, plano_id, data_inicio, data_fim, dia_vencimento, valor_contratado, desconto, multa_percentual, juros_percentual, status, observacoes)
SELECT @aluno_gabi, @plano_func, '2026-06-18', NULL, 18, 149.90, 0.00, 2.00, 1.00, 'ativo', 'Pagamento parcial no mes vigente'
WHERE NOT EXISTS (SELECT 1 FROM contrato WHERE aluno_id = @aluno_gabi AND plano_id = @plano_func AND status = 'ativo');

SET @ctr_ana = (SELECT id FROM contrato WHERE aluno_id = @aluno_ana AND status = 'ativo' LIMIT 1);
SET @ctr_bruno = (SELECT id FROM contrato WHERE aluno_id = @aluno_bruno AND status = 'ativo' LIMIT 1);
SET @ctr_carla = (SELECT id FROM contrato WHERE aluno_id = @aluno_carla LIMIT 1);
SET @ctr_fabio = (SELECT id FROM contrato WHERE aluno_id = @aluno_fabio LIMIT 1);
SET @ctr_diego = (SELECT id FROM contrato WHERE aluno_id = @aluno_diego LIMIT 1);
SET @ctr_henrique = (SELECT id FROM contrato WHERE aluno_id = @aluno_henrique LIMIT 1);
SET @ctr_gabi = (SELECT id FROM contrato WHERE aluno_id = @aluno_gabi AND status = 'ativo' LIMIT 1);

INSERT INTO contrato_item (contrato_id, servico_id, descricao, quantidade, valor_unitario, valor_desconto)
SELECT @ctr_ana, @svc_cross, 'Mensalidade Crossfit', 1, 189.90, 0.00
WHERE @ctr_ana IS NOT NULL AND NOT EXISTS (SELECT 1 FROM contrato_item WHERE contrato_id = @ctr_ana AND servico_id = @svc_cross);

INSERT INTO contrato_item (contrato_id, servico_id, descricao, quantidade, valor_unitario, valor_desconto)
SELECT @ctr_ana, @svc_taxa, 'Taxa de matricula', 1, 50.00, 0.00
WHERE @ctr_ana IS NOT NULL AND NOT EXISTS (SELECT 1 FROM contrato_item WHERE contrato_id = @ctr_ana AND servico_id = @svc_taxa);

INSERT INTO contrato_item (contrato_id, servico_id, descricao, quantidade, valor_unitario, valor_desconto)
SELECT @ctr_bruno, @svc_cross, 'Mensalidade Crossfit', 1, 189.90, 0.00
WHERE @ctr_bruno IS NOT NULL AND NOT EXISTS (SELECT 1 FROM contrato_item WHERE contrato_id = @ctr_bruno);

INSERT INTO contrato_item (contrato_id, servico_id, descricao, quantidade, valor_unitario, valor_desconto)
SELECT @ctr_carla, @svc_func, 'Mensalidade Funcional', 1, 149.90, 10.00
WHERE @ctr_carla IS NOT NULL AND NOT EXISTS (SELECT 1 FROM contrato_item WHERE contrato_id = @ctr_carla);

INSERT INTO contrato_item (contrato_id, servico_id, descricao, quantidade, valor_unitario, valor_desconto)
SELECT @ctr_fabio, @svc_func, 'Mensalidade Funcional', 1, 149.90, 0.00
WHERE @ctr_fabio IS NOT NULL AND NOT EXISTS (SELECT 1 FROM contrato_item WHERE contrato_id = @ctr_fabio);

INSERT INTO contrato_item (contrato_id, servico_id, descricao, quantidade, valor_unitario, valor_desconto)
SELECT @ctr_diego, @svc_cross, 'Mensalidade Crossfit', 1, 189.90, 0.00
WHERE @ctr_diego IS NOT NULL AND NOT EXISTS (SELECT 1 FROM contrato_item WHERE contrato_id = @ctr_diego);

INSERT INTO contrato_item (contrato_id, servico_id, descricao, quantidade, valor_unitario, valor_desconto)
SELECT @ctr_henrique, @svc_cross, 'Mensalidade Crossfit', 1, 179.90, 10.00
WHERE @ctr_henrique IS NOT NULL AND NOT EXISTS (SELECT 1 FROM contrato_item WHERE contrato_id = @ctr_henrique AND servico_id = @svc_cross);

INSERT INTO contrato_item (contrato_id, servico_id, descricao, quantidade, valor_unitario, valor_desconto)
SELECT @ctr_henrique, @svc_func, 'Mensalidade Funcional', 1, 139.90, 10.00
WHERE @ctr_henrique IS NOT NULL AND NOT EXISTS (SELECT 1 FROM contrato_item WHERE contrato_id = @ctr_henrique AND servico_id = @svc_func);

INSERT INTO contrato_item (contrato_id, servico_id, descricao, quantidade, valor_unitario, valor_desconto)
SELECT @ctr_gabi, @svc_func, 'Mensalidade Funcional', 1, 149.90, 0.00
WHERE @ctr_gabi IS NOT NULL AND NOT EXISTS (SELECT 1 FROM contrato_item WHERE contrato_id = @ctr_gabi);

SET @comp_atual = DATE_FORMAT(CURDATE(), '%Y-%m');
SET @comp_ant = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m');
SET @comp_ant2 = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m');

-- Ana: mes anterior pago, mes atual aberto
INSERT INTO cobranca (contrato_id, aluno_id, competencia, descricao, valor_original, desconto, multa, juros, valor_final, data_vencimento, status)
SELECT @ctr_ana, @aluno_ana, @comp_ant, CONCAT('Mensalidade ', @comp_ant), 189.90, 0.00, 0.00, 0.00, 189.90, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-10'), 'paga'
WHERE @ctr_ana IS NOT NULL AND NOT EXISTS (SELECT 1 FROM cobranca WHERE contrato_id = @ctr_ana AND competencia = @comp_ant);

INSERT INTO cobranca (contrato_id, aluno_id, competencia, descricao, valor_original, desconto, multa, juros, valor_final, data_vencimento, status)
SELECT @ctr_ana, @aluno_ana, @comp_atual, CONCAT('Mensalidade ', @comp_atual), 189.90, 0.00, 0.00, 0.00, 189.90, DATE_FORMAT(CURDATE(), '%Y-%m-10'), 'aberta'
WHERE @ctr_ana IS NOT NULL AND NOT EXISTS (SELECT 1 FROM cobranca WHERE contrato_id = @ctr_ana AND competencia = @comp_atual);

-- Bruno: vencida
INSERT INTO cobranca (contrato_id, aluno_id, competencia, descricao, valor_original, desconto, multa, juros, valor_final, data_vencimento, status)
SELECT @ctr_bruno, @aluno_bruno, @comp_ant, CONCAT('Mensalidade ', @comp_ant), 189.90, 0.00, 3.80, 1.90, 195.60, DATE_SUB(CURDATE(), INTERVAL 20 DAY), 'vencida'
WHERE @ctr_bruno IS NOT NULL AND NOT EXISTS (SELECT 1 FROM cobranca WHERE contrato_id = @ctr_bruno AND competencia = @comp_ant);

-- Henrique: paga no trimestre
INSERT INTO cobranca (contrato_id, aluno_id, competencia, descricao, valor_original, desconto, multa, juros, valor_final, data_vencimento, status)
SELECT @ctr_henrique, @aluno_henrique, '2026-07', 'Plano completo 2026-07', 479.70, 20.00, 0.00, 0.00, 459.70, '2026-07-15', 'paga'
WHERE @ctr_henrique IS NOT NULL AND NOT EXISTS (SELECT 1 FROM cobranca WHERE contrato_id = @ctr_henrique AND competencia = '2026-07');

-- Gabi: aberta com pagamento parcial
INSERT INTO cobranca (contrato_id, aluno_id, competencia, descricao, valor_original, desconto, multa, juros, valor_final, data_vencimento, status)
SELECT @ctr_gabi, @aluno_gabi, @comp_atual, CONCAT('Mensalidade ', @comp_atual), 149.90, 0.00, 0.00, 0.00, 149.90, DATE_FORMAT(CURDATE(), '%Y-%m-18'), 'aberta'
WHERE @ctr_gabi IS NOT NULL AND NOT EXISTS (SELECT 1 FROM cobranca WHERE contrato_id = @ctr_gabi AND competencia = @comp_atual);

-- Fabio: historico pago
INSERT INTO cobranca (contrato_id, aluno_id, competencia, descricao, valor_original, desconto, multa, juros, valor_final, data_vencimento, status)
SELECT @ctr_fabio, @aluno_fabio, '2026-04', 'Mensalidade 2026-04', 149.90, 0.00, 0.00, 0.00, 149.90, '2026-04-12', 'paga'
WHERE @ctr_fabio IS NOT NULL AND NOT EXISTS (SELECT 1 FROM cobranca WHERE contrato_id = @ctr_fabio AND competencia = '2026-04');

-- Diego: cobranca cancelada
INSERT INTO cobranca (contrato_id, aluno_id, competencia, descricao, valor_original, desconto, multa, juros, valor_final, data_vencimento, status)
SELECT @ctr_diego, @aluno_diego, '2026-02', 'Mensalidade 2026-02', 189.90, 0.00, 0.00, 0.00, 189.90, '2026-02-10', 'cancelada'
WHERE @ctr_diego IS NOT NULL AND NOT EXISTS (SELECT 1 FROM cobranca WHERE contrato_id = @ctr_diego AND competencia = '2026-02');

SET @cob_ana_paga = (SELECT id FROM cobranca WHERE contrato_id = @ctr_ana AND competencia = @comp_ant LIMIT 1);
SET @cob_henrique = (SELECT id FROM cobranca WHERE contrato_id = @ctr_henrique AND competencia = '2026-07' LIMIT 1);
SET @cob_gabi = (SELECT id FROM cobranca WHERE contrato_id = @ctr_gabi AND competencia = @comp_atual LIMIT 1);
SET @cob_fabio = (SELECT id FROM cobranca WHERE contrato_id = @ctr_fabio AND competencia = '2026-04' LIMIT 1);

INSERT INTO pagamento (cobranca_id, valor_pago, data_pagamento, forma_pagamento, transacao_id, observacoes, registrado_por)
SELECT @cob_ana_paga, 189.90, TIMESTAMP(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-09'), '10:15:00'), 'pix', 'PIX-ANA-001', 'Pagamento integral', @id_camila
WHERE @cob_ana_paga IS NOT NULL AND NOT EXISTS (SELECT 1 FROM pagamento WHERE cobranca_id = @cob_ana_paga);

INSERT INTO pagamento (cobranca_id, valor_pago, data_pagamento, forma_pagamento, transacao_id, observacoes, registrado_por)
SELECT @cob_henrique, 459.70, '2026-07-14 16:40:00', 'cartao', 'CARD-HEN-001', 'Plano trimestral', @id_gerente
WHERE @cob_henrique IS NOT NULL AND NOT EXISTS (SELECT 1 FROM pagamento WHERE cobranca_id = @cob_henrique);

INSERT INTO pagamento (cobranca_id, valor_pago, data_pagamento, forma_pagamento, transacao_id, observacoes, registrado_por)
SELECT @cob_gabi, 50.00, TIMESTAMP(CURDATE(), '11:20:00'), 'dinheiro', NULL, 'Pagamento parcial em dinheiro', @id_camila
WHERE @cob_gabi IS NOT NULL AND NOT EXISTS (SELECT 1 FROM pagamento WHERE cobranca_id = @cob_gabi AND valor_pago = 50.00);

INSERT INTO pagamento (cobranca_id, valor_pago, data_pagamento, forma_pagamento, transacao_id, observacoes, registrado_por)
SELECT @cob_fabio, 149.90, '2026-04-11 09:00:00', 'boleto', 'BOL-FAB-001', NULL, @admin_id
WHERE @cob_fabio IS NOT NULL AND NOT EXISTS (SELECT 1 FROM pagamento WHERE cobranca_id = @cob_fabio);

INSERT INTO audit_logs (user_id, action, operation, module, result)
SELECT @admin_id, 'seed_applied', 'insert', 'sistema', 'success'
WHERE NOT EXISTS (SELECT 1 FROM audit_logs WHERE action = 'seed_applied');

COMMIT;

SELECT 'Seed aplicado. Use Senha@Teste123 nos colaboradores.' AS mensagem;
SELECT tipo_usuario, COUNT(*) AS total, SUM(ativo) AS ativos
FROM usuario
GROUP BY tipo_usuario;
SELECT status, COUNT(*) AS total FROM contrato GROUP BY status;
SELECT status, COUNT(*) AS total FROM cobranca GROUP BY status;
