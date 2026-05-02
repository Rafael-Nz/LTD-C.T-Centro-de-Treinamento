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

-- 5. SAÚDE E AVALIAÇÃO (Normalizado)
CREATE TABLE anamnese_pergunta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pergunta TEXT NOT NULL,
    tipo_resposta ENUM('boolean', 'text') DEFAULT 'boolean'
);

CREATE TABLE anamnese_resposta (
    aluno_id INT NOT NULL,
    pergunta_id INT NOT NULL,
    resposta_boolean BOOLEAN,
    resposta_texto TEXT,
    PRIMARY KEY (aluno_id, pergunta_id),
    FOREIGN KEY (aluno_id) REFERENCES aluno(usuario_id) ON DELETE CASCADE,
    FOREIGN KEY (pergunta_id) REFERENCES anamnese_pergunta(id)
);

CREATE TABLE avaliacao_fisica (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    avaliador_id INT NOT NULL,
    data_avaliacao DATE NOT NULL,
    peso DECIMAL(5,2),
    altura DECIMAL(3,2),
    -- O IMC não precisa estar aqui, pode ser calculado via VIEW ou App
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


-- INSERINDO O PRIMEIRO ADMIN (Exemplo de Fluxo)
INSERT INTO endereco (logradouro, numero, cidade, bairro, cep) 
VALUES ('Av. Central', '100', 'São Paulo', 'Centro', '01010000');

INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, tipo_usuario, endereco_id)
VALUES ('Admin', 'Master', '00000000000', 'admin@gym.com', 'hash_aqui', '1990-01-01', 'admin', 1);

INSERT INTO funcionario (usuario_id, cargo_id, registro_profissional)
VALUES (1, 1, 'ADM-01');