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
    percentual_gordura DECIMAL(4,2),
    percentual_musculo DECIMAL(4,2),
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
VALUES ('Admin', 'Master', '00000000000', 'admin@centrotreinamento.com', '$argon2id$v=19$m=65536,t=4,p=1$enBzQTh6a3NuRTAwWVFFNg$D1fBTREiUz8MPOsv4hl6WI7EgKRbK4+9nl7wf6+U1Sw', '1990-01-01', 'admin', 1);

INSERT INTO funcionario (usuario_id, cargo_id, registro_profissional)
VALUES (1, 1, 'ADM-01');


