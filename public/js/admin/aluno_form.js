document.addEventListener('DOMContentLoaded', function () {

  // ── Máscaras ────────────────────────────────────────────────────────────────
  IMask(document.getElementById('cpf'),       { mask: '000.000.000-00' });
  IMask(document.getElementById('telefone1'), { mask: '(00) 00000-0000' });
  IMask(document.getElementById('telefone2'), { mask: '(00) 0000-0000'  });
  IMask(document.getElementById('cep'),       { mask: '00000-000'       });

  // ── Data de matrícula padrão ─────────────────────────────────────────────────
  const hoje = new Date().toISOString().split('T')[0];
  document.getElementById('dataMatricula').value = hoje;
  document.getElementById('nascimento').setAttribute('max', hoje);

  // ── Busca de CEP ─────────────────────────────────────────────────────────────
  document.getElementById('buscarCep').addEventListener('click', function () {
    const cep = document.getElementById('cep').value.replace(/\D/g, '');
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
        if (data.logradouro) document.getElementById('endereco').value = data.logradouro;
        if (data.bairro) document.getElementById('bairro').value = data.bairro;
        if (data.localidade) document.getElementById('cidade').value = data.localidade;
        if (data.uf) document.getElementById('estado').value = data.uf;
        document.getElementById('numero').focus();
      })
      .catch(() => Swal.fire('Erro', 'Não foi possível buscar o CEP.', 'error'));
  });

  // ── Campos condicionais ───────────────────────────────────────────────────────
  function toggleConditionalField(triggerElement) {
    const targetId = triggerElement.getAttribute('data-target');
    if (!targetId) return;
    
    const target = document.getElementById(targetId);
    if (!target) return;

    if (triggerElement.type === 'radio') {
      const isSim = triggerElement.value === 'Sim' && triggerElement.checked;
      // Verifica se existe outro radio no mesmo grupo que está marcado
      const anySimSelected = [...document.querySelectorAll(`[name="${triggerElement.name}"]`)]
        .some(r => r.value === 'Sim' && r.checked);
      target.classList.toggle('d-none', !anySimSelected);
    }
    
    if (triggerElement.type === 'checkbox') {
      const anyChecked = [...document.querySelectorAll(`[data-target="${targetId}"]`)]
        .some(c => c.checked);
      target.classList.toggle('d-none', !anyChecked);
    }
  }

  document.querySelectorAll('.check-conditional').forEach(function (el) {
    el.addEventListener('change', function () {
      toggleConditionalField(this);
    });
    // Trigger initial state
    toggleConditionalField(el);
  });

  // ── Validação ───────────────────────────────────────────────────────────────────
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

    // Campos obrigatórios
    const requiredFields = ['nome', 'sobrenome', 'cpf', 'nascimento', 'genero', 
                            'telefone1', 'endereco', 'numero', 'cidade', 'bairro', 
                            'estado', 'cep', 'dataMatricula'];
    
    for (const fieldId of requiredFields) {
      const field = document.getElementById(fieldId);
      if (field && !field.value.trim()) {
        showError(fieldId, 'Campo obrigatório');
        valid = false;
      }
    }

    // CPF
    const cpf = document.getElementById('cpf').value.replace(/\D/g, '');
    if (cpf.length !== 11) {
      showError('cpf', 'CPF inválido');
      valid = false;
    }

    // Data de nascimento
    const nascimento = document.getElementById('nascimento').value;
    if (nascimento) {
      const nasc = new Date(nascimento);
      const hoje = new Date();
      if (isNaN(nasc) || nasc >= hoje) {
        showError('nascimento', 'Data de nascimento inválida');
        valid = false;
      }
    }

    // Email (opcional mas deve ser válido se preenchido)
    const email = document.getElementById('email').value;
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      showError('email', 'E-mail inválido');
      valid = false;
    }

    // Data de matrícula
    const dataMatricula = document.getElementById('dataMatricula').value;
    if (dataMatricula) {
      const mat = new Date(dataMatricula);
      const hoje = new Date();
      if (isNaN(mat) || mat > hoje) {
        showError('dataMatricula', 'Data de matrícula não pode ser futura');
        valid = false;
      }
    }

    return valid;
  }

  // ── Helper para pegar valor de radio ──────────────────────────────────────────
  function getRadioValue(name) {
    const el = document.querySelector(`[name="${name}"]:checked`);
    return el ? el.value : 'Não';
  }

  // ── Helper para pegar valor booleano do radio ─────────────────────────────────
  function getRadioBoolean(name) {
    const value = getRadioValue(name);
    return value === 'Sim' ? 1 : 0;
  }

  // ── Coleta do payload alinhado com o backend ───────────────────────────────────
  function buildPayload() {
    // Sintomas (checkbox)
    const sintomas = [...document.querySelectorAll('[name="sintomas[]"]:checked')]
      .map(c => c.value)
      .filter(v => v !== 'Nenhum');
    
    const temNenhumSintoma = [...document.querySelectorAll('[name="sintomas[]"]:checked')]
      .some(c => c.value === 'Nenhum');
    
    // Objetivos (checkbox)
    const objetivos = [...document.querySelectorAll('[name="objetivos[]"]:checked')]
      .map(c => c.value)
      .filter(v => v !== 'Outros');
    
    const temOutrosObjetivos = [...document.querySelectorAll('[name="objetivos[]"]:checked')]
      .some(c => c.value === 'Outros');

    // Mapeamento de status para ativo (backend espera 0 ou 1)
    const statusMap = {
      'ativo': 1,
      'inativo': 0,
      'suspenso': 0
    };
    const statusValue = document.getElementById('statusAluno').value;

    return {
      // Informações Pessoais
      nome:           document.getElementById('nome').value.trim(),
      sobrenome:      document.getElementById('sobrenome').value.trim(),
      cpf:            document.getElementById('cpf').value.replace(/\D/g, ''),
      nascimento:     document.getElementById('nascimento').value,
      genero:         document.getElementById('genero').value,
      ativo:          statusMap[statusValue] ?? 1,

      // Contatos
      telefone1:      document.getElementById('telefone1').value.replace(/\D/g, ''),
      telefone2:      document.getElementById('telefone2').value.replace(/\D/g, '') || null,
      email:          document.getElementById('email').value || null,

      // Endereço
      endereco:       document.getElementById('endereco').value.trim(),
      numero:         document.getElementById('numero').value.trim(),
      cidade:         document.getElementById('cidade').value.trim(),
      bairro:         document.getElementById('bairro').value.trim(),
      estado:         document.getElementById('estado').value,
      cep:            document.getElementById('cep').value.replace(/\D/g, ''),
      complemento:    document.getElementById('complemento').value.trim() || null,

      // Matrícula
      dataMatricula:  document.getElementById('dataMatricula').value,

      // Questionário (Anamnese) - nomes alinhados com extrairQuestionario() do backend
      problemaCardiaco:       getRadioBoolean('problemaCardiaco'),
      problemaCardiacoObs:    document.getElementById('problemaCardiacoObs')?.value.trim() || null,
      dorPeito:               getRadioBoolean('dorPeito'),
      desmaioTontura:         getRadioBoolean('desmaioTontura'),
      pressaoArterial:        getRadioBoolean('pressaoArterial'),
      
      sintomas: sintomas,
      doencaPulmonarObs:      document.getElementById('doencaPulmonarObs')?.value.trim() || null,
      nenhum_sintoma:         temNenhumSintoma ? 1 : 0,
      
      problemaOsseoArticular: getRadioBoolean('problemaOsseo'),
      problemaOsseoArticularObs: document.getElementById('problemaOsseoObs')?.value.trim() || null,
      
      limitacaoFisica:        getRadioBoolean('outroProblema'),
      limitacaoFisicaObs:     document.getElementById('outroProblemaObs')?.value.trim() || null,
      
      medicamento_continuo:   getRadioBoolean('tomandoMedicamento'),
      medicamento_descricao:  document.getElementById('medicamentosTextarea')?.value.trim() || null,
      
      cirurgia_anterior:      getRadioBoolean('fezCirurgia'),
      cirurgia_descricao:     document.getElementById('cirurgiaInput')?.value.trim() || null,
      cirurgia_data:          document.getElementById('dataCirurgiaInput')?.value || null,
      
      gravida:                getRadioBoolean('gravida'),
      gravida_tempo:          document.getElementById('gravidaObs')?.value.trim() || null,
      
      pratica_exercicios:     getRadioBoolean('atividadeFisica'),
      tipo_exercicios:        document.getElementById('tipoAtividadeInput')?.value.trim() || null,
      
      fumante:                getRadioBoolean('fumante'),
      consumo_alcool:         getRadioBoolean('consumoAlcool'),
      
      problema_saude_familia: getRadioBoolean('parenteProblema'),
      problema_saude_familia_descricao: document.getElementById('parenteProblemaObs')?.value.trim() || null,
      
      objetivos: objetivos.length > 0 ? objetivos.join(', ') : null,
      outros_objetivos: temOutrosObjetivos ? (document.getElementById('objetivoOutrosInput')?.value.trim() || null) : null,
      
      observacoes_medicas:    document.getElementById('observacoesMedicas').value.trim() || null
    };
  }

  // ── Submit via fetch ──────────────────────────────────────────────────────────
  const form = document.getElementById('formAluno');
  const idAluno = form.dataset.id || null;

  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    if (!validate()) {
      Swal.fire('Atenção', 'Por favor, corrija os erros no formulário.', 'warning');
      return;
    }

    const payload = buildPayload();
    const url = idAluno 
        ? `/ctt/api/v1/alunos/index.php?id=${idAluno}` 
        : `/ctt/api/v1/alunos/index.php`;
    const method = idAluno ? 'PUT' : 'POST';

    const btn = this.querySelector('[type="submit"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Salvando...';

    try {
      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      const data = await res.json();

      if (res.ok) {
        await Swal.fire({
          icon: 'success',
          title: idAluno ? 'Aluno atualizado!' : 'Aluno cadastrado!',
          text: data.message || (idAluno ? 'Aluno atualizado com sucesso!' : 'Aluno cadastrado com sucesso!'),
          confirmButtonText: 'OK',
        });
        window.location.href = 'alunos.php';
      } else {
        // Tratamento de erros específicos
        let errorMessage = data.message || 'Ocorreu um erro ao processar a solicitação.';
        
        if (res.status === 409) {
          errorMessage = data.message || 'CPF ou e-mail já cadastrado no sistema.';
        } else if (res.status === 422) {
          errorMessage = data.message || 'Dados inválidos. Verifique os campos e tente novamente.';
        } else if (res.status === 401) {
          errorMessage = 'Sessão expirada. Faça login novamente.';
          setTimeout(() => { window.location.href = 'login.php'; }, 2000);
        }
        
        Swal.fire('Erro', errorMessage, 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
      }
    } catch (err) {
      console.error('Erro na requisição:', err);
      Swal.fire('Erro', 'Falha na comunicação com o servidor. Verifique sua conexão.', 'error');
      btn.disabled = false;
      btn.innerHTML = originalText;
    }
  });

  // ── Carregar dados para edição ─────────────────────────────────────────────────
  async function carregarDadosParaEdicao() {
    if (!idAluno) return;

    try {
      const res = await fetch(`/ctt/api/v1/alunos/?id=${idAluno}`);
      const aluno = await res.json();

      if (!res.ok) {
        Swal.fire('Erro', 'Não foi possível carregar os dados do aluno.', 'error');
        return;
      }

      // Preencher campos pessoais
      document.getElementById('nome').value = aluno.nome || '';
      document.getElementById('sobrenome').value = aluno.sobrenome || '';
      document.getElementById('cpf').value = aluno.cpf || '';
      document.getElementById('nascimento').value = aluno.data_nascimento || '';
      document.getElementById('genero').value = aluno.genero || '';
      document.getElementById('statusAluno').value = aluno.ativo == 1 ? 'ativo' : 'inativo';
      
      // Contatos
      document.getElementById('telefone1').value = aluno.telefone1 || '';
      document.getElementById('telefone2').value = aluno.telefone2 || '';
      document.getElementById('email').value = aluno.email || '';
      
      // Endereço
      document.getElementById('endereco').value = aluno.logradouro || '';
      document.getElementById('numero').value = aluno.numero || '';
      document.getElementById('cidade').value = aluno.cidade || '';
      document.getElementById('bairro').value = aluno.bairro || '';
      document.getElementById('estado').value = aluno.estado || '';
      document.getElementById('cep').value = aluno.cep || '';
      document.getElementById('complemento').value = aluno.complemento || '';
      
      // Matrícula
      document.getElementById('dataMatricula').value = aluno.data_matricula || hoje;
      
      // Questionário
      if (aluno.questionario) {
        const q = aluno.questionario;
        
        // Função auxiliar para setar radio
        const setRadio = (name, value) => {
          const radio = document.querySelector(`[name="${name}"][value="${value === 1 ? 'Sim' : 'Não'}"]`);
          if (radio) radio.checked = true;
        };
        
        setRadio('problemaCardiaco', q.problema_cardiaco);
        if (q.problema_cardiaco_descricao) {
          document.getElementById('problemaCardiacoObs').value = q.problema_cardiaco_descricao;
        }
        
        setRadio('dorPeito', q.dor_peito);
        setRadio('desmaioTontura', q.desmaia_frequencia);
        setRadio('pressaoArterial', q.pressao_alta);
        setRadio('problemaOsseo', q.osseo_articular);
        if (q.osseo_articular_descricao) {
          document.getElementById('problemaOsseoObs').value = q.osseo_articular_descricao;
        }
        
        setRadio('outroProblema', q.limitacao_fisica);
        if (q.limitacao_descricao) {
          document.getElementById('outroProblemaObs').value = q.limitacao_descricao;
        }
        
        setRadio('tomandoMedicamento', q.medicamento_continuo);
        if (q.medicamento_descricao) {
          document.getElementById('medicamentosTextarea').value = q.medicamento_descricao;
        }
        
        setRadio('fezCirurgia', q.cirurgia_anterior);
        if (q.cirurgia_descricao) {
          document.getElementById('cirurgiaInput').value = q.cirurgia_descricao;
        }
        if (q.cirurgia_data) {
          document.getElementById('dataCirurgiaInput').value = q.cirurgia_data;
        }
        
        setRadio('gravida', q.gravida);
        if (q.gravida_tempo) {
          document.getElementById('gravidaObs').value = q.gravida_tempo;
        }
        
        setRadio('atividadeFisica', q.pratica_exercicios);
        if (q.tipo_exercicios) {
          document.getElementById('tipoAtividadeInput').value = q.tipo_exercicios;
        }
        
        setRadio('fumante', q.fumante);
        setRadio('consumoAlcool', q.consumo_alcool);
        setRadio('parenteProblema', q.problema_saude_familia);
        if (q.problema_saude_familia_descricao) {
          document.getElementById('parenteProblemaObs').value = q.problema_saude_familia_descricao;
        }
        
        if (q.observacoes_medicas) {
          document.getElementById('observacoesMedicas').value = q.observacoes_medicas;
        }
      }
      
      // Disparar eventos change para mostrar/ocultar campos condicionais
      document.querySelectorAll('.check-conditional').forEach(el => {
        el.dispatchEvent(new Event('change'));
      });
      
    } catch (err) {
      console.error('Erro ao carregar dados:', err);
    }
  }

  // Carregar dados se for edição
  if (idAluno) {
    carregarDadosParaEdicao();
  }
});