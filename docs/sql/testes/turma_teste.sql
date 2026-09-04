-- 1. Primeiro, cadastramos o usuário que será o Instrutor
INSERT INTO endereco (logradouro, numero, cidade, bairro, cep) 
VALUES ('Rua das Flores', '50', 'São Paulo', 'Pinheiros', '05422000');

INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, tipo_usuario, endereco_id)
VALUES ('Ricardo', 'Silva', '12345678901', 'ricardo.instrutor@email.com', 'senha_hash_aqui', '1985-05-20', 'funcionario', 2);

-- 2. Vinculamos o usuário à tabela de funcionário como Instrutor (cargo_id = 2 conforme seu INSERT inicial)
INSERT INTO funcionario (usuario_id, cargo_id, registro_professional)
VALUES (2, 2, 'CREF-12345G/SP');

-- 3. Criamos a Modalidade e o Treino base
INSERT INTO modalidade (nome, descricao) 
VALUES ('Crossfit', 'Treinamento de alta intensidade com movimentos variados.');

INSERT INTO treino (nome, modalidade_id, descricao) 
VALUES ('WOD Iniciante', 1, 'Treino do dia focado em técnica e adaptação.');

-- 4. Criamos a Turma vinculada ao Instrutor
INSERT INTO turma (nome, instrutor_id, capacidade_minima, capacidade_maxima)
VALUES ('Crossfit Matutino - Turma A', 2, 5, 20);

-- 5. Definimos os horários da Turma (Segunda, Quarta e Sexta às 07:00)
INSERT INTO turma_config_horario (turma_id, dia_semana, hora_inicio, hora_fim) VALUES 
(1, 'segunda', '07:00:00', '08:00:00'),
(1, 'quarta', '07:00:00', '08:00:00'),
(1, 'sexta', '07:00:00', '08:00:00');


INSERT INTO espaco_treino (nome, capacidade_minima, capacidade_maxima, equipamentos)
VALUES ('Box Principal', 5, 30, 'Barras, Anilhas, Remos e Box Jumps');

-- 2. Agora vamos AGENDAR os treinos para a Turma (ID 1)
-- Vamos agendar o treino de Segunda e o de Quarta
INSERT INTO treino_agenda (treino_id, turma_id, espaco_id, instrutor_id, data_hora_inicio, data_hora_fim, status) 
VALUES 
-- Treino de Segunda-feira
(1, 1, 1, 2, '2023-10-30 07:00:00', '2023-10-30 08:00:00', 'agendado'),

-- Treino de Quarta-feira
(1, 1, 1, 2, '2023-11-01 07:00:00', '2023-11-01 08:00:00', 'agendado');
