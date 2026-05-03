<?php
// O router já processa os parâmetros corretamente
// Para /alunos/novo: $_GET['acao'] = 'novo'
// Para /alunos/editar/5: $_GET['acao'] = 'editar', $_GET['id'] = 5

// Captura os parâmetros
$acao = $_GET['acao'] ?? 'novo';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$isEdit = ($acao === 'editar' && $id);
$pageTitle = $isEdit ? 'Editar Aluno' : 'Cadastrar Aluno';
?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Cross C.T | <?= $pageTitle ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<link rel="stylesheet" href="/ctt/css/admin-styles.css">
	<link rel="stylesheet" href="/ctt/css/form.css">
	<link rel="stylesheet" href="/ctt/css/sidebar.css">
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
						<input type="hidden" name="cadastrado_por" id="cadastrado_por" value="1">
						
						<div class="row gy-4">
							<!-- Informações Pessoais -->
							<div class="col-12">
								<h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Informações Pessoais</h3>
								<div class="row g-3 mb-3">
									<div class="col-md-6">
										<label for="nome" class="form-label">Nome:</label>
										<input type="text" name="nome" id="nome" class="form-control" placeholder="Digite o nome" required>
									</div>
									
									<div class="col-md-6">
										<label for="sobrenome" class="form-label">Sobrenome:</label>
										<input type="text" name="sobrenome" id="sobrenome" class="form-control" placeholder="Digite o sobrenome" required>
									</div>
								</div>
								
								<div class="row g-3 mb-3">
									<div class="col-md-12">
										<label for="email" class="form-label">E-mail:</label>
										<input type="email" name="email" id="email" class="form-control" placeholder="email@exemplo.com" required>
									</div>
								</div>

								<div class="row g-3 mb-3">
									<div class="col-md-6">
										<label for="cpf" class="form-label">CPF:</label>
										<input type="text" name="cpf" id="cpf" class="form-control" placeholder="___.___.___-__" maxlength="14" required>
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
								</div>
							</div>

							<!-- Contatos -->
							<div class="col-12">
								<h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Contatos</h3>
								
								<div class="row g-3 mb-3">
									<div class="col-md-6">
										<label for="telefone1" class="form-label">Telefone (Celular):</label>
										<input type="tel" name="telefone1" id="telefone1" class="form-control" placeholder="(__) _____-____" maxlength="15" required>
									</div>
									
									<div class="col-md-6">
										<label for="telefone2" class="form-label">WhatsApp (Opcional):</label>
										<input type="tel" name="telefone2" id="telefone2" class="form-control" placeholder="(__) _____-____" maxlength="15">
									</div>
								</div>
								
								<div class="mb-3">
									<label for="email2" class="form-label">E-mail Secundário:</label>
									<input type="email" name="email2" id="email2" class="form-control" 
												 placeholder="email@exemplo.com">
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
											<button class="btn border border-start-0 border-left-0 rounded-end p-auto " style="min-height: 0; font-size: 1.05rem;" type="button" id="buscarCep" title="Buscar CEP">
												<i class="ph ph-magnifying-glass"></i>
											</button>
										</div>
									</div>
									<div class="col-md-6"></div>
								</div>
								
								<div class="mb-3">
									<label for="complemento" class="form-label">Complemento (Opcional):</label>
									<textarea name="complemento" id="complemento" class="form-control" rows="3" placeholder="Complemento do endereço (opcional)" style="resize: none;"></textarea>
								</div>
							</div>

							<!-- Seção: Informações Médicas (Anamnese) -->
							<div class="col-12">
								<h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Informações Médicas (Anamnese)</h3>
								<div class="mb-3">
											<label class="form-label fw-semibold">Algum médico já lhe diagnosticou com problema cardíaco??</label>
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
											<label class="form-label fw-semibold">Você tem dores no peito com frequência??</label>
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
								<!-- Tontura/Vertigem -->
								<div class="mb-3">
									<label class="form-label fw-semibold">Você desmaia com frequencia ou tem episódios de tontura/vertigem?</label>
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
								<!-- Problema Ósseo ou Articular -->
								<div class="mb-3">
									<label class="form-label fw-semibold">Algum médico já lhe diagnosticou com problemas ósseos ou articulares?</label>
									<div class="d-flex gap-3">
										<div class="form-check">
											<input class="form-check-input check-conditional" data-target="problemaOsseoObsDiv" type="radio" name="problemaOsseo" id="problemaOsseoSim" value="Sim">
											<label class="form-check-label" for="problemaOsseoSim">Sim</label>
										</div>
										<div class="form-check">
											<input class="form-check-input check-conditional" data-target="problemaOsseoObsDiv" type="radio" name="problemaOsseo" id="problemaOsseoNao" value="Não" checked>
											<label class="form-check-label" for="problemaOsseoNao">Não</label>
										</div>
									</div>
									<div class="d-none mt-2" id="problemaOsseoObsDiv">
										<label for="problemaOsseoObs" class="form-label">Quais problemas ósseos ou articulares?</label>
										<input type="text" class="form-control" name="problemaOsseoObs" id="problemaOsseoObs" placeholder="Ex: artrose no joelho, tendinite no ombro, osteoporose...">
									</div>
								</div>
								<!-- Algum outro motivo -->
								<div class="mb-3">
									<label class="form-label fw-semibold">Algum outro motivo que possa impedir a prática de exercícios?</label>
									<div class="d-flex gap-3">
										<div class="form-check">
											<input class="form-check-input check-conditional" data-target="outroProblemaObsDiv" type="radio" name="outroProblema" id="outroProblemaSim" value="Sim">
											<label class="form-check-label" for="outroProblemaSim">Sim</label>
										</div>
										<div class="form-check">
											<input class="form-check-input check-conditional" data-target="outroProblemaObsDiv" type="radio" name="outroProblema" id="outroProblemaNao" value="Não" checked>
											<label class="form-check-label" for="outroProblemaNao">Não</label>
										</div>
									</div>
									<div class="d-none mt-2" id="outroProblemaObsDiv">
										<label for="outroProblemaObs" class="form-label">Qual outro motivo?</label>
										<input type="text" class="form-control" name="outroProblemaObs" id="outroProblemaObs" placeholder="Informe o motivo...">
									</div>
								</div>
								<!-- Medicamentos -->
								<div class="mb-3">
									<label class="form-label fw-semibold">Está tomando alguma medicação atualmente?</label>
									<div class="d-flex gap-3">
										<div class="form-check">
											<input class="form-check-input check-conditional" data-target="grupoTipoMedicamento" type="radio" name="tomandoMedicamento" id="medicamentoSimRadio" value="Sim">
											<label class="form-check-label" for="medicamentoSimRadio">Sim</label>
										</div>
										<div class="form-check">
											<input class="form-check-input check-conditional" data-target="grupoTipoMedicamento" type="radio" name="tomandoMedicamento" id="medicamentoNaoRadio" value="Não" checked>
											<label class="form-check-label" for="medicamentoNaoRadio">Não</label>
										</div>
									</div>
									<div class="d-none mt-2" id="grupoTipoMedicamento">
										<label for="medicamentosTextarea" class="form-label">Informe quais medicamentos está tomando?</label>
										<textarea class="form-control" id="medicamentosTextarea" name="medicamentos" placeholder="" rows="2" style="resize: none;"></textarea>
									</div>
								</div>
								<!-- Cirurgia -->
								<div class="mb-3">
									<label class="form-label fw-semibold">Você já fez alguma cirurgia?</label>
									<div class="d-flex gap-3">
										<div class="form-check">
											<input class="form-check-input check-conditional" data-target="grupoTipoCirurgia" type="radio" name="fezCirurgia" id="cirurgiaSimRadio" value="Sim">
											<label class="form-check-label" for="cirurgiaSimRadio">Sim</label>
										</div>
										<div class="form-check">
											<input class="form-check-input check-conditional" data-target="grupoTipoCirurgia" type="radio" name="fezCirurgia" id="cirurgiaNaoRadio" value="Não" checked>
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
											<input class="form-check-input check-conditional" data-target="gravidaObsDiv" type="radio" name="gravida" id="gravidaSim" value="Sim">
											<label class="form-check-label" for="gravidaSim">Sim</label>
										</div>
										<div class="form-check">
											<input class="form-check-input check-conditional" data-target="gravidaObsDiv" type="radio" name="gravida" id="gravidaNao" value="Não" checked>
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
									<label class="form-label fw-semibold">Algum parente próximo (Pai, Mãe, Irmão ou Irmã) seu teve ataque cardíaco antes dos 50 anos?</label>
									<div class="d-flex gap-3">
										<div class="form-check">
											<input class="form-check-input check-conditional" data-target="grupoTipoProblema" type="radio" name="parenteProblema" id="parenteProblemaSim" value="Sim">
											<label class="form-check-label" for="parenteProblemaSim">Sim</label>
										</div>
										<div class="form-check">
											<input class="form-check-input check-conditional" data-target="grupoTipoProblema" type="radio" name="parenteProblema" id="parenteProblemaNao" value="Não" checked>
											<label class="form-check-label" for="parenteProblemaNao">Não</label>
										</div>
									</div>
								</div>
								<!-- Atividade Física -->
								<div class="mb-3">
									<label class="form-label fw-semibold">Você realiza atividades físicas regularmente?</label>
									<div class="d-flex gap-3">
										<div class="form-check">
											<input class="form-check-input check-conditional" data-target="grupoTipoAtividade" type="radio" name="atividadeFisica" id="atividadeFisicaSim" value="Sim">
											<label class="form-check-label" for="atividadeFisicaSim">Sim</label>
										</div>
										<div class="form-check">
											<input class="form-check-input check-conditional" data-target="grupoTipoAtividade" type="radio" name="atividadeFisica" id="atividadeFisicaNao" value="Não" checked>
											<label class="form-check-label" for="atividadeFisicaNao">Não</label>
										</div>
									</div>
									<div class="d-none mt-2" id="grupoTipoAtividade">
										<label for="tipoAtividadeInput" class="form-label">Qual tipo de atividade?</label>
										<input type="text" class="form-control" id="tipoAtividadeInput" name="tipoAtividade" placeholder="Ex: Caminhada, natação, futebol...">
									</div>
								</div>

                <!-- Sintomas -->
                <div class="mb-3">
                  <label class="form-label fw-semibold">Você tem algum dos sintomas abaixo?</label>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="Dor nas costas" id="sintomaDorCostas" name="sintomas[]">
                    <label class="form-check-label" for="sintomaDorCostas">Dor nas costas</label>
                  </div>
                  <div class="form-check">
										<input class="form-check-input" type="checkbox" value="Dor nas articulações, tendões ou músculo" id="sintomaDorArticular" name="sintomas[]">
                    <label class="form-check-label" for="sintomaDorArticular">Dor nas articulações, tendões ou músculo</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input check-conditional" data-target="doencaPulmonarDiv" type="checkbox" value="Doença pulmonar" id="sintomaDoencaPulmonar" name="sintomas[]">
                    <label class="form-check-label" for="sintomaDoencaPulmonar">Doença pulmonar</label>
                  </div>
                  <div class="d-none mt-2" id="doencaPulmonarDiv">
                    <label for="doencaPulmonarObs" class="form-label">Qual doença pulmonar?</label>
                    <input type="text" class="form-control" name="doencaPulmonarObs" id="doencaPulmonarObs" placeholder="Informe...">
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" data-target="nenhumSintoma" type="checkbox" value="Nenhum" id="nenhumSintoma" name="sintomas[]">
                    <label class="form-check-label" for="nenhumSintoma">Nenhum dos sintomas</label>
                  </div>
                </div>

								<!-- Objetivos -->
								<div class="mb-3">
									<label class="form-label fw-semibold">Quais seus objetivos ingressando em um grupo de promoção de sua saúde? (Pode selecionar mais de um)</label>
									<div class="form-check">
										<input class="form-check-input" type="checkbox" value="1" id="objetivoPerderPeso" name="objetivos[]">
										<label class="form-check-label" for="objetivoPerderPeso">Perder peso</label>
									</div>
									<div class="form-check">
										<input class="form-check-input" type="checkbox" value="2" id="objetivoGanharMassa" name="objetivos[]">
										<label class="form-check-label" for="objetivoGanharMassa">Ganhar massa muscular</label>
									</div>
									<div class="form-check">
										<input class="form-check-input" type="checkbox" value="3" id="objetivoCondicionamento" name="objetivos[]">
										<label class="form-check-label" for="objetivoCondicionamento">Melhorar condicionamento</label>
									</div>
									<div class="form-check">
										<input class="form-check-input" type="checkbox" value="4" id="objetivoCardiovascular" name="objetivos[]">
										<label class="form-check-label" for="objetivoCardiovascular">Melhorar preparo cardiovascular</label>
									</div>
									<div class="form-check">
										<input class="form-check-input" type="checkbox" value="5 " id="objetivoDefinicao" name="objetivos[]">
										<label class="form-check-label" for="objetivoDefinicao">Definição muscular/condicionamento</label>
									</div>
									<div class="form-check">
										<input class="form-check-input" type="checkbox" value="6" id="objetivoReabilitacao" name="objetivos[]">
										<label class="form-check-label" for="objetivoReabilitacao">Fins de reabilitação</label>
									</div>
									<div class="form-check">
										<input class="form-check-input" type="checkbox" value="7" id="objetivoReducaoEstresse" name="objetivos[]">
										<label class="form-check-label" for="objetivoReducaoEstresse">Redução de estresse</label>
									</div>
									<div class="form-check">
										<input class="form-check-input" type="checkbox" value="8" id="objetivoQualidadeVida" name="objetivos[]">
										<label class="form-check-label" for="objetivoQualidadeVida">Melhora na qualidade de vida</label>
									</div>
									<div class="form-check">
										<input class="form-check-input check-conditional" data-target="objetivoOutrosDiv" type="checkbox" value="Outros" id="objetivoOutrosCheck" name="objetivos[]">
										<label class="form-check-label" for="objetivoOutrosCheck">Outros</label>
									</div>
									<div class="d-none mt-2" id="objetivoOutrosDiv">
										<label for="objetivoOutros" class="form-label">Quais outros objetivos?</label>
										<input type="text" class="form-control" name="objetivoOutros" id="objetivoOutrosInput" placeholder="Informe os objetivos...">
									</div>
								</div>
									
									<!-- Observações -->
								<div class="mb-3">
									<label for="observacoesMedicas" class="form-label">Observações Médicas (Opcional):</label>
									<textarea name="observacoesMedicas" id="observacoesMedicas" class="form-control" rows="3" placeholder="Outras observações médicas relevantes." style="resize: none;"></textarea>
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
							<a href="/ctt/admin/alunos" class="btn btn-red">Voltar</a>
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

	<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	<script src="https://unpkg.com/imask"></script>
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
	<script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
	<script defer src="/ctt/js/admin/sidebar.js"></script>
	<script src="/ctt/js/admin/form/aluno_form.js"></script>
</body>
</html>