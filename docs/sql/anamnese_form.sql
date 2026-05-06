-- ─── Formulário padrão ────────────────────────────────────────────────────────
INSERT INTO anamnese_formulario (id, nome, descricao, versao, ativo, criado_por)
VALUES (1, 'Anamnese Padrão', 'Formulário padrão de avaliação de saúde para novos alunos', 1, TRUE, 1);


-- ─── Perguntas ────────────────────────────────────────────────────────────────
INSERT INTO anamnese_pergunta
(formulario_id, slug, pergunta, tipo_input, obrigatoria, ordem, config, regra_exibicao)
VALUES

-- 1
(1, 'problema_cardiaco',
 'Algum médico já lhe diagnosticou com problema cardíaco?',
 'radio', 0, 1, NULL, NULL),

-- 2
(1, 'problema_cardiaco_obs',
 'Qual problema cardíaco?',
 'text', 0, 2,
 JSON_OBJECT('placeholder', 'Especifique o problema cardíaco...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','problema_cardiaco','operator','equals','valor','sim'))),

-- 3
(1, 'dor_peito',
 'Você tem dores no peito com frequência?',
 'radio', 0, 3, NULL, NULL),

-- 4
(1, 'desmaio_tontura',
 'Você desmaia com frequência ou tem episódios de tontura/vertigem?',
 'radio', 0, 4, NULL, NULL),

-- 5
(1, 'pressao_arterial',
 'Algum médico já lhe diagnosticou com pressão arterial muito alta?',
 'radio', 0, 5, NULL, NULL),

-- 6
(1, 'problema_osseo',
 'Algum médico já lhe diagnosticou com problemas ósseos ou articulares?',
 'radio', 0, 6, NULL, NULL),

-- 7
(1, 'problema_osseo_obs',
 'Quais problemas ósseos ou articulares?',
 'text', 0, 7,
 JSON_OBJECT('placeholder', 'Ex: artrose no joelho, tendinite no ombro, osteoporose...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','problema_osseo','operator','equals','valor','sim'))),

-- 8
(1, 'outro_problema',
 'Algum outro motivo que possa impedir a prática de exercícios?',
 'radio', 0, 8, NULL, NULL),

-- 9
(1, 'outro_problema_obs',
 'Qual outro motivo?',
 'text', 0, 9,
 JSON_OBJECT('placeholder', 'Informe o motivo...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','outro_problema','operator','equals','valor','sim'))),

-- 10
(1, 'medicamentos',
 'Está tomando alguma medicação atualmente?',
 'radio', 0, 10, NULL, NULL),

-- 11
(1, 'medicamentos_obs',
 'Informe quais medicamentos está tomando?',
 'textarea', 0, 11,
 JSON_OBJECT('placeholder', 'Informe quais medicamentos...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','medicamentos','operator','equals','valor','sim'))),

-- 12
(1, 'cirurgia',
 'Você já fez alguma cirurgia?',
 'radio', 0, 12, NULL, NULL),

-- 13
(1, 'cirurgia_nome',
 'Qual cirurgia?',
 'text', 0, 13,
 JSON_OBJECT('placeholder', 'Informe qual a cirurgia...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','cirurgia','operator','equals','valor','sim'))),

-- 14
(1, 'cirurgia_data',
 'Quando ocorreu?',
 'date', 0, 14, NULL,
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','cirurgia','operator','equals','valor','sim'))),

-- 15
(1, 'gravida',
 'Você está grávida?',
 'radio', 0, 15, NULL, NULL),

-- 16
(1, 'gravida_tempo',
 'Há quanto tempo?',
 'text', 0, 16,
 JSON_OBJECT('placeholder', 'Informe o tempo de gestação...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','gravida','operator','equals','valor','sim'))),

-- 17
(1, 'fumante',
 'Você fuma ou costuma fumar?',
 'radio', 0, 17, NULL, NULL),

-- 18
(1, 'consumo_alcool',
 'Você consome bebidas alcoólicas?',
 'radio', 0, 18, NULL, NULL),

-- 19
(1, 'historico_familiar',
 'Algum parente próximo (Pai, Mãe, Irmão ou Irmã) seu teve ataque cardíaco antes dos 50 anos?',
 'radio', 0, 19, NULL, NULL),

-- 20
(1, 'atividade_fisica',
 'Você realiza atividades físicas regularmente?',
 'radio', 0, 20, NULL, NULL),

-- 21
(1, 'tipo_atividade',
 'Qual tipo de atividade?',
 'text', 0, 21,
 JSON_OBJECT('placeholder', 'Ex: Caminhada, natação, futebol...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','atividade_fisica','operator','equals','valor','sim'))),

-- 22
(1, 'sintomas',
 'Você tem algum dos sintomas abaixo?',
 'checkbox', 0, 22, NULL, NULL),

-- 23
(1, 'doenca_pulmonar_obs',
 'Qual doença pulmonar?',
 'text', 0, 23,
 JSON_OBJECT('placeholder', 'Informe a doença pulmonar...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','sintomas','operator','contains','valor','doenca_pulmonar'))),

-- 24
(1, 'objetivos',
 'Quais seus objetivos ingressando em um grupo de promoção de sua saúde? (Pode selecionar mais de um)',
 'checkbox', 0, 24, NULL, NULL),

-- 25
(1, 'objetivos_outros',
 'Quais outros objetivos?',
 'text', 0, 25,
 JSON_OBJECT('placeholder', 'Informe os outros objetivos...'),
 JSON_OBJECT('if', JSON_OBJECT('pergunta_slug','objetivos','operator','contains','valor','outros'))),

-- 26
(1, 'observacoes_medicas',
 'Observações Médicas (Opcional)',
 'textarea', 0, 26,
 JSON_OBJECT('placeholder', 'Outras observações médicas relevantes...'),
 NULL);


-- ─── Opções Sim/Não ───────────────────────────────────────────────────────────
INSERT INTO anamnese_opcao (pergunta_id, label, valor, ordem)
SELECT id, 'Sim', 'sim', 1 FROM anamnese_pergunta WHERE tipo_input = 'radio' AND formulario_id = 1;

INSERT INTO anamnese_opcao (pergunta_id, label, valor, ordem)
SELECT id, 'Não', 'nao', 2 FROM anamnese_pergunta WHERE tipo_input = 'radio' AND formulario_id = 1;


-- ─── Opções de Sintomas ───────────────────────────────────────────────────────
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


-- ─── Opções de Objetivos ──────────────────────────────────────────────────────
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