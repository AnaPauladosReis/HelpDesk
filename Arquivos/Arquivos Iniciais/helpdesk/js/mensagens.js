/**
 * Modelos de mensagens com SweetAlert2
 * Uso: Mensagens.loginDadosIncorretos(), Mensagens.usuarioInativo(), etc.
 */
const Mensagens = {
    /**
     * Login - dados incorretos (e-mail ou senha)
     */
    loginDadosIncorretos() {
        return Swal.fire({
            icon: 'error',
            title: 'Dados incorretos',
            text: 'E-mail ou senha inválidos. Verifique e tente novamente.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#667eea'
        });
    },

    /**
     * Login - usuário inativo
     */
    usuarioInativo() {
        return Swal.fire({
            icon: 'warning',
            title: 'Usuário inativo',
            text: 'Sua conta está inativa. Entre em contato com o administrador.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#667eea'
        });
    },

    /**
     * Sucesso genérico
     * @param {string} titulo
     * @param {string} texto
     */
    sucesso(titulo = 'Sucesso!', texto = 'Operação realizada com sucesso.') {
        return Swal.fire({
            icon: 'success',
            title: titulo,
            text: texto,
            confirmButtonText: 'OK',
            confirmButtonColor: '#28a745'
        });
    },

    /**
     * Erro genérico
     * @param {string} titulo
     * @param {string} texto
     */
    erro(titulo = 'Erro!', texto = 'Ocorreu um erro. Tente novamente.') {
        return Swal.fire({
            icon: 'error',
            title: titulo,
            text: texto,
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        });
    },

    /**
     * Aviso genérico
     * @param {string} titulo
     * @param {string} texto
     */
    aviso(titulo = 'Atenção', texto = '') {
        return Swal.fire({
            icon: 'warning',
            title: titulo,
            text: texto,
            confirmButtonText: 'OK',
            confirmButtonColor: '#ffc107'
        });
    },

    /**
     * Confirmação (ex: antes de excluir)
     * @param {string} titulo
     * @param {string} texto
     * @param {string} textoConfirmar
     * @param {string} textoCancelar
     */
    confirmar(
        titulo = 'Confirmar?',
        texto = 'Esta ação não pode ser desfeita.',
        textoConfirmar = 'Sim, continuar',
        textoCancelar = 'Cancelar'
    ) {
        return Swal.fire({
            icon: 'question',
            title: titulo,
            text: texto,
            showCancelButton: true,
            confirmButtonText: textoConfirmar,
            cancelButtonText: textoCancelar,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d'
        });
    },

    /**
     * Loader / Processando (ótimo para submits via AJAX)
     * @param {string} titulo
     * @param {string} texto
     */
    carregando(titulo = 'Processando...', texto = 'Aguarde um momento') {
        return Swal.fire({
            title: titulo,
            text: texto,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    },

    /**
     * Fecha qualquer mensagem aberta (ex: após concluir um loading)
     */
    fechar() {
        return Swal.close();
    },

    /**
     * Sucesso rápido (sem botão, fecha sozinho)
     * @param {string} texto
     * @param {number} tempo
     */
    salvoSemReload(texto = 'Dados atualizados com sucesso.', tempo = 1500) {
        return Swal.fire({
            icon: 'success',
            title: 'Atualizado!',
            text: texto,
            timer: tempo,
            showConfirmButton: false
        });
    },

    /**
     * Erro de validação de campo específico
     * @param {string} campo
     * @param {string} texto
     */
    campoInvalido(campo = 'Campo inválido', texto = '') {
        return Swal.fire({
            icon: 'error',
            title: campo,
            text: texto,
            confirmButtonText: 'Corrigir',
            confirmButtonColor: '#dc3545'
        });
    },

    /**
     * Sessão expirada (redireciona)
     * @param {string} url
     */
    sessaoExpirada(url = '../index.php') {
        return Swal.fire({
            icon: 'warning',
            title: 'Sessão expirada',
            text: 'Faça login novamente.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#667eea'
        }).then(() => {
            window.location.href = url;
        });
    },

    /**
     * Exibe mensagem de retorno do login conforme parâmetro/flash
     * Uso: Mensagens.exibirRetornoLogin()
     */
    exibirRetornoLogin() {
        if (!window.LOGIN_FLASH) return;

        const { codigo } = window.LOGIN_FLASH;

        if (codigo === 'login_invalido') {
            this.loginDadosIncorretos();
        }

        if (codigo === 'usuario_inativo') {
            this.usuarioInativo();
        }

        if (codigo === 'csrf_invalido') {
            this.erro(
                'Sessão expirada',
                'Sua sessão expirou. Recarregue a página e tente novamente.'
            );
        }
    }
};
