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
          <form id="formAluno" action="" method="POST">
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
                    <label for="estado" class="form-label">Estado:</label>
                    <select name="estado" id="estado" class="form-select" required>
                      <option value="">Selecione</option>
                      <option value="AC">Acre</option>
                      <option value="AL">Alagoas</option>
                      <option value="AP">Amapá</option>
                      <option value="AM">Amazonas</option>
                      <option value="BA">Bahia</option>
                      <option value="CE">Ceará</option>
                      <option value="DF">Distrito Federal</option>
                      <option value="ES">Espírito Santo</option>
                      <option value="GO">Goiás</option>
                      <option value="MA">Maranhão</option>
                      <option value="MT">Mato Grosso</option>
                      <option value="MS">Mato Grosso do Sul</option>
                      <option value="MG">Minas Gerais</option>
                      <option value="PA">Pará</option>
                      <option value="PB">Paraíba</option>
                      <option value="PR">Paraná</option>
                      <option value="PE">Pernambuco</option>
                      <option value="PI">Piauí</option>
                      <option value="RJ">Rio de Janeiro</option>
                      <option value="RN">Rio Grande do Norte</option>
                      <option value="RS">Rio Grande do Sul</option>
                      <option value="RO">Rondônia</option>
                      <option value="RR">Roraima</option>
                      <option value="SC">Santa Catarina</option>
                      <option value="SP">São Paulo</option>
                      <option value="SE">Sergipe</option>
                      <option value="TO">Tocantins</option>
                    </select>
                  </div>
                  
                  <div class="col-md-6">
                    <label for="cep" class="form-label">CEP:</label>
                    <div class="input-group">
                      <input type="text" name="cep" id="cep" class="form-control" placeholder="00000-000" maxlength="9" required>
                      <button class="btn border border-start-0" type="button" id="buscarCep" title="Buscar CEP">
                        <i class="ph ph-magnifying-glass"></i>
                      </button>
                    </div>
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
                              placeholder="Informe todos os medicamentos..." rows="2" style="resize: none;"></textarea>
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
                      <label class="form-label fw-semibold">Você está grávida ou suspeita que possa estar?</label>
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
                      <label class="form-label fw-semibold">Você consome bebidas alcoólicas regularmente?</label>
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
                      <label class="form-label fw-semibold">Algum parente seu tem problemas de saúde?</label>
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
                      <label class="form-label fw-semibold">Quais seus objetivos ao se matricular? (Pode selecionar mais de um)</label>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="Perder peso" id="objetivoPerderPeso"
                              name="objetivos[]">
                          <label class="form-check-label" for="objetivoPerderPeso">Perder peso</label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="Ganhar massa muscular" id="objetivoGanharMassa"
                              name="objetivos[]">
                          <label class="form-check-label" for="objetivoGanharMassa">Ganhar massa muscular</label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="Melhorar condicionamento" id="objetivoCondicionamento"
                              name="objetivos[]">
                          <label class="form-check-label" for="objetivoCondicionamento">Melhorar condicionamento</label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="Melhorar preparo cardiovascular" id="objetivoCardiovascular"
                              name="objetivos[]">
                          <label class="form-check-label" for="objetivoCardiovascular">Melhorar preparo cardiovascular</label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="Definição muscular/condicionamento" id="objetivoDefinicao"
                              name="objetivos[]">
                          <label class="form-check-label" for="objetivoDefinicao">Definição muscular/condicionamento</label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="Fins de reabilitação" id="objetivoReabilitacao"
                              name="objetivos[]">
                          <label class="form-check-label" for="objetivoReabilitacao">Fins de reabilitação</label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="Redução de estresse" id="objetivoReducaoEstresse"
                              name="objetivos[]">
                          <label class="form-check-label" for="objetivoReducaoEstresse">Redução de estresse</label>
                      </div>
                      <div class="form-check">
                          <input class="form-check-input" type="checkbox" value="Melhora na qualidade de vida" id="objetivoQualidadeVida"
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
                
                <div class="mb-3">
                  <label for="observacoes" class="form-label">Observações (Opcional):</label>
                  <textarea name="observacoes" id="observacoes" class="form-control" rows="3" 
                            placeholder="Anotações sobre o aluno." style="resize: none;"></textarea>
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
    // Máscaras para os campos (igual ao funcionario_form.php)
    document.addEventListener('DOMContentLoaded', function() {
        // Máscara para CPF
        const cpfMask = IMask(document.getElementById('cpf'), {
            mask: '000.000.000-00'
        });
        
        // Máscara para Telefone 1 (celular)
        const telefone1Mask = IMask(document.getElementById('telefone1'), {
            mask: '(00) 00000-0000'
        });
        
        // Máscara para Telefone 2 (fixo)
        const telefone2Mask = IMask(document.getElementById('telefone2'), {
            mask: '(00) 0000-0000'
        });
        
        // Máscara para CEP
        const cepMask = IMask(document.getElementById('cep'), {
            mask: '00000-000'
        });
        
        // Buscar CEP
        document.getElementById('buscarCep').addEventListener('click', function() {
            const cep = document.getElementById('cep').value.replace(/\D/g, '');
            
            if (cep.length !== 8) {
                Swal.fire('Atenção', 'Digite um CEP válido com 8 dígitos.', 'warning');
                return;
            }
            
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        document.getElementById('endereco').value = data.logradouro;
                        document.getElementById('bairro').value = data.bairro;
                        document.getElementById('cidade').value = data.localidade;
                        document.getElementById('numero').focus();
                    } else {
                        Swal.fire('Atenção', 'CEP não encontrado.', 'warning');
                    }
                })
                .catch(error => {
                    Swal.fire('Erro', 'Não foi possível buscar o CEP.', 'error');
                });
        });
        
        // Funcionalidade para campos condicionais
        document.querySelectorAll('.check-conditional').forEach(function(element) {
            element.addEventListener('change', function() {
                const targetId = this.getAttribute('data-target');
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    if ((this.type === 'radio' && this.value === 'Sim' && this.checked) || 
                        (this.type === 'checkbox' && this.checked)) {
                        targetElement.classList.remove('d-none');
                    } else {
                        // Para checkboxes, verificar se outros estão selecionados
                        if (this.type === 'checkbox') {
                            const checkboxes = document.querySelectorAll(`[data-target="${targetId}"]`);
                            let anyChecked = false;
                            checkboxes.forEach(cb => {
                                if (cb.checked) anyChecked = true;
                            });
                            
                            if (!anyChecked) {
                                targetElement.classList.add('d-none');
                            }
                        } else {
                            // Para radios, verificar se o "Sim" está selecionado
                            const radios = document.querySelectorAll(`[name="${this.name}"]`);
                            let simSelected = false;
                            radios.forEach(radio => {
                                if (radio.value === 'Sim' && radio.checked) simSelected = true;
                            });
                            
                            if (!simSelected) {
                                targetElement.classList.add('d-none');
                            }
                        }
                    }
                }
            });
        });
        
        // Validação do formulário
        document.getElementById('formAluno').addEventListener('submit', function(e) {
            let valid = true;
            
            // Limpar erros anteriores
            document.querySelectorAll('.error-message').forEach(el => el.innerHTML = '');
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            
            // Validação de CPF
            const cpf = document.getElementById('cpf').value.replace(/\D/g, '');
            if (cpf.length !== 11) {
                showError('cpf', 'CPF inválido');
                valid = false;
            }
            
            // Validação de data de nascimento
            const nascimento = new Date(document.getElementById('nascimento').value);
            const hoje = new Date();
            if (nascimento >= hoje) {
                showError('nascimento', 'Data de nascimento inválida');
                valid = false;
            }
            
            // Validação de e-mail
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('email', 'E-mail inválido');
                valid = false;
            }
            
            // Validação de data de matrícula
            const dataMatricula = new Date(document.getElementById('dataMatricula').value);
            if (dataMatricula > hoje) {
                showError('dataMatricula', 'Data de matrícula não pode ser futura');
                valid = false;
            }
            
            if (!valid) {
                e.preventDefault();
                Swal.fire('Atenção', 'Por favor, corrija os erros no formulário.', 'warning');
            }
        });
        
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            field.classList.add('is-invalid');
            
            let errorDiv = field.nextElementSibling;
            if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                field.parentNode.insertBefore(errorDiv, field.nextSibling);
            }
            errorDiv.textContent = message;
        }
        
        // Definir data de matrícula como hoje por padrão
        const hoje = new Date().toISOString().split('T')[0];
        document.getElementById('dataMatricula').value = hoje;
    });
  </script>
</body>
</html>