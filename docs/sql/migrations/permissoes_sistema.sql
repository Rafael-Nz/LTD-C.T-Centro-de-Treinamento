-- Migracao: permissoes_sistema.sql
-- Atualiza a tabela permissao e popula o catalogo completo de permissoes do sistema por cargo.

ALTER TABLE `permissao`
    ADD COLUMN IF NOT EXISTS `modulo` VARCHAR(50) NOT NULL DEFAULT 'Geral' AFTER `slug`,
    ADD COLUMN IF NOT EXISTS `nome` VARCHAR(100) NOT NULL DEFAULT '' AFTER `modulo`;

CREATE INDEX IF NOT EXISTS idx_permissao_modulo ON permissao (modulo);

-- Atualiza permissões existentes
UPDATE permissao SET modulo = 'Financeiro', nome = 'Visualizar Financeiro', descricao = 'Visualizar dados financeiros, servicos, planos e cobrancas' WHERE slug = 'financeiro.visualizar';
UPDATE permissao SET modulo = 'Financeiro', nome = 'Gerenciar Financeiro', descricao = 'Cadastrar e editar servicos, planos, contratos e gerar cobrancas' WHERE slug = 'financeiro.gerenciar';
UPDATE permissao SET modulo = 'Financeiro', nome = 'Registrar Pagamento', descricao = 'Registrar e estornar pagamentos de mensalidades e servicos' WHERE slug = 'financeiro.registrar_pagamento';

-- Insere novas permissões do catálogo
INSERT INTO permissao (slug, modulo, nome, descricao) VALUES
-- Alunos
('alunos.visualizar', 'Alunos', 'Visualizar Alunos', 'Permite listar e visualizar o perfil completo dos alunos'),
('alunos.criar', 'Alunos', 'Cadastrar Alunos', 'Permite cadastrar novos alunos no sistema'),
('alunos.editar', 'Alunos', 'Editar Alunos', 'Permite editar dados cadastrais de alunos'),
('alunos.desativar', 'Alunos', 'Desativar/Reativar Alunos', 'Permite desativar ou reativar alunos'),

-- Turmas
('turmas.visualizar', 'Turmas', 'Visualizar Turmas', 'Permite listar e visualizar turmas cadastradas'),
('turmas.gerenciar', 'Turmas', 'Gerenciar Turmas e Presenças', 'Permite gerenciar treinos da turma e lista de chamada/presenças'),
('turmas.criar', 'Turmas', 'Criar Turmas', 'Permite criar novas turmas e definir horários'),
('turmas.editar', 'Turmas', 'Editar Turmas', 'Permite alterar configurações de turmas existentes'),
('turmas.desativar', 'Turmas', 'Desativar/Reativar Turmas', 'Permite desativar ou reativar turmas'),

-- Treinos
('treinos.visualizar', 'Treinos', 'Visualizar Treinos', 'Permite consultar treinos cadastrados'),
('treinos.criar', 'Treinos', 'Criar Treinos', 'Permite cadastrar novos treinos e fichas'),
('treinos.editar', 'Treinos', 'Editar Treinos', 'Permite editar treinos existentes'),
('treinos.desativar', 'Treinos', 'Desativar/Reativar Treinos', 'Permite desativar ou reativar treinos'),

-- Avaliações Físicas
('avaliacoes.visualizar', 'Avaliações', 'Visualizar Avaliações', 'Permite visualizar histórico e detalhes das avaliações físicas'),
('avaliacoes.criar', 'Avaliações', 'Realizar Avaliação Física', 'Permite cadastrar novas avaliações físicas para alunos'),
('avaliacoes.editar', 'Avaliações', 'Editar Avaliação Física', 'Permite editar dados de avaliações físicas existentes'),

-- Anamnese
('anamnese.visualizar', 'Anamnese', 'Visualizar Anamnese', 'Permite consultar o questionário e respostas de anamnese dos alunos'),
('anamnese.preencher', 'Anamnese', 'Preencher Anamnese', 'Permite responder questionário de anamnese para alunos'),

-- Locais e Modalidades
('locais.visualizar', 'Locais e Modalidades', 'Visualizar Locais', 'Permite consultar os espaços e locais de treino'),
('locais.gerenciar', 'Locais e Modalidades', 'Gerenciar Locais', 'Permite cadastrar, editar e desativar locais de treino'),
('modalidades.visualizar', 'Locais e Modalidades', 'Visualizar Modalidades', 'Permite consultar as modalidades cadastradas'),
('modalidades.gerenciar', 'Locais e Modalidades', 'Gerenciar Modalidades', 'Permite cadastrar, editar e desativar modalidades'),

-- Relatórios
('relatorios.visualizar', 'Relatórios', 'Visualizar Relatórios', 'Permite consultar métricas gerais, frequências e relatórios'),
('relatorios.exportar', 'Relatórios', 'Exportar Relatórios', 'Permite exportar dados de relatórios em CSV e PDF'),

-- Gestão e Configurações
('cargos.visualizar', 'Configurações', 'Visualizar Cargos', 'Permite listar os cargos existentes'),
('cargos.gerenciar', 'Configurações', 'Gerenciar Cargos e Permissões', 'Permite criar, alterar cargos e atribuir permissões'),
('funcionarios.visualizar', 'Configurações', 'Visualizar Funcionários', 'Permite listar e visualizar colaboradores'),
('funcionarios.gerenciar', 'Configurações', 'Gerenciar Funcionários', 'Permite cadastrar, alterar e desativar funcionários'),
('usuarios.gerenciar', 'Configurações', 'Gerenciar Usuários Centrais', 'Permite gerenciar as contas gerais de usuário')
ON DUPLICATE KEY UPDATE 
    modulo = VALUES(modulo),
    nome = VALUES(nome),
    descricao = VALUES(descricao);

-- Vincula permissões padrão para os cargos existentes

-- Instrutor (id 2) - Treinos, Turmas, Avaliações, Alunos, Anamnese e Locais (sem acesso a configurações)
INSERT IGNORE INTO cargo_permissao (cargo_id, permissao_id)
SELECT 2, p.id FROM permissao p WHERE p.slug IN (
    'alunos.visualizar',
    'turmas.visualizar', 'turmas.gerenciar',
    'treinos.visualizar', 'treinos.criar', 'treinos.editar',
    'avaliacoes.visualizar', 'avaliacoes.criar', 'avaliacoes.editar',
    'anamnese.visualizar',
    'locais.visualizar'
);

-- Estagiário (id 3)
INSERT IGNORE INTO cargo_permissao (cargo_id, permissao_id)
SELECT 3, p.id FROM permissao p WHERE p.slug IN (
    'alunos.visualizar',
    'turmas.visualizar', 'turmas.gerenciar',
    'treinos.visualizar',
    'avaliacoes.visualizar',
    'anamnese.visualizar',
    'locais.visualizar'
);

-- Garante que Instrutor (id 2) e Estagiário (id 3) não possuam acesso a telas de configurações ou modalidades
DELETE cp FROM cargo_permissao cp
INNER JOIN permissao p ON p.id = cp.permissao_id
WHERE cp.cargo_id IN (2, 3) AND p.slug IN (
    'modalidades.visualizar', 'modalidades.gerenciar',
    'cargos.visualizar', 'cargos.gerenciar',
    'funcionarios.visualizar', 'funcionarios.gerenciar',
    'usuarios.gerenciar', 'configuracoes.gerenciar'
);

-- Gerente (id 4)
INSERT IGNORE INTO cargo_permissao (cargo_id, permissao_id)
SELECT 4, p.id FROM permissao p WHERE p.slug IN (
    'alunos.visualizar', 'alunos.criar', 'alunos.editar', 'alunos.desativar',
    'turmas.visualizar', 'turmas.gerenciar', 'turmas.criar', 'turmas.editar', 'turmas.desativar',
    'treinos.visualizar', 'treinos.criar', 'treinos.editar', 'treinos.desativar',
    'avaliacoes.visualizar', 'avaliacoes.criar', 'avaliacoes.editar',
    'anamnese.visualizar', 'anamnese.preencher',
    'locais.visualizar', 'locais.gerenciar',
    'modalidades.visualizar', 'modalidades.gerenciar',
    'financeiro.visualizar', 'financeiro.gerenciar', 'financeiro.registrar_pagamento',
    'relatorios.visualizar', 'relatorios.exportar',
    'cargos.visualizar',
    'funcionarios.visualizar'
);

-- Atendente (id 5)
INSERT IGNORE INTO cargo_permissao (cargo_id, permissao_id)
SELECT 5, p.id FROM permissao p WHERE p.slug IN (
    'alunos.visualizar', 'alunos.criar', 'alunos.editar',
    'turmas.visualizar',
    'treinos.visualizar',
    'anamnese.visualizar', 'anamnese.preencher',
    'locais.visualizar',
    'modalidades.visualizar',
    'financeiro.visualizar', 'financeiro.registrar_pagamento'
);
