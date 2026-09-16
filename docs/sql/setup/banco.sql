DROP DATABASE IF EXISTS db_centro_treinamento;
CREATE DATABASE db_centro_treinamento CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_centro_treinamento;

CREATE TABLE endereco (
    id INT AUTO_INCREMENT PRIMARY KEY,
    logradouro VARCHAR(200),
    numero VARCHAR(10),
    cidade VARCHAR(100),
    bairro VARCHAR(100),
    cep CHAR(8),
    complemento VARCHAR(100)
);

CREATE TABLE cargo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao VARCHAR(255),
    salario_base DECIMAL(10,2) DEFAULT 0.00,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    sobrenome VARCHAR(100) NOT NULL,
    cpf CHAR(11) UNIQUE NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    senha VARCHAR(300) NOT NULL,
    data_nascimento DATE NOT NULL,
    genero ENUM('M', 'F', 'O') DEFAULT 'O',
    endereco_id INT,
    tipo_usuario ENUM('admin', 'funcionario', 'aluno') NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (endereco_id) REFERENCES endereco(id)
);

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE,
    INDEX idx_password_resets_usuario (usuario_id),
    INDEX idx_password_resets_expires_at (expires_at)
);

CREATE TABLE auth_rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(32) NOT NULL,
    identifier_hash CHAR(64) NOT NULL,
    ip_hash CHAR(64) NOT NULL,
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    window_started_at DATETIME NOT NULL,
    blocked_until DATETIME NULL,
    UNIQUE KEY uq_auth_rate_limit (action, identifier_hash, ip_hash),
    INDEX idx_auth_rate_limits_blocked (blocked_until)
);

CREATE TABLE student_account_activations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE,
    INDEX idx_student_activation_usuario (usuario_id),
    INDEX idx_student_activation_expires (expires_at)
);

CREATE TABLE funcionario (
    usuario_id INT PRIMARY KEY,
    cargo_id INT NOT NULL,
    registro_profissional VARCHAR(50),
    observacoes TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (cargo_id) REFERENCES cargo(id)
);

CREATE TABLE aluno (
    usuario_id INT PRIMARY KEY,
    data_matricula DATE NOT NULL,
    cadastrado_por INT,
    codigo_matricula VARCHAR(20) UNIQUE NOT NULL, -- AAAAMM000001
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE,
    FOREIGN KEY (cadastrado_por) REFERENCES funcionario(usuario_id)
);

CREATE TABLE sequencia_matricula (
    id INT AUTO_INCREMENT PRIMARY KEY
);

CREATE TABLE contato (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    valor VARCHAR(100) NOT NULL,
    UNIQUE (usuario_id, tipo),
    INDEX idx_contato_usuario (usuario_id),
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
);

CREATE TABLE modalidade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE treino (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    modalidade_id INT NOT NULL,
    descricao TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (modalidade_id) REFERENCES modalidade(id),
    UNIQUE (nome, modalidade_id)
);

CREATE TABLE espaco_treino (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    capacidade_minima INT NOT NULL,
    capacidade_maxima INT NOT NULL,
    equipamentos TEXT,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE turma (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    instrutor_id INT NULL,
    capacidade_minima INT NOT NULL,
    capacidade_maxima INT NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instrutor_id) REFERENCES funcionario(usuario_id)
);

CREATE TABLE aluno_turma (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    turma_id INT NOT NULL,
    data_inscricao DATE NOT NULL DEFAULT CURDATE(),
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (aluno_id, turma_id),
    INDEX idx_aluno (aluno_id),
    INDEX idx_turma (turma_id),
    FOREIGN KEY (aluno_id) REFERENCES aluno(usuario_id) ON DELETE CASCADE,
    FOREIGN KEY (turma_id) REFERENCES turma(id) ON DELETE CASCADE
);

CREATE TABLE anamnese_formulario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    versao INT DEFAULT 1,
    ativo BOOLEAN DEFAULT TRUE,
    criado_por INT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ativo (ativo),
    FOREIGN KEY (criado_por) REFERENCES usuario(id) ON DELETE SET NULL
);

CREATE TABLE anamnese_pergunta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    formulario_id INT,
    slug VARCHAR(50) NOT NULL,
    pergunta TEXT NOT NULL,
    categoria VARCHAR(50),
    tipo_input ENUM(
        'text',
        'textarea',
        'number',
        'date',
        'boolean',
        'select',
        'radio',
        'checkbox'
    ) NOT NULL,
    obrigatoria BOOLEAN DEFAULT FALSE,
    ordem INT DEFAULT 0,
    versao INT DEFAULT 1,
    ativo BOOLEAN DEFAULT TRUE,
    config JSON NULL,
    regra_exibicao JSON NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pergunta_formulario (formulario_id, ordem),
    FOREIGN KEY (formulario_id) REFERENCES anamnese_formulario(id) ON DELETE CASCADE,
    UNIQUE (slug, versao)
);

CREATE TABLE anamnese_opcao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pergunta_id INT NOT NULL,
    label VARCHAR(100) NOT NULL,
    valor VARCHAR(50) NOT NULL,
    ordem INT DEFAULT 0,
    config JSON NULL, -- ex: possui_observacao, cor, etc
    FOREIGN KEY (pergunta_id) REFERENCES anamnese_pergunta(id) ON DELETE CASCADE,
    UNIQUE (pergunta_id, valor)
);

CREATE TABLE anamnese_resposta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    pergunta_id INT NOT NULL,
    valor JSON NOT NULL,
    observacao TEXT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aluno_id) REFERENCES aluno(usuario_id) ON DELETE CASCADE,
    FOREIGN KEY (pergunta_id) REFERENCES anamnese_pergunta(id),
    UNIQUE (aluno_id, pergunta_id)
);

CREATE TABLE avaliacao_fisica (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    avaliador_id INT NOT NULL,
    data_avaliacao DATE NOT NULL,
    peso DECIMAL(5,2),
    altura DECIMAL(3,2),
    imc DECIMAL(5,2),
    cintura DECIMAL(5,2),
    torax DECIMAL(5,2),
    braco_dc DECIMAL(5,2),
    braco_d DECIMAL(5,2),
    braco_ec DECIMAL(5,2),
    braco_e DECIMAL(5,2),
    coxa_d DECIMAL(5,2),
    coxa_e DECIMAL(5,2),
    panturrilha_d DECIMAL(5,2),
    panturrilha_e DECIMAL(5,2),
    percentual_gordura DECIMAL(4,2),
    percentual_musculo DECIMAL(4,2),
    metabolismo_repouso INT,
    idade_biologica INT,
    gordura_visceral DECIMAL(5,2),
    observacoes TEXT,
    FOREIGN KEY (aluno_id) REFERENCES aluno(usuario_id) ON DELETE CASCADE,
    FOREIGN KEY (avaliador_id) REFERENCES funcionario(usuario_id)
);

CREATE TABLE turma_config_horario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    turma_id INT,
    dia_semana ENUM('segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'),
    hora_inicio TIME,
    hora_fim TIME,
    UNIQUE (turma_id, dia_semana),
    FOREIGN KEY (turma_id) REFERENCES turma(id)
);

CREATE TABLE treino_agenda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    treino_id INT NOT NULL,
    turma_id INT,
    espaco_id INT NOT NULL,
    instrutor_id INT,
    data_hora_inicio DATETIME NOT NULL,
    data_hora_fim DATETIME NOT NULL,
    status ENUM('agendado', 'concluido', 'cancelado') DEFAULT 'agendado',
    observacoes TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_treino_agenda_treino (treino_id),
    INDEX idx_treino_agenda_turma (turma_id),
    INDEX idx_treino_agenda_espaco (espaco_id),
    INDEX idx_treino_agenda_instrutor (instrutor_id),
    INDEX idx_treino_agenda_status (status),
    FOREIGN KEY (treino_id) REFERENCES treino(id),
    FOREIGN KEY (turma_id) REFERENCES turma(id) ON DELETE SET NULL,
    FOREIGN KEY (espaco_id) REFERENCES espaco_treino(id),
    FOREIGN KEY (instrutor_id) REFERENCES funcionario(usuario_id) ON DELETE SET NULL
);

CREATE TABLE presenca_treino (
    treino_id INT NOT NULL,
    aluno_id INT NOT NULL,
    situacao ENUM('presente', 'ausente', 'justificado') DEFAULT 'presente',
    checkin_time DATETIME,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_presenca_aluno (aluno_id),
    INDEX idx_presenca_treino (treino_id),
    PRIMARY KEY (treino_id, aluno_id),
    FOREIGN KEY (treino_id) REFERENCES treino_agenda(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id) REFERENCES aluno(usuario_id) ON DELETE CASCADE
);

CREATE TABLE servico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL UNIQUE,
    descricao TEXT NULL,
    tipo ENUM('mensalidade', 'avaliacao', 'personal', 'taxa', 'outro') NOT NULL DEFAULT 'mensalidade',
    valor_base DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    recorrente BOOLEAN NOT NULL DEFAULT TRUE,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_servico_ativo (ativo)
);

CREATE TABLE plano (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL UNIQUE,
    descricao TEXT NULL,
    periodicidade ENUM('mensal', 'trimestral', 'semestral', 'anual', 'avulso') NOT NULL DEFAULT 'mensal',
    valor DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_plano_ativo (ativo)
);

CREATE TABLE plano_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plano_id INT NOT NULL,
    servico_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    valor_unitario DECIMAL(10,2) NOT NULL,
    UNIQUE (plano_id, servico_id),
    FOREIGN KEY (plano_id) REFERENCES plano(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servico(id)
);

CREATE TABLE contrato (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    plano_id INT NULL,
    periodicidade ENUM('mensal', 'trimestral', 'semestral', 'anual', 'avulso') NOT NULL DEFAULT 'avulso',
    geracao_automatica BOOLEAN NOT NULL DEFAULT TRUE,
    data_inicio DATE NOT NULL,
    data_fim DATE NULL,
    dia_vencimento TINYINT UNSIGNED NOT NULL,
    valor_contratado DECIMAL(10,2) NOT NULL,
    desconto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    multa_percentual DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    juros_percentual DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    status ENUM('ativo', 'pausado', 'encerrado', 'cancelado') NOT NULL DEFAULT 'ativo',
    observacoes TEXT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (aluno_id) REFERENCES aluno(usuario_id),
    FOREIGN KEY (plano_id) REFERENCES plano(id) ON DELETE SET NULL,
    INDEX idx_contrato_aluno (aluno_id),
    INDEX idx_contrato_status (status)
);

CREATE TABLE contrato_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrato_id INT NOT NULL,
    servico_id INT NULL,
    descricao VARCHAR(160) NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    valor_unitario DECIMAL(10,2) NOT NULL,
    valor_desconto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (contrato_id) REFERENCES contrato(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servico(id) ON DELETE SET NULL
);

CREATE TABLE cobranca (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrato_id INT NOT NULL,
    aluno_id INT NOT NULL,
    competencia CHAR(7) NULL,
    descricao VARCHAR(180) NOT NULL,
    valor_original DECIMAL(10,2) NOT NULL,
    desconto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    multa DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    juros DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    valor_final DECIMAL(10,2) NOT NULL,
    data_vencimento DATE NOT NULL,
    status ENUM('aberta', 'paga', 'vencida', 'cancelada') NOT NULL DEFAULT 'aberta',
    cancelada_em DATETIME NULL,
    cancelada_por INT NULL,
    motivo_cancelamento VARCHAR(500) NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contrato_id) REFERENCES contrato(id),
    FOREIGN KEY (aluno_id) REFERENCES aluno(usuario_id),
    UNIQUE (contrato_id, competencia),
    INDEX idx_cobranca_aluno (aluno_id),
    INDEX idx_cobranca_status_vencimento (status, data_vencimento)
);

CREATE TABLE pagamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cobranca_id INT NOT NULL,
    valor_pago DECIMAL(10,2) NOT NULL,
    data_pagamento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    forma_pagamento ENUM('pix', 'dinheiro', 'cartao', 'boleto', 'transferencia', 'outro') NOT NULL,
    transacao_id VARCHAR(120) NULL,
    transacao_unica VARCHAR(120) COLLATE utf8mb4_bin GENERATED ALWAYS AS (NULLIF(TRIM(transacao_id), '')) STORED,
    chave_idempotencia VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL,
    requisicao_hash CHAR(64) NULL,
    estornado_em DATETIME NULL,
    estornado_por INT NULL,
    motivo_estorno VARCHAR(500) NULL,
    observacoes TEXT NULL,
    registrado_por INT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cobranca_id) REFERENCES cobranca(id),
    FOREIGN KEY (registrado_por) REFERENCES usuario(id) ON DELETE SET NULL,
    INDEX idx_pagamento_cobranca (cobranca_id),
    INDEX idx_pagamento_data (data_pagamento),
    UNIQUE KEY uq_pagamento_operacao (chave_idempotencia),
    UNIQUE KEY uq_pagamento_transacao (transacao_unica)
);

-- 7. SEGURANÇA E LOGS
CREATE TABLE permissao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) UNIQUE NOT NULL, -- ex: 'relatorios.financeiro'
    descricao VARCHAR(255)
);

INSERT INTO permissao (slug, descricao) VALUES
('financeiro.visualizar', 'Visualizar dados financeiros'),
('financeiro.gerenciar', 'Gerenciar servicos, contratos e cobrancas'),
('financeiro.registrar_pagamento', 'Registrar pagamentos financeiros');

CREATE TABLE cargo_permissao (
    cargo_id INT NOT NULL,
    permissao_id INT NOT NULL,
    PRIMARY KEY (cargo_id, permissao_id),
    FOREIGN KEY (cargo_id) REFERENCES cargo(id) ON DELETE CASCADE,
    FOREIGN KEY (permissao_id) REFERENCES permissao(id) ON DELETE CASCADE
);


-- POPULANDO DADOS MESTRES --

INSERT INTO cargo (nome, descricao, salario_base, ativo) VALUES
('Administrador', 'Responsável pela gestão completa do sistema', 0.00, TRUE),
('Instrutor', 'Responsável por ministrar aulas e treinos', 0.00, TRUE),
('Estagiário', 'Auxiliar em atividades diversas', 0.00, TRUE),
('Gerente', 'Gerencia operações e equipes', 0.00, TRUE),
('Atendente', 'Atendimento ao cliente e secretaria', 0.00, TRUE);

-- INSERINDO O PRIMEIRO ADMIN (Exemplo)
INSERT INTO endereco (logradouro, numero, cidade, bairro, cep)
VALUES ('Av. Central', '100', 'São Luís', 'Centro', '01010000');

INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, tipo_usuario, endereco_id)
VALUES ('Admin', 'Master', '00000000000', 'admin@centrotreinamento.com', '$argon2id$v=19$m=65536,t=4,p=1$enBzQTh6a3NuRTAwWVFFNg$D1fBTREiUz8MPOsv4hl6WI7EgKRbK4+9nl7wf6+U1Sw', '1990-01-01', 'admin', 1);

INSERT INTO funcionario (usuario_id, cargo_id, registro_profissional)
VALUES (1, 1, 'ADM-01');

-- FORMULARIO INICIAL DE ANAMNESE

INSERT INTO anamnese_formulario (id, nome, descricao, versao, ativo, criado_por)
VALUES (1, 'Anamnese Padrão', 'Formulário padrão de avaliação de saúde para novos alunos', 1, TRUE, 1);

INSERT INTO anamnese_pergunta
(formulario_id, slug, pergunta, tipo_input, obrigatoria, ordem, config, regra_exibicao)
VALUES
(1, 'problema_cardiaco',
 'Algum médico já lhe diagnosticou com problema cardíaco?',
 'radio', 0, 1, NULL, NULL),

(1, 'problema_cardiaco_obs',
 'Qual problema cardíaco?',
 'text', 0, 2,
 JSON_OBJECT('placeholder', 'Especifique o problema cardíaco...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','problema_cardiaco','operator','equals','valor','sim'))),

(1, 'dor_peito',
 'Você tem dores no peito com frequência?',
 'radio', 0, 3, NULL, NULL),

(1, 'desmaio_tontura',
 'Você desmaia com frequência ou tem episódios de tontura/vertigem?',
 'radio', 0, 4, NULL, NULL),

(1, 'pressao_arterial',
 'Algum médico já lhe diagnosticou com pressão arterial muito alta?',
 'radio', 0, 5, NULL, NULL),

(1, 'problema_osseo',
 'Algum médico já lhe diagnosticou com problemas ósseos ou articulares?',
 'radio', 0, 6, NULL, NULL),

(1, 'problema_osseo_obs',
 'Quais problemas ósseos ou articulares?',
 'text', 0, 7,
 JSON_OBJECT('placeholder', 'Ex: artrose no joelho, tendinite no ombro, osteoporose...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','problema_osseo','operator','equals','valor','sim'))),

(1, 'outro_problema',
 'Algum outro motivo que possa impedir a prática de exercícios?',
 'radio', 0, 8, NULL, NULL),

(1, 'outro_problema_obs',
 'Qual outro motivo?',
 'text', 0, 9,
 JSON_OBJECT('placeholder', 'Informe o motivo...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','outro_problema','operator','equals','valor','sim'))),

(1, 'medicamentos',
 'Está tomando alguma medicação atualmente?',
 'radio', 0, 10, NULL, NULL),

(1, 'medicamentos_obs',
 'Informe quais medicamentos está tomando?',
 'textarea', 0, 11,
 JSON_OBJECT('placeholder', 'Informe quais medicamentos...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','medicamentos','operator','equals','valor','sim'))),

(1, 'cirurgia',
 'Você já fez alguma cirurgia?',
 'radio', 0, 12, NULL, NULL),

(1, 'cirurgia_nome',
 'Qual cirurgia?',
 'text', 0, 13,
 JSON_OBJECT('placeholder', 'Informe qual a cirurgia...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','cirurgia','operator','equals','valor','sim'))),

(1, 'cirurgia_data',
 'Quando ocorreu?',
 'date', 0, 14, NULL,
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','cirurgia','operator','equals','valor','sim'))),

(1, 'gravida',
 'Você está grávida?',
 'radio', 0, 15, NULL, NULL),

(1, 'gravida_tempo',
 'Há quanto tempo?',
 'text', 0, 16,
 JSON_OBJECT('placeholder', 'Informe o tempo de gestação...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','gravida','operator','equals','valor','sim'))),

(1, 'fumante',
 'Você fuma ou costuma fumar?',
 'radio', 0, 17, NULL, NULL),

(1, 'consumo_alcool',
 'Você consome bebidas alcoólicas?',
 'radio', 0, 18, NULL, NULL),

(1, 'historico_familiar',
 'Algum parente próximo (Pai, Mãe, Irmão ou Irmã) seu teve ataque cardíaco antes dos 50 anos?',
 'radio', 0, 19, NULL, NULL),

(1, 'atividade_fisica',
 'Você realiza atividades físicas regularmente?',
 'radio', 0, 20, NULL, NULL),

(1, 'tipo_atividade',
 'Qual tipo de atividade?',
 'text', 0, 21,
 JSON_OBJECT('placeholder', 'Ex: Caminhada, natação, futebol...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','atividade_fisica','operator','equals','valor','sim'))),

(1, 'sintomas',
 'Você tem algum dos sintomas abaixo?',
 'checkbox', 0, 22, NULL, NULL),

(1, 'doenca_pulmonar_obs',
 'Qual doença pulmonar?',
 'text', 0, 23,
 JSON_OBJECT('placeholder', 'Informe a doença pulmonar...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','sintomas','operator','contains','valor','doenca_pulmonar'))),

(1, 'objetivos',
 'Quais seus objetivos ingressando em um grupo de promoção de sua saúde? (Pode selecionar mais de um)',
 'checkbox', 0, 24, NULL, NULL),

(1, 'objetivos_outros',
 'Quais outros objetivos?',
 'text', 0, 25,
 JSON_OBJECT('placeholder', 'Informe os outros objetivos...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','objetivos','operator','contains','valor','outros'))),

(1, 'observacoes_medicas',
 'Observações Médicas (Opcional)',
 'textarea', 0, 26,
 JSON_OBJECT('placeholder', 'Outras observações médicas relevantes...'),
 NULL);

-- Opcoes Sim/Nao para todas as perguntas do tipo radio.
INSERT INTO anamnese_opcao (pergunta_id, label, valor, ordem)
SELECT id, 'Sim', 'sim', 1
FROM anamnese_pergunta
WHERE tipo_input = 'radio' AND formulario_id = 1;

INSERT INTO anamnese_opcao (pergunta_id, label, valor, ordem)
SELECT id, 'Não', 'nao', 2
FROM anamnese_pergunta
WHERE tipo_input = 'radio' AND formulario_id = 1;

-- Opcoes da pergunta de sintomas.
INSERT INTO anamnese_opcao (pergunta_id, label, valor, ordem)
SELECT id, 'Dor nas costas', 'dor_costas', 1
FROM anamnese_pergunta WHERE slug = 'sintomas' AND formulario_id = 1;

INSERT INTO anamnese_opcao (pergunta_id, label, valor, ordem)
SELECT id, 'Dor nas articulações, tendões ou músculo', 'dor_articular', 2
FROM anamnese_pergunta WHERE slug = 'sintomas' AND formulario_id = 1;

INSERT INTO anamnese_opcao (pergunta_id, label, valor, ordem)
SELECT id, 'Doença pulmonar', 'doenca_pulmonar', 3
FROM anamnese_pergunta WHERE slug = 'sintomas' AND formulario_id = 1;

INSERT INTO anamnese_opcao (pergunta_id, label, valor, ordem)
SELECT id, 'Nenhum dos sintomas', 'nenhum', 4
FROM anamnese_pergunta WHERE slug = 'sintomas' AND formulario_id = 1;

-- Opcoes da pergunta de objetivos.
INSERT INTO anamnese_opcao (pergunta_id, label, valor, ordem)
SELECT id, 'Perder peso', 'perder_peso', 1
FROM anamnese_pergunta WHERE slug = 'objetivos' AND formulario_id = 1;

INSERT INTO anamnese_opcao (pergunta_id, label, valor, ordem)
SELECT id, 'Ganhar massa muscular', 'ganhar_massa', 2
FROM anamnese_pergunta WHERE slug = 'objetivos' AND formulario_id = 1;

INSERT INTO anamnese_opcao (pergunta_id, label, valor, ordem)
SELECT id, 'Melhorar condicionamento', 'condicionamento', 3
FROM anamnese_pergunta WHERE slug = 'objetivos' AND formulario_id = 1;

INSERT INTO anamnese_opcao (pergunta_id, label, valor, ordem)
SELECT id, 'Melhorar preparo cardiovascular', 'cardiovascular', 4
FROM anamnese_pergunta WHERE slug = 'objetivos' AND formulario_id = 1;

INSERT INTO anamnese_opcao (pergunta_id, label, valor, ordem)
SELECT id, 'Definição muscular/condicionamento', 'definicao', 5
FROM anamnese_pergunta WHERE slug = 'objetivos' AND formulario_id = 1;

INSERT INTO anamnese_opcao (pergunta_id, label, valor, ordem)
SELECT id, 'Fins de reabilitação', 'reabilitacao', 6
FROM anamnese_pergunta WHERE slug = 'objetivos' AND formulario_id = 1;

INSERT INTO anamnese_opcao (pergunta_id, label, valor, ordem)
SELECT id, 'Redução de estresse', 'reducao_estresse', 7
FROM anamnese_pergunta WHERE slug = 'objetivos' AND formulario_id = 1;

INSERT INTO anamnese_opcao (pergunta_id, label, valor, ordem)
SELECT id, 'Melhora na qualidade de vida', 'qualidade_vida', 8
FROM anamnese_pergunta WHERE slug = 'objetivos' AND formulario_id = 1;

INSERT INTO anamnese_opcao (pergunta_id, label, valor, ordem)
SELECT id, 'Outros', 'outros', 9
FROM anamnese_pergunta WHERE slug = 'objetivos' AND formulario_id = 1;

-- BEGIN AUDIT OPERATIONS
-- Estrutura e triggers da auditoria; manter sincronizados com docs/sql/auditoria_operacoes.sql.
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
