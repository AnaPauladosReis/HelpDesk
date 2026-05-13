


document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('formRecuperarSenha');
  if (!form) return;

  const btn = form.querySelector('button[type="submit"]');
  const inputEmail = document.getElementById('email_recuperar');
  const modalEl = document.getElementById('modalRecuperarSenha');

  // Ajuste aqui o nome do arquivo PHP que vai processar o envio:
  const endpoint = 'scripts/recuperar_senha_enviar.php';

  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    const email = (inputEmail?.value || '').trim();

    if (!email) {
      Mensagens.aviso('Atenção', 'Informe seu e-mail.');
      return;
    }

    // UI: loading
    const textoOriginal = btn ? btn.innerHTML : '';
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Enviando...';
    }

    try {
      const formData = new FormData(form);

      const resp = await fetch(endpoint, {
        method: 'POST',
        body: formData
      });

      // Se o PHP retornar erro 500/404 etc.
      if (!resp.ok) {
        throw new Error('Falha ao comunicar com o servidor.');
      }

      const data = await resp.json();

      // Esperado do PHP:
      // { ok: true, msg: "..." } ou { ok: false, msg: "..." }
      if (data.ok) {
        // fecha modal (opcional)
        if (modalEl && window.bootstrap) {
          const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
          modal.hide();
        }

        Mensagens.sucesso('Tudo certo!', data.msg || 'Se o e-mail existir, você receberá as instruções em instantes.');
        form.reset();
      } else {
        Mensagens.aviso('Atenção', data.msg || 'Não foi possível processar sua solicitação.');
      }

    } catch (err) {
      Mensagens.erro('Erro', err.message || 'Ocorreu um erro ao enviar. Tente novamente.');
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
      }
    }
  });
});

