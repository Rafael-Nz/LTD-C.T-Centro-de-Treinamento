-- 1. Inserir endereço do aluno
INSERT INTO endereco (logradouro, numero, cidade, bairro, cep, complemento)
VALUES ('Rua das Palmeiras', '123', 'São Luís', 'Cohama', '65000000', 'Apto 101');

-- 2. Inserir usuário do tipo aluno
INSERT INTO usuario (
    nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario
) VALUES (
    'João', 'Silva', '12345678901', 'joao.silva@email.com',
    '$argon2id$v=19$m=65536,t=4,p=1$enBzQTh6a3NuRTAwWVFFNg$D1fBTREiUz8MPOsv4hl6WI7EgKRbK4+9nl7wf6+U1Sw',
    '2000-05-10', 'M', 2, 'aluno'
);

-- 3. Inserir aluno (matrícula)
INSERT INTO aluno (
    usuario_id, data_matricula, cadastrado_por, codigo_matricula
) VALUES (
    2, CURDATE(), 1, '202605000001'
);

-- 4. Inserir contatos
INSERT INTO contato (usuario_id, tipo, valor) VALUES
(2, 'telefone', '98999999999'),
(2, 'email_secundario', 'joao.alt@email.com');