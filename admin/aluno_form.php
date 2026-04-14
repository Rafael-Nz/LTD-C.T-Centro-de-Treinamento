<?php

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | <?= $id ? 'Editar Aluno' : 'Cadastrar Aluno' ?></title>
  <link rel="stylesheet" href="../public/css/bootstrap-5.3.8/bootstrap.css">
  <link rel="stylesheet" href="../public/css/admin-styles.css">
  <link rel="stylesheet" href="../public/css/form.css">
  <link rel="stylesheet" href="../public/css/sidebar.css">
  <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet"/>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="https://kit.fontawesome.com/2748b3b4b0.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet"/>
</head>
<body class="d-flex flex-column min-vh-100">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <?php include __DIR__ . '/partials/header.php'; ?>
  
  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 d-flex flex-column flex-fill">
      <h1 class="h4 mb-4"><?= $id ? "Editar Aluno" : "Cadastrar Aluno" ?></h1>
      <div class="card shadow-sm d-flex flex-fill">
        <div class="card-body">
          <form id="formAluno" data-id="<?= $id ?>" action="" method="POST">
            <?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>
            
            <div class="row gy-4">
              <!-- Informações Pessoais -->
              <div class="col-12">
                <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Informações Pessoais</h3>
                
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="nome" class="form-label">Nome:</label>
                    <input type="text" name="nome" id="nome" class="form-control" 
                           placeholder="Digite o nome" required>
                  </div>
                  
                  <div class="col-md-6">
                    <label for="sobrenome" class="form-label">Sobrenome:</label>
                    <input type="text" name="sobrenome" id="sobrenome" class="form-control" 
                           placeholder="Digite o sobrenome" required>
                  </div>
                </div>
                
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="cpf" class="form-label">CPF:</label>
                    <input type="text" name="cpf" id="cpf" class="form-control" 
                           placeholder="___.___.___-__" maxlength="14" required>
                  </div>
                  
                  <div class="col-md-6">
                    <label for="nascimento" class="form-label">Data de Nascimento:</label>
                    <input type="date" name="nascimento" id="nascimento" class="form-control" required>
                  </div>
                </div>
                
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="genero" class="form-label">Gênero:</label>
                    <select name="genero" id="genero" class="form-select" required>
                      <option value="">Selecione</option>
                      <option value="M">Masculino</option>
                      <option value="F">Feminino</option>
                      <option value="O">Outro / Prefiro não informar</option>
                    </select>
                  </div>
                  
                  <div class="col-md-6">
                    <label for="statusAluno" class="form-label">Status:</label>
                    <select name="status" id="statusAluno" class="form-select">
                      <option value="ativo">Ativo</option>
                      <option value="inativo">Inativo</option>
                      <option value="suspenso">Suspenso</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Contatos -->
              <div class="col-12">
                <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Contatos</h3>
                
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="telefone1" class="form-label">Telefone (Celular):</label>
                    <input type="tel" name="telefone1" id="telefone1" class="form-control" 
                           placeholder="(__) _____-____" maxlength="15" required>
                  </div>
                  
                  <div class="col-md-6">
                    <label for="telefone2" class="form-label">Telefone (Fixo/Opcional):</label>
                    <input type="tel" name="telefone2" id="telefone2" class="form-control" 
                           placeholder="(__) ____-____" maxlength="14">
                  </div>
                </div>
                
                <div class="mb-3">
                  <label for="email" class="form-label">E-mail:</label>
                  <input type="email" name="email" id="email" class="form-control" 
                         placeholder="email@exemplo.com" required>
                </div>
              </div>

              <!-- Endereço -->
              <div class="col-12">
                <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Endereço</h3>
                
                <div class="row g-3 mb-3">
                  <div class="col-md-8">
                    <label for="endereco" class="form-label">Logradouro/Rua:</label>
                    <input type="text" name="endereco" id="endereco" class="form-control" placeholder="Rua, Avenida, etc." required>
                  </div>
                  
                  <div class="col-md-4">
                    <label for="numero" class="form-label">Nº:</label>
                    <input type="text" name="numero" id="numero" class="form-control" placeholder="Ex: 123" required>
                  </div>
                </div>
                
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="cidade" class="form-label">Cidade:</label>
                    <input type="text" name="cidade" id="cidade" class="form-control" placeholder="Ex: São Luís" required>
                  </div>
                  
                  <div class="col-md-6">
                    <label for="bairro" class="form-label">Bairro:</label>
                    <input type="text" name="bairro" id="bairro" class="form-control" placeholder="Ex: Centro" required>
                  </div>
                </div>
                
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="cep" class="form-label">CEP:</label>
                    <div class="input-group">
                      <input type="text" name="cep" id="cep" class="form-control" placeholder="00000-000" maxlength="9" required>
                      <button class="btn border border-start-0" type="button" id="buscarCep" title="Buscar CEP">
                        <i class="ph ph-magnifying-glass"></i>
                      </button>
                    </div>
                  </div>
                  <div class="col-md-6">
                  </div>
                </div>
                
                <div class="mb-3">
                  <label for="complemento" class="form-label">Complemento (Opcional):</label>
                  <textarea name="complemento" id="complemento" class="form-control" 
                            rows="3" placeholder="Complemento do endereço (opcional)" 
                            style="resize: none;"></textarea>
                </div>
              </div>

              <!-- Informações Médicas (Anamnese) -->
              <div class="col-12">
                  <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Informações Médicas (Anamnese)</h3>
                  
                  <!-- Problema Cardíaco -->
                  <div class="mb-3">
                      <label class="form-label fw-semibold">Você tem algum problema cardíaco?</label>
                      <div class="d-flex gap-3">
                          <div class="form-check">
                              <input class="form-check-input check-conditional" data-target="problemaCardiacoObsDiv" type="radio"
                                  name="problemaCardiaco" id="problemaCardiacoSim" value="Sim">
                              <label class="form-check-label" for="problemaCardiacoSim">Sim</label>
                          </div>
                          <div class="form-check">
                              <input class="form-check-input check-conditional" data-target="problemaCardiacoObsDiv" type="radio"
                                  name="problemaCardiaco" id="problemaCardiacoNao" value="Não" checked>
                              <label class="form-check-label" for="problemaCardiacoNao">Não</label>
                          </div>
                      </div>
                      <div class="d-none mt-2" id="problemaCardiacoObsDiv">
                          <label for="problemaCardiacoObs" class="form-label">Qual problema cardíaco?</label>
                          <input type="text" class="form-control" name="problemaCardiacoObs" id="problemaCardiacoObs"
                              placeholder="Especifique o problema cardíaco...">
                      </div>
                  </div>
                  
                  <!-- Dor no Peito -->
                  <div class="mb-3">
                      <label class="form-label fw-semibold">Sente dores no peito com frequência?</label>
                      <div class="d-flex gap-3">
                          <div class="form-check">
                              <input class="form-check-input" type="radio" name="dorPeito" id="dorPeitoSim" value="Sim">
                              <label class="form-check-label" for="dorPeitoSim">Sim</label>
                          </div>
                          <div class="form-check">
                              <input class="form-check-input" type="radio" name="dorPeito" id="dorPeitoNao" value="Não" checked>
                              <label class="form-check-label" for="dorPeitoNao">Não</label>
                          </div>
                      </div>
                  </div>
                  
                  <!-- Desmaio ou Tontura -->
                  <div class="mb-3">
                      <label class="form-label fw-semibold">Você desmaia com frequência ou tem episódios de vertigem?</label>
                      <div class="d-flex gap-3">
                          <div class="form-check">
                              <input class="form-check-input" type="radio" name="desmaioTontura" id="desmaioTonturaSim" value="Sim">
                              <label class="form-check-label" for="desmaioTonturaSim">Sim</label>
                          </div>
                          <div class="form-check">
                              <input class="form-check-input" type="radio" name="desmaioTontura" id="desmaioTonturaNao" value="Não" checked>
                              <label class="form-check-label" for="desmaioTonturaNao">Não</label>
                          </div>
                      </div>
                  </div>
                  
                  <!-- Pressão Arterial -->
                  <div class="mb-3">
                      <label class="form-label fw-semibold">Algum médico já lhe diagnosticou com pressão arterial muito alta?</label>
                      <div class="d-flex gap-3">
                          <div class="form-check">
                              <input class="form-check-input" type="radio" name="pressaoArterial" id="pressaoArterialSim" value="Sim">
                              <label class="form-check-label" for="pressaoArterialSim">Sim</label>
                          </div>
                          <div class="form-check">
                              <input class="form-check-input" type="radio" name="pressaoArterial" id="pressaoArterialNao" value="Não" checked>
                              <label class="form-check-label" for="pressaoArterialNao">Não</label>
                          </div>
                      </div>
                  </div>
                  
                  <!-- Sintomas -->
                  <div class="mb-3">
                      <label class="form-label fw-semibold">Você tem algum dos sintomas abaixo?</label>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="Dor nas costas" id="sintomaDorCostas"
                              name="sintomas[]">
                          <label class="form-check-label" for="sintomaDorCostas">Dor nas costas</label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="Dor nas articulações, tendões ou músculo" id="sintomaDorArticular"
                              name="sintomas[]">
                          <label class="form-check-label" for="sintomaDorArticular">Dor nas articulações, tendões ou músculo</label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input check-conditional" data-target="doencaPulmonarDiv" type="checkbox"
                              value="Doença pulmonar" id="sintomaDoencaPulmonar" name="sintomas[]">
                          <label class="form-check-label" for="sintomaDoencaPulmonar">Doença pulmonar</label>
                      </div>
                      <div class="d-none mt-2" id="doencaPulmonarDiv">
                          <label for="doencaPulmonarObs" class="form-label">Qual doença pulmonar?</label>
                          <input type="text" class="form-control" name="doencaPulmonarObs" id="doencaPulmonarObs"
                              placeholder="Informe...">
                      </div>
                      <div class="form-check">
                          <input class="form-check-input" data-target="nenhumSintoma" type="checkbox" value="Nenhum"
                              id="nenhumSintoma" name="sintomas[]">
                          <label class="form-check-label" for="nenhumSintoma">Nenhum dos sintomas</label>
                      </div>
                  </div>
                  
                  <!-- Problema Ósseo ou Articular -->
                  <div class="mb-3">
                      <label class="form-label fw-semibold">Foi diagnosticado com problemas ósseos ou articulares que se agravam com exercícios?</label>
                      <div class="d-flex gap-3">
                          <div class="form-check">
                              <input class="form-check-input check-conditional" data-target="problemaOsseoObsDiv" type="radio"
                                  name="problemaOsseo" id="problemaOsseoSim" value="Sim">
                              <label class="form-check-label" for="problemaOsseoSim">Sim</label>
                          </div>
                          <div class="form-check">
                              <input class="form-check-input check-conditional" data-target="problemaOsseoObsDiv" type="radio"
                                  name="problemaOsseo" id="problemaOsseoNao" value="Não" checked>
                              <label class="form-check-label" for="problemaOsseoNao">Não</label>
                          </div>
                      </div>
                      <div class="d-none mt-2" id="problemaOsseoObsDiv">
                          <label for="problemaOsseoObs" class="form-label">Quais problemas ósseos ou articulares?</label>
                          <input type="text" class="form-control" name="problemaOsseoObs" id="problemaOsseoObs"
                              placeholder="Ex: artrose no joelho, tendinite no ombro, osteoporose...">
                      </div>
                  </div>
                  
                  <!-- Outro Problema -->
                  <div class="mb-3">
                      <label class="form-label fw-semibold">Algum outro motivo que possa impedir a prática de exercícios?</label>
                      <div class="d-flex gap-3">
                          <div class="form-check">
                              <input class="form-check-input check-conditional" data-target="outroProblemaObsDiv" type="radio"
                                  name="outroProblema" id="outroProblemaSim" value="Sim">
                              <label class="form-check-label" for="outroProblemaSim">Sim</label>
                          </div>
                          <div class="form-check">
                              <input class="form-check-input check-conditional" data-target="outroProblemaObsDiv" type="radio"
                                  name="outroProblema" id="outroProblemaNao" value="Não" checked>
                              <label class="form-check-label" for="outroProblemaNao">Não</label>
                          </div>
                      </div>
                      <div class="d-none mt-2" id="outroProblemaObsDiv">
                          <label for="outroProblemaObs" class="form-label">Qual outro motivo?</label>
                          <input type="text" class="form-control" name="outroProblemaObs" id="outroProblemaObs"
                              placeholder="Informe o motivo...">
                      </div>
                  </div>
                  
                  <!-- Medicamentos -->
                  <div class="mb-3">
                      <label class="form-label fw-semibold">Está tomando alguma medicação atualmente que possa afetar seu desempenho ou segurança?</label>
                      <div class="d-flex gap-3">
                          <div class="form-check">
                              <input class="form-check-input check-conditional" data-target="grupoTipoMedicamento" type="radio"
                                  name="tomandoMedicamento" id="medicamentoSimRadio" value="Sim">
                              <label class="form-check-label" for="medicamentoSimRadio">Sim</label>
                          </div>
                          <div class="form-check">
                              <input class="form-check-input check-conditional" data-target="grupoTipoMedicamento" type="radio"
                                  name="tomandoMedicamento" id="medicamentoNaoRadio" value="Não" checked>
                              <label class="form-check-label" for="medicamentoNaoRadio">Não</label>
                          </div>
                      </div>
                      <div class="d-none mt-2" id="grupoTipoMedicamento">
                          <label for="medicamentosTextarea" class="form-label">Quais medicamentos está tomando?</label>
                          <textarea class="form-control" id="medicamentosTextarea" name="medicamentos"
                              placeholder="Informe todos os medicamentos e motivo do uso Ex: Losartana 50 mg (1x ao dia, para pressão alta)..." rows="2" style="resize: none;"></textarea>
                      </div>
                  </div>
                  
                  <!-- Cirurgia -->
                  <div class="mb-3">
                      <label class="form-label fw-semibold">Você já fez alguma cirurgia?</label>
                      <div class="d-flex gap-3">
                          <div class="form-check">
                              <input class="form-check-input check-conditional" data-target="grupoTipoCirurgia" type="radio"
                                  name="fezCirurgia" id="cirurgiaSimRadio" value="Sim">
                              <label class="form-check-label" for="cirurgiaSimRadio">Sim</label>
                          </div>
                          <div class="form-check">
                              <input class="form-check-input check-conditional" data-target="grupoTipoCirurgia" type="radio"
                                  name="fezCirurgia" id="cirurgiaNaoRadio" value="Não" checked>
                              <label class="form-check-label" for="cirurgiaNaoRadio">Não</label>
                          </div>
                      </div>
                      <div class="d-none mt-2" id="grupoTipoCirurgia">
                          <label for="cirurgiaInput" class="form-label">Qual cirurgia?</label>
                          <input type="text" class="form-control" id="cirurgiaInput" name="cirurgia" placeholder="Informe qual a cirurgia...">
                          <label for="dataCirurgiaInput" class="form-label mt-2">Quando ocorreu?</label>
                          <input type="date" class="form-control" id="dataCirurgiaInput" name="dataCirurgia">
                      </div>
                  </div>
                  
                  <!-- Gravidez -->
                  <div class="mb-3">
                      <label class="form-label fw-semibold">Você está grávida?</label>
                      <div class="d-flex gap-3">
                          <div class="form-check">
                              <input class="form-check-input check-conditional" data-target="gravidaObsDiv" type="radio"
                                  name="gravida" id="gravidaSim" value="Sim">
                              <label class="form-check-label" for="gravidaSim">Sim</label>
                          </div>
                          <div class="form-check">
                              <input class="form-check-input check-conditional" data-target="gravidaObsDiv" type="radio"
                                  name="gravida" id="gravidaNao" value="Não" checked>
                              <label class="form-check-label" for="gravidaNao">Não</label>
                          </div>
                      </div>
                      <div class="d-none mt-2" id="gravidaObsDiv">
                          <label for="gravidaObs" class="form-label">Há quanto tempo?</label>
                          <input type="text" class="form-control" name="gravidaObs" id="gravidaObs" placeholder="Informe o tempo...">
                      </div>
                  </div>
                  
                  <!-- Fumo -->
                  <div class="mb-3">
                      <label class="form-label fw-semibold">Você fuma ou costuma fumar?</label>
                      <div class="d-flex gap-3">
                          <div class="form-check">
                              <input class="form-check-input" type="radio" name="fumante" id="fumanteSim" value="Sim">
                              <label class="form-check-label" for="fumanteSim">Sim</label>
                          </div>
                          <div class="form-check">
                              <input class="form-check-input" type="radio" name="fumante" id="fumanteNao" value="Não" checked>
                              <label class="form-check-label" for="fumanteNao">Não</label>
                          </div>
                      </div>
                  </div>
                  
                  <!-- Álcool -->
                  <div class="mb-3">
                      <label class="form-label fw-semibold">Você consome bebidas alcoólicas?</label>
                      <div class="d-flex gap-3">
                          <div class="form-check">
                              <input class="form-check-input" type="radio" name="consumoAlcool" id="alcoolSim" value="Sim">
                              <label class="form-check-label" for="alcoolSim">Sim</label>
                          </div>
                          <div class="form-check">
                              <input class="form-check-input" type="radio" name="consumoAlcool" id="alcoolNao" value="Não" checked>
                              <label class="form-check-label" for="alcoolNao">Não</label>
                          </div>
                      </div>
                  </div>
                  
                  <!-- Parente com Problema de Saúde -->
                  <div class="mb-3">
                      <label class="form-label fw-semibold">Algum parente  proxímo (Pai, Mãe, Irmão ou Irmã) seu tem problemas de saúde?</label>
                      <div class="d-flex gap-3">
                          <div class="form-check">
                              <input class="form-check-input check-conditional" data-target="grupoTipoProblema" type="radio"
                                  name="parenteProblema" id="parenteProblemaSim" value="Sim">
                              <label class="form-check-label" for="parenteProblemaSim">Sim</label>
                          </div>
                          <div class="form-check">
                              <input class="form-check-input check-conditional" data-target="grupoTipoProblema" type="radio"
                                  name="parenteProblema" id="parenteProblemaNao" value="Não" checked>
                              <label class="form-check-label" for="parenteProblemaNao">Não</label>
                          </div>
                      </div>
                      <div class="d-none mt-2" id="grupoTipoProblema">
                          <label for="parenteProblemaObs" class="form-label">Quais problemas?</label>
                          <input type="text" class="form-control" name="parenteProblemaObs" id="parenteProblemaObs"
                              placeholder="Informe o problema e o grau de parentesco (Ex: Pai, Hipertensão)">
                      </div>
                  </div>
                  
                  <!-- Atividade Física -->
                  <div class="mb-3">
                      <label class="form-label fw-semibold">Você realiza atividades físicas regularmente?</label>
                      <div class="d-flex gap-3">
                          <div class="form-check">
                              <input class="form-check-input check-conditional" data-target="grupoTipoAtividade" type="radio"
                                  name="atividadeFisica" id="atividadeFisicaSim" value="Sim">
                              <label class="form-check-label" for="atividadeFisicaSim">Sim</label>
                          </div>
                          <div class="form-check">
                              <input class="form-check-input check-conditional" data-target="grupoTipoAtividade" type="radio"
                                  name="atividadeFisica" id="atividadeFisicaNao" value="Não" checked>
                              <label class="form-check-label" for="atividadeFisicaNao">Não</label>
                          </div>
                      </div>
                      <div class="d-none mt-2" id="grupoTipoAtividade">
                          <label for="tipoAtividadeInput" class="form-label">Qual tipo de atividade?</label>
                          <input type="text" class="form-control" id="tipoAtividadeInput" name="tipoAtividade" placeholder="Ex: Caminhada, natação, futebol...">
                      </div>
                  </div>
                  
                  <!-- Objetivos -->
                  <div class="mb-3">
                      <label class="form-label fw-semibold">Quais seus objetivos ingressando em um grupo de promoção de sua saúde? (Pode selecionar mais de um)</label>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="1" id="objetivoPerderPeso"
                              name="objetivos[]">
                          <label class="form-check-label" for="objetivoPerderPeso">Perder peso</label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="2" id="objetivoGanharMassa"
                              name="objetivos[]">
                          <label class="form-check-label" for="objetivoGanharMassa">Ganhar massa muscular</label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="3" id="objetivoCondicionamento"
                              name="objetivos[]">
                          <label class="form-check-label" for="objetivoCondicionamento">Melhorar condicionamento</label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="4" id="objetivoCardiovascular"
                              name="objetivos[]">
                          <label class="form-check-label" for="objetivoCardiovascular">Melhorar preparo cardiovascular</label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="5 " id="objetivoDefinicao"
                              name="objetivos[]">
                          <label class="form-check-label" for="objetivoDefinicao">Definição muscular/condicionamento</label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="6" id="objetivoReabilitacao"
                              name="objetivos[]">
                          <label class="form-check-label" for="objetivoReabilitacao">Fins de reabilitação</label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="7" id="objetivoReducaoEstresse"
                              name="objetivos[]">
                          <label class="form-check-label" for="objetivoReducaoEstresse">Redução de estresse</label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="8" id="objetivoQualidadeVida"
                              name="objetivos[]">
                          <label class="form-check-label" for="objetivoQualidadeVida">Melhora na qualidade de vida</label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input check-conditional" data-target="objetivoOutrosDiv" type="checkbox"
                              value="Outros" id="objetivoOutrosCheck" name="objetivos[]">
                          <label class="form-check-label" for="objetivoOutrosCheck">Outros</label>
                      </div>
                      <div class="d-none mt-2" id="objetivoOutrosDiv">
                          <label for="objetivoOutros" class="form-label">Quais outros objetivos?</label>
                          <input type="text" class="form-control" name="objetivoOutros" id="objetivoOutrosInput"
                              placeholder="Informe os objetivos...">
                      </div>
                  </div>
                  
                  <!-- Observações -->
                  <div class="mb-3">
                      <label for="observacoesMedicas" class="form-label">Observações Médicas (Opcional):</label>
                      <textarea name="observacoesMedicas" id="observacoesMedicas" class="form-control" rows="3" 
                                  placeholder="Outras observações médicas relevantes." style="resize: none;"></textarea>
                  </div>
              </div>

              <!-- Informações da Matrícula -->
              <div class="col-12">
                <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Informações da Matrícula</h3>
                
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="dataMatricula" class="form-label">Data de Matrícula:</label>
                    <input type="date" name="dataMatricula" id="dataMatricula" class="form-control" required>
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a href="alunos.php" class="btn btn-red">Voltar</a>
              <button type="submit" class="btn btn-red">
                <?= $id ? "Salvar Alterações" : "Cadastrar Aluno" ?>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://unpkg.com/imask"></script>
  <script defer src="../public/js/bootstrap-5.3.8/bootstrap.bundle.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script defer src="../public/js/admin/sidebar.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {

    // ── Máscaras com fallback ─────────────────────────────────────────────────────
    function applyMasks() {
      if (typeof IMask !== 'undefined') {
        const cpfField = document.getElementById('cpf');
        const tel1Field = document.getElementById('telefone1');
        const tel2Field = document.getElementById('telefone2');
        const cepField = document.getElementById('cep');
        
        if (cpfField) IMask(cpfField, { mask: '000.000.000-00' });
        if (tel1Field) IMask(tel1Field, { mask: '(00) 00000-0000' });
        if (tel2Field) IMask(tel2Field, { mask: '(00) 0000-0000' });
        if (cepField) IMask(cepField, { mask: '00000-000' });
      } else {
        console.warn('IMask não carregado, usando máscaras manuais');
        // Máscaras manuais como fallback
        document.getElementById('cpf')?.addEventListener('input', function(e) {
          let v = e.target.value.replace(/\D/g, '');
          if (v.length <= 11) {
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = v;
          }
        });
      }
    }
    applyMasks();

    // ── Data de matrícula padrão ─────────────────────────────────────────────────
    const dataMatriculaField = document.getElementById('dataMatricula');
    if (dataMatriculaField) {
      dataMatriculaField.value = new Date().toISOString().split('T')[0];
    }

    // ── Busca de CEP ─────────────────────────────────────────────────────────────
    const buscarCepBtn = document.getElementById('buscarCep');
    if (buscarCepBtn) {
      buscarCepBtn.addEventListener('click', function () {
        const cepInput = document.getElementById('cep');
        const cep = cepInput ? cepInput.value.replace(/\D/g, '') : '';
        if (cep.length !== 8) {
          Swal.fire('Atenção', 'Digite um CEP válido com 8 dígitos.', 'warning');
          return;
        }
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
          .then(r => r.json())
          .then(data => {
            if (data.erro) { 
              Swal.fire('Atenção', 'CEP não encontrado.', 'warning'); 
              return; 
            }
            const enderecoField = document.getElementById('endereco');
            const bairroField = document.getElementById('bairro');
            const cidadeField = document.getElementById('cidade');
            const numeroField = document.getElementById('numero');
            
            if (enderecoField) enderecoField.value = data.logradouro || '';
            if (bairroField) bairroField.value = data.bairro || '';
            if (cidadeField) cidadeField.value = data.localidade || '';
            if (numeroField) numeroField.focus();
          })
          .catch(() => Swal.fire('Erro', 'Não foi possível buscar o CEP.', 'error'));
      });
    }

    // ── Campos condicionais ───────────────────────────────────────────────────────
    function setupConditionalFields() {
      document.querySelectorAll('.check-conditional').forEach(function (el) {
        el.addEventListener('change', function () {
          const targetId = this.getAttribute('data-target');
          if (!targetId) return;
          const target = document.getElementById(targetId);
          if (!target) return;

          if (this.type === 'radio') {
            const simSelecionado = [...document.querySelectorAll(`[name="${this.name}"]`)]
              .some(r => r.value === 'Sim' && r.checked);
            target.classList.toggle('d-none', !simSelecionado);
          }

          if (this.type === 'checkbox') {
            const algumMarcado = [...document.querySelectorAll(`[data-target="${targetId}"]`)]
              .some(c => c.checked);
            target.classList.toggle('d-none', !algumMarcado);
          }
        });
      });
    }
    setupConditionalFields();

    // ── Validação ─────────────────────────────────────────────────────────────────
    function showError(fieldId, msg) {
      const field = document.getElementById(fieldId);
      if (!field) return;
      field.classList.add('is-invalid');
      let fb = field.nextElementSibling;
      if (!fb || !fb.classList.contains('invalid-feedback')) {
        fb = document.createElement('div');
        fb.className = 'invalid-feedback';
        field.parentNode.insertBefore(fb, field.nextSibling);
      }
      fb.textContent = msg;
    }

    function clearErrors() {
      document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
      document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function validate() {
      let valid = true;
      clearErrors();

      const nome = document.getElementById('nome')?.value.trim();
      if (!nome) { showError('nome', 'Nome é obrigatório'); valid = false; }

      const sobrenome = document.getElementById('sobrenome')?.value.trim();
      if (!sobrenome) { showError('sobrenome', 'Sobrenome é obrigatório'); valid = false; }

      const cpf = document.getElementById('cpf')?.value.replace(/\D/g, '');
      if (!cpf || cpf.length !== 11) { showError('cpf', 'CPF inválido'); valid = false; }

      const nasc = new Date(document.getElementById('nascimento')?.value);
      if (isNaN(nasc.getTime()) || nasc >= new Date()) { 
        showError('nascimento', 'Data de nascimento inválida'); 
        valid = false; 
      }

      const email = document.getElementById('email')?.value;
      if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { 
        showError('email', 'E-mail inválido'); 
        valid = false; 
      }

      const telefone1 = document.getElementById('telefone1')?.value.replace(/\D/g, '');
      if (!telefone1 || telefone1.length < 10) { 
        showError('telefone1', 'Telefone celular é obrigatório'); 
        valid = false; 
      }

      const dataMatricula = document.getElementById('dataMatricula')?.value;
      if (!dataMatricula) { 
        showError('dataMatricula', 'Data de matrícula é obrigatória'); 
        valid = false; 
      }

      return valid;
    }

    // ── Coleta do questionário (corrigido para corresponder ao backend) ───────────
    function getRadioValue(name, defaultValue = 'Não') {
      const el = document.querySelector(`[name="${name}"]:checked`);
      return el ? el.value : defaultValue;
    }

    function buildPayload() {
      // Sintomas
      const sintomas = [...document.querySelectorAll('[name="sintomas[]"]:checked')]
        .map(c => c.value)
        .filter(v => v !== 'Nenhum');
      
      const temNenhumSintoma = [...document.querySelectorAll('[name="sintomas[]"]:checked')]
        .some(c => c.value === 'Nenhum');

      // Objetivos (IDs)
      const objetivos = [...document.querySelectorAll('[name="objetivos[]"]:checked')]
          .map(cb => cb.value)
          .filter(v => v !== 'Outros' && !isNaN(parseInt(v))) // filtra "Outros" e garante número
          .map(v => parseInt(v)); // converte para inteiro

      return {
        // Informações Pessoais
        nome: document.getElementById('nome')?.value.trim() || '',
        sobrenome: document.getElementById('sobrenome')?.value.trim() || '',
        cpf: document.getElementById('cpf')?.value || '',
        nascimento: document.getElementById('nascimento')?.value || '',
        genero: document.getElementById('genero')?.value || '',
        status: document.getElementById('statusAluno')?.value || 'ativo',

        // Contatos
        telefone1: document.getElementById('telefone1')?.value || '',
        telefone2: document.getElementById('telefone2')?.value || '',
        email: document.getElementById('email')?.value || '',

        // Endereço
        endereco: document.getElementById('endereco')?.value || '',
        numero: document.getElementById('numero')?.value || '',
        cidade: document.getElementById('cidade')?.value || '',
        bairro: document.getElementById('bairro')?.value || '',
        cep: document.getElementById('cep')?.value || '',
        complemento: document.getElementById('complemento')?.value || '',

        // Matrícula
        dataMatricula: document.getElementById('dataMatricula')?.value || '',

        // Anamnese - Nomes dos campos CORRETOS para o backend
        problemaCardiaco: getRadioValue('problemaCardiaco'),
        problemaCardiacoObs: document.getElementById('problemaCardiacoObs')?.value || null,
        
        dorPeito: getRadioValue('dorPeito'),
        desmaioTontura: getRadioValue('desmaioTontura'),
        pressaoArterial: getRadioValue('pressaoArterial'),
        
        sintomas: sintomas,
        doencaPulmonarObs: document.getElementById('doencaPulmonarObs')?.value || null,
        
        problemaOsseoArticular: getRadioValue('problemaOsseo'),
        problemaOsseoArticularObs: document.getElementById('problemaOsseoObs')?.value || null,
        
        limitacaoFisica: getRadioValue('outroProblema'),
        limitacaoFisicaObs: document.getElementById('outroProblemaObs')?.value || null,
        
        medicamento: getRadioValue('tomandoMedicamento'),
        medicamentoObs: document.getElementById('medicamentosTextarea')?.value || null,
        
        cirurgia: getRadioValue('fezCirurgia'),
        cirurgiaObs: document.getElementById('cirurgiaInput')?.value || null,
        cirurgiaData: document.getElementById('dataCirurgiaInput')?.value || null,
        
        gravida: getRadioValue('gravida'),
        gravidaTempo: document.getElementById('gravidaObs')?.value || null,
        
        atividadeFisica: getRadioValue('atividadeFisica'),
        tipoAtividade: document.getElementById('tipoAtividadeInput')?.value || null,
        
        fumante: getRadioValue('fumante'),
        alcool: getRadioValue('consumoAlcool'),
        
        problemaFamilia: getRadioValue('parenteProblema'),
        parenteProblemaObs: document.getElementById('parenteProblemaObs')?.value || null,
        
        objetivos: objetivos,
        objetivoOutros: document.getElementById('objetivoOutrosInput')?.value || null,
        
        observacoesMedicas: document.getElementById('observacoesMedicas')?.value || ''
      };
    }

    // Função auxiliar para definir valor de campos
    function setValue(id, value) {
      var field = document.getElementById(id);
      if (field) {
        field.value = (value !== null && value !== undefined) ? value : '';
      }
    }

    // Função auxiliar para definir radio button
    function setRadioValue(name, value) {
      var radio = document.querySelector('[name="' + name + '"][value="' + value + '"]');
      if (radio) radio.checked = true;
    }

    // Função auxiliar para definir checkbox
    function setCheckbox(id, checked) {
      var checkbox = document.getElementById(id);
      if (checkbox) checkbox.checked = checked;
    }

    // ── Carregar dados para edição ────────────────────────────────────────────────
    const idAluno = <?= $id ?? 'null' ?>;

    async function loadAlunoData() {
      if (!idAluno) return;

      try {
        const response = await fetch(`/ctt/api/v1/aluno/${idAluno}`);
        const data = await response.json();

        if (data.success && data.data) {
          const aluno = data.data;
          
          // Informações Pessoais
          setValue('nome', aluno.nome);
          setValue('sobrenome', aluno.sobrenome);
          setValue('cpf', aluno.cpf);
          setValue('nascimento', aluno.data_nascimento);
          setValue('genero', aluno.genero);
          
          // Status
          var statusField = document.getElementById('statusAluno');
          if (statusField) statusField.value = aluno.ativo ? 'ativo' : 'inativo';
          
          // Contatos
          if (aluno.contatos && Array.isArray(aluno.contatos)) {
            var telefoneCelular = aluno.contatos.find(function(c) { return c.tipo_contato === 'telefone' && c.observacao === 'Celular'; });
            var telefoneFixo = aluno.contatos.find(function(c) { return c.tipo_contato === 'telefone' && c.observacao === 'Fixo'; });
            var emailContato = aluno.contatos.find(function(c) { return c.tipo_contato === 'email'; });
            
            setValue('telefone1', telefoneCelular ? telefoneCelular.valor : '');
            setValue('telefone2', telefoneFixo ? telefoneFixo.valor : '');
            setValue('email', emailContato ? emailContato.valor : '');
          }
          
          // Endereço
          setValue('endereco', aluno.logradouro);
          setValue('numero', aluno.numero);
          setValue('cidade', aluno.cidade);
          setValue('bairro', aluno.bairro);
          setValue('cep', aluno.cep);
          setValue('complemento', aluno.complemento);
          
          // Matrícula
          setValue('dataMatricula', aluno.data_matricula);
          
          // Questionário
          if (aluno.questionario) {
            var q = aluno.questionario;

            setRadioValue('problemaCardiaco', q.problema_cardiaco ? 'Sim' : 'Não');
            setRadioValue('dorPeito', q.dor_peito ? 'Sim' : 'Não');
            setRadioValue('desmaioTontura', q.desmaia_frequencia ? 'Sim' : 'Não');
            setRadioValue('pressaoArterial', q.pressao_alta ? 'Sim' : 'Não');
            setRadioValue('problemaOsseo', q.osseo_articular ? 'Sim' : 'Não');
            setRadioValue('outroProblema', q.limitacao_fisica ? 'Sim' : 'Não');
            setRadioValue('tomandoMedicamento', q.medicamento_continuo ? 'Sim' : 'Não');
            setRadioValue('fezCirurgia', q.cirurgia_anterior ? 'Sim' : 'Não');
            setRadioValue('gravida', q.gravida ? 'Sim' : 'Não');
            setRadioValue('atividadeFisica', q.pratica_exercicios ? 'Sim' : 'Não');
            setRadioValue('fumante', q.fumante ? 'Sim' : 'Não');
            setRadioValue('consumoAlcool', q.consumo_alcool ? 'Sim' : 'Não');
            setRadioValue('parenteProblema', q.problema_saude_familia ? 'Sim' : 'Não');
            
            setValue('problemaCardiacoObs', q.problema_cardiaco_descricao);
            setValue('doencaPulmonarObs', q.doenca_pulmonar_descricao);
            setValue('problemaOsseoObs', q.osseo_articular_descricao);
            setValue('outroProblemaObs', q.limitacao_descricao);
            setValue('medicamentosTextarea', q.medicamento_descricao);
            setValue('cirurgiaInput', q.cirurgia_descricao);
            setValue('dataCirurgiaInput', q.cirurgia_data);
            setValue('gravidaObs', q.gravida_tempo);
            setValue('tipoAtividadeInput', q.tipo_exercicios);
            setValue('parenteProblemaObs', q.problema_saude_familia_descricao);
            setValue('objetivoOutrosInput', q.outros_objetivos);
            setValue('observacoesMedicas', q.observacoes_medicas);
            
            // Sintomas
            setCheckbox('sintomaDorCostas', q.dor_costa === 1);
            setCheckbox('sintomaDorArticular', q.dor_musculo === 1);
            setCheckbox('sintomaDoencaPulmonar', q.doenca_pulmonar === 1);
            setCheckbox('nenhumSintoma', q.nenhum_sintoma === 1);
            
            // ── Objetivos ─────────────────────────────────────
            if (aluno.objetivos && Array.isArray(aluno.objetivos)) {
                const objetivosIds = aluno.objetivos.map(o => o.id); // pega os IDs

                document.querySelectorAll('[name="objetivos[]"]').forEach(function (checkbox) {
                    const val = checkbox.value;
                    // Marca se o ID do checkbox está no array de IDs retornados
                    if (objetivosIds.includes(parseInt(val))) {
                        checkbox.checked = true;
                    }
                });
            }

            // Disparar campos condicionais
            document.querySelectorAll('.check-conditional').forEach(function(el) {
              el.dispatchEvent(new Event('change'));
            });
          }
        }
      } catch (error) {
        console.error('Erro ao carregar dados do aluno:', error);
      }
    }

    // ── Submit via fetch ──────────────────────────────────────────────────────────
    const form = document.getElementById('formAluno');
    if (form) {
      form.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (!validate()) {
          Swal.fire('Atenção', 'Por favor, corrija os erros no formulário.', 'warning');
          return;
        }

        const payload = buildPayload();
        const url = idAluno 
          ? `/ctt/api/v1/aluno/${idAluno}` 
          : '/ctt/api/v1/aluno/';
        const method = idAluno ? 'PUT' : 'POST';

        const btn = this.querySelector('[type="submit"]');
        if (btn) btn.disabled = true;

        try {
          const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
          });

          const data = await res.json();

          if (data.success) {
            await Swal.fire({
              icon: 'success',
              title: idAluno ? 'Aluno atualizado!' : 'Aluno cadastrado!',
              text: data.data?.message || (idAluno ? 'Aluno atualizado com sucesso!' : 'Aluno cadastrado com sucesso!'),
              confirmButtonText: 'OK',
            });
            window.location.href = 'alunos.php';
          } else {
            Swal.fire('Erro', data.message || 'Ocorreu um erro ao processar sua solicitação.', 'error');
            if (btn) btn.disabled = false;
          }
        } catch (err) {
          console.error('Erro na requisição:', err);
          Swal.fire('Erro', 'Falha na comunicação com o servidor. Verifique sua conexão.', 'error');
          if (btn) btn.disabled = false;
        }
      });
    }

    // Carregar dados se for edição
    loadAlunoData();

  });
  </script>
</body>
</html>