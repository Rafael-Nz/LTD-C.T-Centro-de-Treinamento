DROP DATABASE IF EXISTS db_centro_treinamento;
CREATE DATABASE db_centro_treinamento CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_centro_treinamento;

-- 1. INFRAESTRUTURA BÁSICA
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

-- 2. CORE DE USUÁRIOS (Herança/Especialização)
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

-- 3. COMUNICAÇÃO (Unificada para qualquer usuário)
CREATE TABLE contato (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    valor VARCHAR(100) NOT NULL,
    UNIQUE (usuario_id, tipo),
    INDEX idx_contato_usuario (usuario_id),
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
);

-- 4. GESTÃO DE TREINOS E ESPAÇOS
CREATE TABLE modalidade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
    modalidade_id INT NOT NULL,
    instrutor_id INT NOT NULL,
    turno ENUM('manha', 'tarde', 'noite') NOT NULL,
    capacidade_minima INT NOT NULL,
    capacidade_maxima INT NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (modalidade_id) REFERENCES modalidade(id),
    FOREIGN KEY (instrutor_id) REFERENCES funcionario(usuario_id)
);

-- 5. INFORMÇÕES DE SAÚDE E AVALIAÇÃO (Normalizado)
CREATE TABLE anamnese_pergunta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) UNIQUE NOT NULL,
    pergunta TEXT NOT NULL,
    categoria VARCHAR(50),
    tipo_resposta ENUM(
        'boolean',
        'text',
        'boolean_with_text',
        'multi_select'
    ) DEFAULT 'boolean',
    obrigatoria BOOLEAN DEFAULT FALSE,
    ordem INT DEFAULT 0,
    versao INT DEFAULT 1,
    ativo BOOLEAN DEFAULT TRUE
    UNIQUE (slug, versao)
);

CREATE TABLE anamnese_opcao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pergunta_id INT NOT NULL,
    label VARCHAR(100) NOT NULL,
    valor VARCHAR(50) NOT NULL,
    possui_observacao BOOLEAN DEFAULT FALSE,
    ordem INT DEFAULT 0,
    FOREIGN KEY (pergunta_id) REFERENCES anamnese_pergunta(id) ON DELETE CASCADE
    UNIQUE (pergunta_id, valor)
);

CREATE TABLE anamnese_resposta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    pergunta_id INT NOT NULL,
    resposta_boolean BOOLEAN,
    resposta_texto TEXT,
    resposta_data DATE,
    opcao_id INT,
    observacao TEXT,
    FOREIGN KEY (aluno_id) REFERENCES aluno(usuario_id) ON DELETE CASCADE,
    FOREIGN KEY (pergunta_id) REFERENCES anamnese_pergunta(id),
    FOREIGN KEY (opcao_id) REFERENCES anamnese_opcao(id)
    UNIQUE (aluno_id, pergunta_id, opcao_id)
);

CREATE TABLE avaliacao_fisica (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    avaliador_id INT NOT NULL,
    data_avaliacao DATE NOT NULL,
    peso DECIMAL(5,2),
    altura DECIMAL(3,2),
    -- O IMC não precisa estar aqui, ele pode ser calculado via VIEW ou App...
    percentual_gordura DECIMAL(4,2),
    percentual_musculo DECIMAL(4,2),
    observacoes TEXT,
    FOREIGN KEY (aluno_id) REFERENCES aluno(usuario_id) ON DELETE CASCADE,
    FOREIGN KEY (avaliador_id) REFERENCES funcionario(usuario_id)
);

-- 6. AGENDAMENTOS E FREQUÊNCIA
CREATE TABLE treino_agenda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    turma_id INT NOT NULL,
    espaco_id INT NOT NULL,
    data_hora_inicio DATETIME NOT NULL,
    data_hora_fim DATETIME NOT NULL,
    status ENUM('agendado', 'concluido', 'cancelado') DEFAULT 'agendado',
    INDEX idx_treino_turma (turma_id),
    INDEX idx_treino_espaco (espaco_id),
    FOREIGN KEY (turma_id) REFERENCES turma(id),
    FOREIGN KEY (espaco_id) REFERENCES espaco_treino(id)
);

CREATE TABLE presenca_treino (
    treino_id INT NOT NULL,
    aluno_id INT NOT NULL,
    situacao ENUM('presente', 'ausente', 'justificado') DEFAULT 'presente',
    checkin_time DATETIME,
    INDEX idx_presenca_aluno (aluno_id),
    INDEX idx_presenca_treino (treino_id),
    PRIMARY KEY (treino_id, aluno_id),
    FOREIGN KEY (treino_id) REFERENCES treino_agenda(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id) REFERENCES aluno(usuario_id) ON DELETE CASCADE
);

-- 7. SEGURANÇA E LOGS
CREATE TABLE permissao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) UNIQUE NOT NULL, -- ex: 'relatorios.financeiro'
    descricao VARCHAR(255)
);

CREATE TABLE cargo_permissao (
    cargo_id INT NOT NULL,
    permissao_id INT NOT NULL,
    PRIMARY KEY (cargo_id, permissao_id),
    FOREIGN KEY (cargo_id) REFERENCES cargo(id) ON DELETE CASCADE,
    FOREIGN KEY (permissao_id) REFERENCES permissao(id) ON DELETE CASCADE
);

CREATE TABLE sistema_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    acao TEXT NOT NULL,
    ip_origem VARCHAR(45),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE SET NULL
);

-- POPULANDO DADOS MESTRES --

INSERT INTO cargo (nome, descricao, salario_base, ativo) VALUES
('Administrador', 'Responsável pela gestão completa do sistema', 0.00, TRUE),
('Instrutor', 'Responsável por ministrar aulas e treinos', 0.00, TRUE),
('Estagiário', 'Auxiliar em atividades diversas', 0.00, TRUE),
('Gerente', 'Gerencia operações e equipes', 0.00, TRUE),
('Atendente', 'Atendimento ao cliente e secretaria', 0.00, TRUE);

INSERT INTO anamnese_pergunta (slug, pergunta, categoria, tipo_resposta, ordem) VALUES
('problema_cardiaco', 'Algum médico já lhe diagnosticou com problema cardíaco?', 'cardio', 'boolean_with_text', 1),
('dor_peito', 'Você tem dores no peito com frequência?', 'cardio', 'boolean', 2),
('desmaio_tontura', 'Você desmaia ou tem tontura/vertigem?', 'cardio', 'boolean', 3),
('pressao_alta', 'Diagnóstico de pressão arterial alta?', 'cardio', 'boolean', 4),
('problema_osseo', 'Problemas ósseos ou articulares?', 'ortopedico', 'boolean_with_text', 5),
('outro_problema', 'Outro motivo que impeça exercícios?', 'geral', 'boolean_with_text', 6),
('medicamentos', 'Está tomando medicação?', 'historico', 'boolean_with_text', 7),
('cirurgia', 'Já fez alguma cirurgia?', 'historico', 'boolean_with_text', 8),
('gravida', 'Está grávida?', 'condicoes', 'boolean_with_text', 9),
('fumante', 'Você fuma?', 'habitos', 'boolean', 10),
('alcool', 'Consome álcool?', 'habitos', 'boolean', 11),
('historico_familiar', 'Parente teve ataque cardíaco antes dos 50?', 'familia', 'boolean', 12),
('atividade_fisica', 'Realiza atividade física?', 'habitos', 'boolean_with_text', 13),
('sintomas', 'Sintomas apresentados', 'saude', 'multi_select', 14),
('objetivos', 'Objetivos do aluno', 'objetivos', 'multi_select', 15),
('observacoes_medicas', 'Observações médicas', 'geral', 'text', 16);

INSERT INTO anamnese_opcao (pergunta_id, label, valor, possui_observacao, ordem) VALUES
(14, 'Dor nas costas', 'dor_costas', FALSE, 1),
(14, 'Dor articular/muscular', 'dor_articular', FALSE, 2),
(14, 'Doença pulmonar (asma, enfisema, outra...)', 'doenca_pulmonar', TRUE, 3),
(14, 'Nenhum', 'nenhum', FALSE, 4);

INSERT INTO anamnese_opcao (pergunta_id, label, valor, possui_observacao, ordem) VALUES
(15, 'Perder peso', 'perder_peso', FALSE, 1),
(15, 'Ganhar massa muscular', 'ganhar_massa', FALSE, 2),
(15, 'Condicionamento', 'condicionamento', FALSE, 3),
(15, 'Cardiovascular', 'cardiovascular', FALSE, 4),
(15, 'Definição muscular', 'definicao', FALSE, 5),
(15, 'Reabilitação', 'reabilitacao', FALSE, 6),
(15, 'Redução de estresse', 'reducao_estresse', FALSE, 7),
(15, 'Qualidade de vida', 'qualidade_vida', FALSE, 8),
(15, 'Outros', 'outros', TRUE, 9);

-- INSERINDO O PRIMEIRO ADMIN (Exemplo de Fluxo)
INSERT INTO endereco (logradouro, numero, cidade, bairro, cep) 
VALUES ('Av. Central', '100', 'São Paulo', 'Centro', '01010000');

INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, tipo_usuario, endereco_id)
VALUES ('Admin', 'Master', '00000000000', 'admin@gym.com', 'hash_aqui', '1990-01-01', 'admin', 1);

INSERT INTO funcionario (usuario_id, cargo_id, registro_profissional)
VALUES (1, 1, 'ADM-01');


