DROP DATABASE IF EXISTS centro_treinamento;
CREATE DATABASE centro_treinamento CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE centro_treinamento;

CREATE TABLE endereco (
    id INT AUTO_INCREMENT PRIMARY KEY,
    logradouro VARCHAR(200),
    numero VARCHAR(10),
    cidade VARCHAR(100),
    bairro VARCHAR(100),
    cep VARCHAR(9),
    complemento VARCHAR(100)
);

CREATE TABLE modalidade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE espaco_treino (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    capacidade_maxima INT NOT NULL,
    capacidade_minima INT NOT NULL,
    equipamentos TEXT,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE cargo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao VARCHAR(255),
    salario_base DECIMAL(10,2),
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO cargo (nome, descricao, salario_base, ativo) VALUES
('Administrador', 'Responsável pela gestão completa do sistema', 0.00, TRUE),
('Instrutor', 'Responsável por ministrar aulas e treinos', 0.00, TRUE),
('Estagiário', 'Auxiliar em atividades diversas', 0.00, TRUE),
('Gerente', 'Gerencia operações e equipes', 0.00, TRUE),
('Atendente', 'Atendimento ao cliente e secretaria', 0.00, TRUE);

CREATE TABLE funcionario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    sobrenome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    data_nascimento DATE NOT NULL,
    genero ENUM('M', 'F', 'O'),
    email VARCHAR(150),
    senha VARCHAR(300),
    cargo_id INT NOT NULL,
    registro_profissional VARCHAR(50),
    observacoes TEXT,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    endereco_id INT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (cargo_id) REFERENCES cargo(id),
    FOREIGN KEY (endereco_id) REFERENCES endereco(id)
);

CREATE TABLE funcionario_contato (
    id INT AUTO_INCREMENT PRIMARY KEY,
    funcionario_id INT NOT NULL,
    tipo_contato ENUM('telefone', 'email') NOT NULL,
    valor VARCHAR(100) NOT NULL,
    observacao VARCHAR(100),
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (funcionario_id) REFERENCES funcionario(id) ON DELETE CASCADE,
    UNIQUE (funcionario_id, tipo_contato, valor)
);

CREATE TABLE aluno (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    sobrenome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE,
    genero ENUM('M', 'F', 'O'),
    email VARCHAR(150),
    senha VARCHAR(300),
    data_nascimento DATE NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    endereco_id INT NOT NULL,
    data_matricula DATE NOT NULL,
    cadastrado_por INT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (endereco_id) REFERENCES endereco(id),
    FOREIGN KEY (cadastrado_por) REFERENCES funcionario(id)
);

CREATE TABLE turma (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,

    turno ENUM('manha', 'tarde', 'noite') NOT NULL,

    capacidade_minima INT NOT NULL,
    capacidade_maxima INT NOT NULL,

    instrutor_id INT NOT NULL,
    espaco_treino_id INT NOT NULL,

    ativo BOOLEAN DEFAULT TRUE,

    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (instrutor_id) REFERENCES funcionario(id),
    FOREIGN KEY (espaco_treino_id) REFERENCES espaco_treino(id)
);

CREATE TABLE aluno_contato (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    tipo_contato ENUM('telefone', 'email') NOT NULL,
    valor VARCHAR(100) NOT NULL,
    observacao VARCHAR(100),
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (aluno_id) REFERENCES aluno(id) ON DELETE CASCADE,
    UNIQUE (aluno_id, tipo_contato, valor)
);

CREATE TABLE aluno_questionario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL UNIQUE,

    problema_cardiaco BOOLEAN,
    problema_cardiaco_descricao TEXT,
    dor_peito BOOLEAN,
    desmaia_frequencia BOOLEAN,
    pressao_alta BOOLEAN,

    dor_costa BOOLEAN,
    dor_musculo BOOLEAN,
    doenca_pulmonar BOOLEAN,
    doenca_pulmonar_descricao TEXT,
    nenhum_sintoma BOOLEAN,

    osseo_articular BOOLEAN,
    osseo_articular_descricao TEXT,

    limitacao_fisica BOOLEAN,
    limitacao_descricao TEXT,

    medicamento_continuo BOOLEAN,
    medicamento_descricao TEXT,

    cirurgia_anterior BOOLEAN,
    cirurgia_descricao TEXT,
    cirurgia_data VARCHAR(50),

    gravida BOOLEAN,
    gravida_tempo VARCHAR(50),

    pratica_exercicios BOOLEAN,
    tipo_exercicios TEXT,
    fumante BOOLEAN,
    consumo_alcool BOOLEAN,

    problema_saude_familia BOOLEAN,
    problema_saude_familia_descricao TEXT,

    outros_objetivos TEXT,
    observacoes_medicas TEXT,

    data_preenchimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (aluno_id) REFERENCES aluno(id) ON DELETE CASCADE
);

CREATE TABLE objetivo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE aluno_objetivo (
    aluno_id INT NOT NULL,
    objetivo_id INT NOT NULL,

    PRIMARY KEY (aluno_id, objetivo_id),

    FOREIGN KEY (aluno_id) REFERENCES aluno(id) ON DELETE CASCADE,
    FOREIGN KEY (objetivo_id) REFERENCES objetivo(id) ON DELETE CASCADE
);

INSERT INTO objetivo (nome) VALUES
('Perder peso'),
('Ganhar massa muscular'),
('Melhorar condicionamento'),
('Melhorar preparo cardiovascular'),
('Definição muscular/condicionamento'),
('Fins de reabilitação'),
('Redução de estresse'),
('Melhora na qualidade de vida');

CREATE TABLE avaliacao_fisica (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    data_avaliacao DATE NOT NULL,
    avaliado_por INT NOT NULL,

    peso DECIMAL(5,2),
    cintura DECIMAL(5,2),
    braco_direito_contraido DECIMAL(5,2),
    braco_esquerdo_contraido DECIMAL(5,2),
    braco_direito DECIMAL(5,2),
    braco_esquerdo DECIMAL(5,2),
    coxa_direita DECIMAL(5,2),
    coxa_esquerda DECIMAL(5,2),
    panturrilha_direita DECIMAL(5,2),
    panturrilha_esquerda DECIMAL(5,2),

    imc DECIMAL(4,2),
    percentual_musculo DECIMAL(4,2),
    percentual_gordura DECIMAL(4,2),
    metabolismo_repouso INT,
    idade_biologica INT,
    gordura_visceral DECIMAL(3,1),

    observacoes TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (aluno_id) REFERENCES aluno(id) ON DELETE CASCADE,
    FOREIGN KEY (avaliado_por) REFERENCES funcionario(id)
);

CREATE TABLE treino (
    id INT AUTO_INCREMENT PRIMARY KEY,
    turma_id INT NOT NULL,
    data_horario_inicio DATETIME NOT NULL,
    data_horario_termino DATETIME NOT NULL,
    status_treino ENUM('agendado', 'em_andamento', 'concluido', 'cancelado') DEFAULT 'agendado',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (turma_id) REFERENCES turma(id)
);

CREATE TABLE treino_modalidade (
    treino_id INT NOT NULL,
    modalidade_id INT NOT NULL,
    PRIMARY KEY (treino_id, modalidade_id),
    FOREIGN KEY (treino_id) REFERENCES treino(id) ON DELETE CASCADE,
    FOREIGN KEY (modalidade_id) REFERENCES modalidade(id) ON DELETE CASCADE
);

CREATE TABLE aluno_treino (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    treino_id INT NOT NULL,
    cadastrado_por INT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    situacao ENUM('matriculado', 'cancelado', 'presente', 'ausente', 'justificado') DEFAULT 'matriculado',
    checkin_time DATETIME,
    checkout_time DATETIME,
    observacoes TEXT,

    FOREIGN KEY (aluno_id) REFERENCES aluno(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (treino_id) REFERENCES treino(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (cadastrado_por) REFERENCES funcionario(id) ON DELETE SET NULL ON UPDATE CASCADE,

    UNIQUE KEY uq_aluno_treino (aluno_id, treino_id)
);

CREATE TABLE configuracoes (
    chave VARCHAR(100) PRIMARY KEY,
    valor TEXT NOT NULL,
    descricao TEXT,
    categoria VARCHAR(50) DEFAULT 'geral',
    tipo ENUM('texto', 'numero', 'booleano', 'json') DEFAULT 'texto',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE perfil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao VARCHAR(255),
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO perfil (nome, descricao, ativo) VALUES
('Administrador', 'Acesso total ao sistema', TRUE),
('Gerente', 'Acesso gerencial', TRUE),
('Instrutor', 'Acesso às funcionalidades de instrutor', TRUE),
('Atendente', 'Acesso limitado ao atendimento', TRUE);

CREATE TABLE permissao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao VARCHAR(250),
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE perfil_permissao (
    perfil_id INT NOT NULL,
    permissao_id INT NOT NULL,
    PRIMARY KEY (perfil_id, permissao_id),
    FOREIGN KEY (perfil_id) REFERENCES perfil(id),
    FOREIGN KEY (permissao_id) REFERENCES permissao(id)
);

CREATE TABLE funcionario_perfil (
    funcionario_id INT NOT NULL,
    perfil_id INT NOT NULL,
    PRIMARY KEY (funcionario_id, perfil_id),
    FOREIGN KEY (funcionario_id) REFERENCES funcionario(id),
    FOREIGN KEY (perfil_id) REFERENCES perfil(id)
);

CREATE TABLE cargo_perfil (
    cargo_id INT NOT NULL,
    perfil_id INT NOT NULL,
    PRIMARY KEY (cargo_id, perfil_id),
    FOREIGN KEY (cargo_id) REFERENCES cargo(id),
    FOREIGN KEY (perfil_id) REFERENCES perfil(id)
);

CREATE TABLE sistema_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    tipo ENUM('login', 'logout', 'criacao', 'edicao', 'exclusao', 'erro', 'acesso'),
    modulo VARCHAR(50),
    acao TEXT,
    ip VARCHAR(45),
    user_agent TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);