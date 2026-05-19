-- Seed de apoio para testar a tela gerenciar_turma
-- Objetivo:
-- 1. Criar 1 instrutor e 1 estagiario
-- 2. Criar modalidades
-- 3. Criar treinos-base reutilizaveis
-- 4. Criar espacos de treino
-- 5. Criar uma turma de exemplo com horarios
--
-- O script foi escrito para ser rerodado sem duplicar os mesmos registros.

START TRANSACTION;

-- Enderecos de apoio
INSERT INTO endereco (logradouro, numero, cidade, bairro, cep, complemento)
SELECT 'Rua Teste Instrutor', '101', 'Sao Paulo', 'Centro', '01010000', 'Seed gerenciar turma'
WHERE NOT EXISTS (
    SELECT 1
    FROM endereco
    WHERE logradouro = 'Rua Teste Instrutor'
      AND numero = '101'
      AND cep = '01010000'
);

INSERT INTO endereco (logradouro, numero, cidade, bairro, cep, complemento)
SELECT 'Rua Teste Estagiario', '202', 'Sao Paulo', 'Centro', '01010001', 'Seed gerenciar turma'
WHERE NOT EXISTS (
    SELECT 1
    FROM endereco
    WHERE logradouro = 'Rua Teste Estagiario'
      AND numero = '202'
      AND cep = '01010001'
);

SET @endereco_instrutor_id = (
    SELECT id
    FROM endereco
    WHERE logradouro = 'Rua Teste Instrutor'
      AND numero = '101'
      AND cep = '01010000'
    ORDER BY id DESC
    LIMIT 1
);

SET @endereco_estagiario_id = (
    SELECT id
    FROM endereco
    WHERE logradouro = 'Rua Teste Estagiario'
      AND numero = '202'
      AND cep = '01010001'
    ORDER BY id DESC
    LIMIT 1
);

-- Usuarios funcionarios
INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario, ativo)
SELECT
    'Paulo',
    'Instrutor',
    '90000000001',
    'paulo.instrutor.teste@ctt.local',
    '$argon2id$v=19$m=65536,t=4,p=1$enBzQTh6a3NuRTAwWVFFNg$D1fBTREiUz8MPOsv4hl6WI7EgKRbK4+9nl7wf6+U1Sw',
    '1988-04-10',
    'M',
    @endereco_instrutor_id,
    'funcionario',
    TRUE
WHERE NOT EXISTS (
    SELECT 1
    FROM usuario
    WHERE cpf = '90000000001'
       OR email = 'paulo.instrutor.teste@ctt.local'
);

INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario, ativo)
SELECT
    'Larissa',
    'Estagiaria',
    '90000000002',
    'larissa.estagiaria.teste@ctt.local',
    '$argon2id$v=19$m=65536,t=4,p=1$enBzQTh6a3NuRTAwWVFFNg$D1fBTREiUz8MPOsv4hl6WI7EgKRbK4+9nl7wf6+U1Sw',
    '2001-09-18',
    'F',
    @endereco_estagiario_id,
    'funcionario',
    TRUE
WHERE NOT EXISTS (
    SELECT 1
    FROM usuario
    WHERE cpf = '90000000002'
       OR email = 'larissa.estagiaria.teste@ctt.local'
);

SET @instrutor_usuario_id = (
    SELECT id
    FROM usuario
    WHERE cpf = '90000000001'
    LIMIT 1
);

SET @estagiario_usuario_id = (
    SELECT id
    FROM usuario
    WHERE cpf = '90000000002'
    LIMIT 1
);

SET @cargo_instrutor_id = (
    SELECT id
    FROM cargo
    WHERE nome = 'Instrutor'
    LIMIT 1
);

SET @cargo_estagiario_id = (
    SELECT id
    FROM cargo
    WHERE nome = 'Estagiário'
    LIMIT 1
);

INSERT INTO funcionario (usuario_id, cargo_id, registro_profissional, observacoes)
SELECT
    @instrutor_usuario_id,
    @cargo_instrutor_id,
    'CREF-TESTE-001',
    'Instrutor criado pelo seed de gerenciar turma'
WHERE @instrutor_usuario_id IS NOT NULL
  AND @cargo_instrutor_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM funcionario
      WHERE usuario_id = @instrutor_usuario_id
  );

INSERT INTO funcionario (usuario_id, cargo_id, registro_profissional, observacoes)
SELECT
    @estagiario_usuario_id,
    @cargo_estagiario_id,
    NULL,
    'Estagiaria criada pelo seed de gerenciar turma'
WHERE @estagiario_usuario_id IS NOT NULL
  AND @cargo_estagiario_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM funcionario
      WHERE usuario_id = @estagiario_usuario_id
  );

-- Modalidades
INSERT INTO modalidade (nome, descricao, ativo)
SELECT 'Crossfit', 'Treinamento de alta intensidade com movimentos variados.', TRUE
WHERE NOT EXISTS (
    SELECT 1 FROM modalidade WHERE nome = 'Crossfit'
);

INSERT INTO modalidade (nome, descricao, ativo)
SELECT 'Funcional', 'Treinos funcionais para condicionamento, mobilidade e resistencia.', TRUE
WHERE NOT EXISTS (
    SELECT 1 FROM modalidade WHERE nome = 'Funcional'
);

INSERT INTO modalidade (nome, descricao, ativo)
SELECT 'Mobilidade', 'Sessao voltada para mobilidade articular, alongamento e recuperacao.', TRUE
WHERE NOT EXISTS (
    SELECT 1 FROM modalidade WHERE nome = 'Mobilidade'
);

SET @modalidade_crossfit_id = (
    SELECT id FROM modalidade WHERE nome = 'Crossfit' LIMIT 1
);

SET @modalidade_funcional_id = (
    SELECT id FROM modalidade WHERE nome = 'Funcional' LIMIT 1
);

SET @modalidade_mobilidade_id = (
    SELECT id FROM modalidade WHERE nome = 'Mobilidade' LIMIT 1
);

-- Treinos-base
INSERT INTO treino (nome, modalidade_id, descricao, ativo)
SELECT 'WOD Iniciante', @modalidade_crossfit_id, 'Treino-base introdutorio para alunos iniciantes.', TRUE
WHERE @modalidade_crossfit_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM treino
      WHERE nome = 'WOD Iniciante'
        AND modalidade_id = @modalidade_crossfit_id
  );

INSERT INTO treino (nome, modalidade_id, descricao, ativo)
SELECT 'WOD Intermediario', @modalidade_crossfit_id, 'Treino-base com progressao tecnica e intensidade moderada.', TRUE
WHERE @modalidade_crossfit_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM treino
      WHERE nome = 'WOD Intermediario'
        AND modalidade_id = @modalidade_crossfit_id
  );

INSERT INTO treino (nome, modalidade_id, descricao, ativo)
SELECT 'Circuito Funcional Base', @modalidade_funcional_id, 'Treino-base em circuito para condicionamento geral.', TRUE
WHERE @modalidade_funcional_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM treino
      WHERE nome = 'Circuito Funcional Base'
        AND modalidade_id = @modalidade_funcional_id
  );

INSERT INTO treino (nome, modalidade_id, descricao, ativo)
SELECT 'Funcional Core e Equilibrio', @modalidade_funcional_id, 'Treino-base com foco em core, postura e estabilidade.', TRUE
WHERE @modalidade_funcional_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM treino
      WHERE nome = 'Funcional Core e Equilibrio'
        AND modalidade_id = @modalidade_funcional_id
  );

INSERT INTO treino (nome, modalidade_id, descricao, ativo)
SELECT 'Sessao de Mobilidade', @modalidade_mobilidade_id, 'Treino-base leve para aquecimento e recuperacao ativa.', TRUE
WHERE @modalidade_mobilidade_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM treino
      WHERE nome = 'Sessao de Mobilidade'
        AND modalidade_id = @modalidade_mobilidade_id
  );

-- Espacos de treino para viabilizar o agendamento na tela
INSERT INTO espaco_treino (nome, capacidade_minima, capacidade_maxima, equipamentos, ativo)
SELECT 'Box Principal - Seed', 5, 30, 'Barras, anilhas, remos, cordas e caixas', TRUE
WHERE NOT EXISTS (
    SELECT 1 FROM espaco_treino WHERE nome = 'Box Principal - Seed'
);

INSERT INTO espaco_treino (nome, capacidade_minima, capacidade_maxima, equipamentos, ativo)
SELECT 'Sala Funcional - Seed', 4, 20, 'Kettlebells, elásticos, cones e colchonetes', TRUE
WHERE NOT EXISTS (
    SELECT 1 FROM espaco_treino WHERE nome = 'Sala Funcional - Seed'
);

-- Turma de exemplo para abrir diretamente no gerenciar_turma
INSERT INTO turma (nome, instrutor_id, capacidade_minima, capacidade_maxima, ativo)
SELECT
    'Turma Seed - Crossfit 06h',
    @instrutor_usuario_id,
    5,
    20,
    TRUE
WHERE @instrutor_usuario_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1 FROM turma WHERE nome = 'Turma Seed - Crossfit 06h'
  );

SET @turma_seed_id = (
    SELECT id
    FROM turma
    WHERE nome = 'Turma Seed - Crossfit 06h'
    LIMIT 1
);

INSERT INTO turma_config_horario (turma_id, dia_semana, hora_inicio, hora_fim)
SELECT @turma_seed_id, 'segunda', '06:00:00', '07:00:00'
WHERE @turma_seed_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM turma_config_horario
      WHERE turma_id = @turma_seed_id
        AND dia_semana = 'segunda'
  );

INSERT INTO turma_config_horario (turma_id, dia_semana, hora_inicio, hora_fim)
SELECT @turma_seed_id, 'quarta', '06:00:00', '07:00:00'
WHERE @turma_seed_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM turma_config_horario
      WHERE turma_id = @turma_seed_id
        AND dia_semana = 'quarta'
  );

INSERT INTO turma_config_horario (turma_id, dia_semana, hora_inicio, hora_fim)
SELECT @turma_seed_id, 'sexta', '06:00:00', '07:00:00'
WHERE @turma_seed_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM turma_config_horario
      WHERE turma_id = @turma_seed_id
        AND dia_semana = 'sexta'
  );

COMMIT;

-- Consulta rapida para localizar a turma de teste:
-- SELECT id, nome, instrutor_id FROM turma WHERE nome = 'Turma Seed - Crossfit 06h';
