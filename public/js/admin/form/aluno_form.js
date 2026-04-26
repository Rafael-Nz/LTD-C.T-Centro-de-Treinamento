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

      const endereco = document.getElementById('endereco')?.value.trim();
      if (!endereco) { showError('endereco', 'Endereço é obrigatório'); valid = false; }

      const numero = document.getElementById('numero')?.value.trim();
      if (!numero) { showError('numero', 'Número é obrigatório'); valid = false; }

      const cidade = document.getElementById('cidade')?.value.trim();
      if (!cidade) { showError('cidade', 'Cidade é obrigatória'); valid = false; }

      const bairro = document.getElementById('bairro')?.value.trim();
      if (!bairro) { showError('bairro', 'Bairro é obrigatório'); valid = false; }

      return valid;
    }


    function buildPayload() {
      // VALIDAR CAMPOS OBRIGATÓRIOS
      const senha = document.getElementById('senha')?.value || 
                    document.getElementById('cpf')?.value.replace(/\D/g, '');
      
      const cadastradoPor = document.getElementById('cadastrado_por')?.value || 1;

      return {
        // ✅ CAMPOS CORRETOS PARA API
        nome: document.getElementById('nome')?.value.trim(),
        sobrenome: document.getElementById('sobrenome')?.value.trim(),
        cpf: document.getElementById('cpf')?.value.replace(/\D/g, ''),
        data_nascimento: document.getElementById('nascimento')?.value, // ✅ Campo correto
        email: document.getElementById('email')?.value,
        senha: senha, // ✅ Obrigatório
        genero: document.getElementById('genero')?.value || 'O',
        cadastrado_por: parseInt(cadastradoPor), // ✅ Obrigatório
        data_matricula: document.getElementById('dataMatricula')?.value,
        
        // ✅ ENDEREÇO CORRETO
        endereco: {
          logradouro: document.getElementById('endereco')?.value,
          numero: document.getElementById('numero')?.value,
          cidade: document.getElementById('cidade')?.value,
          bairro: document.getElementById('bairro')?.value,
          cep: document.getElementById('cep')?.value.replace(/\D/g, ''),
          complemento: document.getElementById('complemento')?.value
        },
        
        // ✅ CONTATOS CORRETOS
        contatos: [
          {
            tipo: 'telefone',
            valor: document.getElementById('telefone1')?.value
          },
          ...(document.getElementById('telefone2')?.value ? [{
            tipo: 'telefone', 
            valor: document.getElementById('telefone2')?.value
          }] : []),
          ...(document.getElementById('email')?.value ? [{
            tipo: 'email_secundario',
            valor: document.getElementById('email')?.value
          }] : [])
        ].filter(c => c.valor) // Remove vazios
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
    const id = document.getElementById('formAluno')?.dataset.id || null;

    async function loadAlunoData() {
      if (!id) return;

      try {
        const response = await fetch(`/api/alunos/${id}`);
        const data = await response.json();

        if (data.success && data.data) {
          const aluno = data.data;
          
          // Informações Pessoais
          setValue('nome', aluno.nome);
          setValue('sobrenome', aluno.sobrenome);
          setValue('cpf', aluno.cpf);
          setValue('nascimento', aluno.data_nascimento);
          setValue('genero', aluno.genero);
          
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
          
          // ❌ REMOVER QUESTIONÁRIO POR ENQUANTO
          // console.log('Questionário não carregado - API não suporta ainda');
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
        const url = id 
          ? `/api/alunos/${id}` 
          : '/api/alunos';
        const method = id ? 'PUT' : 'POST';

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
              title: id ? 'Aluno atualizado!' : 'Aluno cadastrado!',
              text: data.data?.message || (id ? 'Aluno atualizado com sucesso!' : 'Aluno cadastrado com sucesso!'),
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
