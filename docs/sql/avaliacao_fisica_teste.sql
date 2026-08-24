-- Seed de apoio para testar o formulario e o historico de avaliacao fisica
-- Dependencias esperadas:
-- 1. docs/sql/aluno_teste.sql
-- 2. docs/sql/gerenciar_turma_seed.sql
-- 3. docs/sql/avaliacao_fisica_alter.sql em bases antigas

START TRANSACTION;

SET @aluno_teste_id = (
    SELECT a.usuario_id
    FROM aluno a
    INNER JOIN usuario u ON u.id = a.usuario_id
    WHERE u.cpf = '12345678901'
    ORDER BY a.usuario_id DESC
    LIMIT 1
);

SET @avaliador_teste_id = (
    SELECT f.usuario_id
    FROM funcionario f
    INNER JOIN usuario u ON u.id = f.usuario_id
    WHERE u.cpf = '90000000001'
    ORDER BY f.usuario_id DESC
    LIMIT 1
);

-- Avaliacao 1
INSERT INTO avaliacao_fisica (
    aluno_id,
    avaliador_id,
    data_avaliacao,
    peso,
    altura,
    imc,
    cintura,
    torax,
    braco_dc,
    braco_d,
    braco_ec,
    braco_e,
    coxa_d,
    coxa_e,
    panturrilha_d,
    panturrilha_e,
    percentual_gordura,
    percentual_musculo,
    metabolismo_repouso,
    idade_biologica,
    gordura_visceral,
    observacoes
)
SELECT
    @aluno_teste_id,
    @avaliador_teste_id,
    '2026-02-10',
    82.40,
    1.76,
    26.60,
    89.00,
    102.00,
    37.00,
    34.80,
    36.80,
    34.50,
    58.00,
    57.80,
    39.00,
    38.70,
    21.80,
    39.40,
    1710,
    30,
    9.00,
    'Primeira avaliacao de referencia. Aluno sedentario retornando aos treinos.'
WHERE @aluno_teste_id IS NOT NULL
  AND @avaliador_teste_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM avaliacao_fisica
      WHERE aluno_id = @aluno_teste_id
        AND data_avaliacao = '2026-02-10'
  );

-- Avaliacao 2
INSERT INTO avaliacao_fisica (
    aluno_id,
    avaliador_id,
    data_avaliacao,
    peso,
    altura,
    imc,
    cintura,
    torax,
    braco_dc,
    braco_d,
    braco_ec,
    braco_e,
    coxa_d,
    coxa_e,
    panturrilha_d,
    panturrilha_e,
    percentual_gordura,
    percentual_musculo,
    metabolismo_repouso,
    idade_biologica,
    gordura_visceral,
    observacoes
)
SELECT
    @aluno_teste_id,
    @avaliador_teste_id,
    '2026-03-15',
    79.90,
    1.76,
    25.79,
    86.50,
    101.00,
    36.60,
    34.90,
    36.30,
    34.70,
    57.60,
    57.20,
    38.60,
    38.40,
    19.90,
    40.60,
    1695,
    29,
    8.00,
    'Melhora inicial no condicionamento e reducao de cintura. Mantem boa aderencia.'
WHERE @aluno_teste_id IS NOT NULL
  AND @avaliador_teste_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM avaliacao_fisica
      WHERE aluno_id = @aluno_teste_id
        AND data_avaliacao = '2026-03-15'
  );

-- Avaliacao 3
INSERT INTO avaliacao_fisica (
    aluno_id,
    avaliador_id,
    data_avaliacao,
    peso,
    altura,
    imc,
    cintura,
    torax,
    braco_dc,
    braco_d,
    braco_ec,
    braco_e,
    coxa_d,
    coxa_e,
    panturrilha_d,
    panturrilha_e,
    percentual_gordura,
    percentual_musculo,
    metabolismo_repouso,
    idade_biologica,
    gordura_visceral,
    observacoes
)
SELECT
    @aluno_teste_id,
    @avaliador_teste_id,
    '2026-04-20',
    77.80,
    1.76,
    25.11,
    84.00,
    100.00,
    36.90,
    35.20,
    36.70,
    35.00,
    58.20,
    57.90,
    39.20,
    38.90,
    18.10,
    41.70,
    1705,
    28,
    7.00,
    'Aumento leve de massa magra com reducao continua do percentual de gordura.'
WHERE @aluno_teste_id IS NOT NULL
  AND @avaliador_teste_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM avaliacao_fisica
      WHERE aluno_id = @aluno_teste_id
        AND data_avaliacao = '2026-04-20'
  );

-- Avaliacao 4
INSERT INTO avaliacao_fisica (
    aluno_id,
    avaliador_id,
    data_avaliacao,
    peso,
    altura,
    imc,
    cintura,
    torax,
    braco_dc,
    braco_d,
    braco_ec,
    braco_e,
    coxa_d,
    coxa_e,
    panturrilha_d,
    panturrilha_e,
    percentual_gordura,
    percentual_musculo,
    metabolismo_repouso,
    idade_biologica,
    gordura_visceral,
    observacoes
)
SELECT
    @aluno_teste_id,
    @avaliador_teste_id,
    '2026-05-20',
    76.90,
    1.76,
    24.83,
    82.50,
    99.50,
    37.10,
    35.40,
    36.90,
    35.20,
    58.60,
    58.10,
    39.40,
    39.10,
    17.20,
    42.30,
    1720,
    27,
    6.00,
    'Evolucao consistente. Aluno proximo da faixa ideal de IMC e com melhora de composicao corporal.'
WHERE @aluno_teste_id IS NOT NULL
  AND @avaliador_teste_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM avaliacao_fisica
      WHERE aluno_id = @aluno_teste_id
        AND data_avaliacao = '2026-05-20'
  );

COMMIT;

-- Consulta de conferência:
-- SELECT id, aluno_id, avaliador_id, data_avaliacao, peso, altura, imc
-- FROM avaliacao_fisica
-- WHERE aluno_id = @aluno_teste_id
-- ORDER BY data_avaliacao DESC;
