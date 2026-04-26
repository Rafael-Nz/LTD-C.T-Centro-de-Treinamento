document.addEventListener('DOMContentLoaded', function() {

    // Máscaras com fallback 
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

      return valid;
    }

    function buildPayload() {
      return {
        // Informações Pessoais
        nome: document.getElementById('nome')?.value.trim() || '',
        sobrenome: document.getElementById('sobrenome')?.value.trim() || '',
        cpf: document.getElementById('cpf')?.value || '',
        data_nascimento: document.getElementById('nascimento')?.value || '',
        genero: document.getElementById('genero')?.value || '',
        cargo_id: document.getElementById('cargo')?.value || '', // Adicionado cargo_id
        registro_professional: document.getElementById('registro_profissional')?.value || '',
        observacoes: document.getElementById('observacoes')?.value || '',

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
      };
    }

    // Função auxiliar para definir valor de campos
    function setValue(id, value) {
      const field = document.getElementById(id);
      if (field) {
        field.value = (value !== null && value !== undefined) ? value : '';
        // Se usar Select2, precisa disparar o evento change para atualizar o visual
        if (field.classList.contains('select2-hidden-accessible')) {
          $(field).trigger('change');
        }
      }
    }

    // Carregar dados para edição 
    const idFuncionario = document.getElementById('formFuncionario')?.dataset.id || null;

    async function loadFuncionarioData() {
      if (!idFuncionario) return;

      try {
        const response = await fetch(`/ctt/api/v1/funcionario/${idFuncionario}`);
        const data = await response.json();

        if (data.success && data.data) {
          const funcionario = data.data;

          // Informações Pessoais
          setValue('nome', funcionario.nome);
          setValue('sobrenome', funcionario.sobrenome);
          setValue('cpf', funcionario.cpf);
          setValue('nascimento', funcionario.data_nascimento);
          setValue('genero', funcionario.genero);
          setValue('cargo', funcionario.cargo_id);
          setValue('registro_profissional', funcionario.registro_profissional);
          setValue('observacoes', funcionario.observacoes);
          
          // Contatos
          if (funcionario.contatos && Array.isArray(funcionario.contatos)) {
            var telefoneCelular = funcionario.contatos.find(function(c) { return c.tipo_contato === 'telefone' && c.observacao === 'Celular'; });
            var telefoneFixo = funcionario.contatos.find(function(c) { return c.tipo_contato === 'telefone' && c.observacao === 'Fixo'; });
            var emailContato = funcionario.contatos.find(function(c) { return c.tipo_contato === 'email'; });
            
            setValue('telefone1', telefoneCelular ? telefoneCelular.valor : '');
            setValue('telefone2', telefoneFixo ? telefoneFixo.valor : '');
            setValue('email', emailContato ? emailContato.valor : '');
          }
          
          // Endereço
          setValue('endereco', funcionario.logradouro);
          setValue('numero', funcionario.numero);
          setValue('cidade', funcionario.cidade);
          setValue('bairro', funcionario.bairro);
          setValue('cep', funcionario.cep);
          setValue('complemento', funcionario.complemento);
          
          
        }
      } catch (error) {
        console.error('Erro ao carregar dados do funcionário:', error);
      }
    }

    async function loadCargos() {
        const cargoSelect = document.getElementById('cargo');
        if (!cargoSelect) return;

        try {
            // Busca os cargos ativos da sua API
            const response = await fetch('/ctt/api/v1/cargo');
            const result = await response.json();

            const listaCargos = result.data && result.data.data ? result.data.data : [];
            // O CargoController retorna um objeto com { data: [...] }
            if (Array.isArray(listaCargos)) {
                cargoSelect.innerHTML = '<option value="">Selecione</option>';
                
                listaCargos.forEach(cargo => {
                    const option = document.createElement('option');
                    option.value = cargo.id;
                    option.textContent = cargo.nome;
                    cargoSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Erro ao carregar cargos:', error);
        }
    }

    async function init() {
        applyMasks();
        
        // Primeiro carregamos as opções de cargos
        await loadCargos();
        
        // Depois, se for edição, carregamos os dados do funcionário
        if (idFuncionario) {
            await loadFuncionarioData();
        }

        // Se usar Select2, inicialize-o aqui para pegar os dados já carregados
        if ($.fn.select2) {
            $('#cargo').select2({
                theme: 'bootstrap-5',
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: 'Selecione um cargo',
                allowClear: true
            });
        }
    }

    init();

    // Submit via fetch
    const form = document.getElementById('formFuncionario');
    if (form) {
      form.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (!validate()) {
          Swal.fire('Atenção', 'Por favor, corrija os erros no formulário.', 'warning');
          return;
        }

        const payload = buildPayload();
        const url = idFuncionario ? `/ctt/api/v1/funcionario/${idFuncionario}` : '/ctt/api/v1/funcionario';
        const method = idFuncionario ? 'PUT' : 'POST';

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
              title: idFuncionario ? 'Funcionário atualizado!' : 'Funcionário cadastrado!',
              text: data.data?.message || (idFuncionario ? 'Funcionário atualizado com sucesso!' : 'Funcionário cadastrado com sucesso!'),
              confirmButtonText: 'OK',
            });
            window.location.href = 'funcionarios.php';
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

});