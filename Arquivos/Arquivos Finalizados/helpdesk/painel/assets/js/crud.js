/**
 * crud.js (minimal)
 * - Bootstrap 5 + SweetAlert2 (Mensagens.js)
 * - Padrão form: <form id="form">
 * - Resposta: { ok: true|false, msg: "..." }
 */
window.Crud = (function () {
    "use strict";
  
    function $(sel, root) {
      return (root || document).querySelector(sel);
    }
  
    function normalizeId(id) {
      return (id || "").replace(/^#/, "");
    }
  
    // ✅ pega Mensagens mesmo quando não está em window.Mensagens
    function getMsg() {
      try {
        if (typeof window !== "undefined" && window.Mensagens) return window.Mensagens;
        if (typeof Mensagens !== "undefined") return Mensagens; // const Mensagens
      } catch (e) {}
      return null;
    }
  
    // =========================
    // MODAL
    // =========================
    function showModal(modalId) {
      var id = normalizeId(modalId);
      var el = document.getElementById(id);
      if (!el) return;
      bootstrap.Modal.getOrCreateInstance(el).show();
    }
  
    function hideModal(modalId) {
      var id = normalizeId(modalId);
      var el = document.getElementById(id);
      if (!el) return;
      var inst = bootstrap.Modal.getInstance(el);
      if (inst) inst.hide();
    }
  
    function setModalTitle(titleIdOrSelector, text) {
      var el = document.getElementById(normalizeId(titleIdOrSelector)) || $(titleIdOrSelector);
      if (!el) return;
      el.textContent = text || "";
    }
  
    // =========================
    // FORM
    // =========================
    function resetForm(formSelector) {
      var form = typeof formSelector === "string" ? $(formSelector) : formSelector;
      if (!form) return;
      form.reset();
    }
  
    // =========================
    // FETCH JSON
    // =========================
    async function fetchJSON(url, options) {
      var resp = await fetch(url, options || {});
      var ct = resp.headers.get("content-type") || "";
  
      if (ct.indexOf("application/json") === -1) {
        var text = await resp.text();
        throw new Error("Resposta inesperada do servidor.\n" + text.slice(0, 300));
      }
  
      var data = await resp.json();
      if (!data.ok) throw new Error(data.msg || "Operação falhou.");
      return data;
    }
  
    async function postForm(url, formSelector) {
      var form = typeof formSelector === "string" ? $(formSelector) : formSelector;
      if (!form) throw new Error("Formulário não encontrado.");
  
      var fd = new FormData(form);
  
      return fetchJSON(url, {
        method: "POST",
        body: fd
      });
    }
  
    function isSessaoExpirada(msg) {
      msg = (msg || "").toLowerCase();
      return msg.indexOf("sess") !== -1 || msg.indexOf("login") !== -1;
    }
  
    // =========================
    // SALVAR
    // =========================
    async function salvar(opts) {
      opts = opts || {};
      var url = opts.url;
      var formSelector = opts.formSelector || "#form";
      var modalId = opts.modalId || null;
      var reloadOnSuccess = !!opts.reloadOnSuccess;
  
      if (!url) throw new Error("Crud.salvar: informe a URL.");
  
      var Msg = getMsg();
      if (Msg && Msg.carregando) Msg.carregando("Salvando...", "Aguarde");
  
      try {
        var data = await postForm(url, formSelector);
  
        if (Msg && Msg.fechar) Msg.fechar();
        if (Msg && Msg.salvoSemReload) Msg.salvoSemReload(data.msg || "Dados salvos!");
  
        if (modalId) hideModal(modalId);
  
        if (typeof opts.onSuccess === "function") {
          opts.onSuccess(data);
        } else if (reloadOnSuccess) {
          setTimeout(function () { location.reload(); }, 900);
        }
  
        return data;
  
      } catch (err) {
        var Msg2 = getMsg();
        if (Msg2 && Msg2.fechar) Msg2.fechar();
  
        var msg = (err && err.message) ? err.message : "Erro ao salvar.";
        if (isSessaoExpirada(msg)) {
          if (Msg2 && Msg2.sessaoExpirada) Msg2.sessaoExpirada("../index.php");
          return;
        }
  
        if (Msg2 && Msg2.erro) Msg2.erro("Erro ao salvar", msg);
        throw err;
      }
    }
  
    // =========================
    // BIND SUBMIT
    // =========================
    function bindSubmit(opts) {
      opts = opts || {};
      var formSelector = opts.formSelector || "#form";
      var url = opts.url;
  
      if (!url) throw new Error("Crud.bindSubmit: informe a URL.");
  
      var form = typeof formSelector === "string" ? $(formSelector) : formSelector;
      if (!form) throw new Error("Crud.bindSubmit: formulário não encontrado.");
  
      // evita bind duplicado
      if (form.dataset.crudBound === "1") return;
      form.dataset.crudBound = "1";
  
      form.addEventListener("submit", function (e) {
        e.preventDefault();
  
        salvar({
          url: url,
          formSelector: formSelector,
          modalId: opts.modalId || null,
          reloadOnSuccess: !!opts.reloadOnSuccess,
          onSuccess: opts.onSuccess || null
        }).catch(function () {
          // evita "Uncaught (in promise)" no console
        });
      });
    }
  
    // =========================
    // PREVIEW IMAGEM
    // =========================
    function bindImagePreview(opts) {
      opts = opts || {};
      var input = document.getElementById(opts.inputId);
      var preview = document.getElementById(opts.previewId);
      if (!input || !preview) return;
  
      var fallback = opts.fallbackSrc || "";
      var maxMB = opts.maxMB || 2;
  
      input.addEventListener("change", function () {
        var file = this.files && this.files[0];
        var Msg = getMsg();
  
        if (!file) {
          if (fallback) preview.src = fallback;
          return;
        }
  
        if (file.type.indexOf("image/") !== 0) {
          input.value = "";
          if (fallback) preview.src = fallback;
          if (Msg && Msg.campoInvalido) Msg.campoInvalido("Arquivo inválido", "Selecione uma imagem (JPG, PNG ou WEBP).");
          return;
        }
  
        var maxBytes = maxMB * 1024 * 1024;
        if (file.size > maxBytes) {
          input.value = "";
          if (fallback) preview.src = fallback;
          if (Msg && Msg.campoInvalido) Msg.campoInvalido("Imagem muito grande", "Envie uma imagem de até " + maxMB + "MB.");
          return;
        }
  
        var url = URL.createObjectURL(file);
        preview.src = url;
        preview.onload = function () { URL.revokeObjectURL(url); };
      });
    }



    // =========================
// SELEÇÃO EM MASSA (GENÉRICO)
// Requisitos no HTML:
// - checkbox geral: #checkAll
// - checkbox linha: .checkRow (value=id)
// - botão excluir: #btnExcluirSelecionados (com d-none)
// - contador: #qtdSelecionados
// =========================
function getSelectedIds(rowSelector) {
  rowSelector = rowSelector || ".checkRow";
  return Array.from(document.querySelectorAll(rowSelector + ":checked"))
    .map(el => (el && el.value ? String(el.value) : ""))
    .filter(v => v);
}

function toggleBulkDeleteButton(opts) {
  opts = opts || {};
  var btnId = opts.btnId || "btnExcluirSelecionados";
  var qtdId = opts.qtdId || "qtdSelecionados";
  var rowSelector = opts.rowSelector || ".checkRow";

  var btn = document.getElementById(btnId);
  var qtdEl = document.getElementById(qtdId);
  if (!btn || !qtdEl) return;

  var ids = getSelectedIds(rowSelector);
  var qtd = ids.length;

  qtdEl.textContent = String(qtd);

  if (qtd > 0) btn.classList.remove("d-none");
  else btn.classList.add("d-none");
}

function initSelecaoEmMassa(opts) {
  opts = opts || {};

  // padrões
  var checkAllId = opts.checkAllId || "checkAll";
  var rowSelector = opts.rowSelector || ".checkRow";
  var btnId = opts.btnId || "btnExcluirSelecionados";
  var qtdId = opts.qtdId || "qtdSelecionados";

  // evita bind duplicado
  var key = "bulkBound_" + checkAllId + "_" + rowSelector;
  if (document.body.dataset[key] === "1") return;
  document.body.dataset[key] = "1";

  document.addEventListener("change", function (e) {

    // marcar/desmarcar tudo
    if (e.target && e.target.id === checkAllId) {
      var marcar = !!e.target.checked;
      document.querySelectorAll(rowSelector).forEach(function (ch) {
        ch.checked = marcar;
      });
      toggleBulkDeleteButton({ btnId: btnId, qtdId: qtdId, rowSelector: rowSelector });
    }

    // marcar individual
    if (e.target && e.target.classList && e.target.classList.contains(rowSelector.replace(".", ""))) {
      var all = document.querySelectorAll(rowSelector);
      var checked = document.querySelectorAll(rowSelector + ":checked");

      var checkAll = document.getElementById(checkAllId);
      if (checkAll) checkAll.checked = (all.length > 0 && checked.length === all.length);

      toggleBulkDeleteButton({ btnId: btnId, qtdId: qtdId, rowSelector: rowSelector });
    }

  });

  // estado inicial
  toggleBulkDeleteButton({ btnId: btnId, qtdId: qtdId, rowSelector: rowSelector });
}

async function excluirSelecionados(opts) {
  opts = opts || {};
  var Msg = getMsg();

  var url = opts.url; // obrigatório
  if (!url) throw new Error("Crud.excluirSelecionados: informe a URL.");

  var titulo = opts.titulo || "Excluir selecionados?";
  var textoBase = opts.texto || "Essa ação não pode ser desfeita.";
  var confirmTxt = opts.confirmTxt || "Sim, excluir";
  var cancelTxt = opts.cancelTxt || "Cancelar";

  var rowSelector = opts.rowSelector || ".checkRow";
  var btnId = opts.btnId || "btnExcluirSelecionados";
  var qtdId = opts.qtdId || "qtdSelecionados";
  var checkAllId = opts.checkAllId || "checkAll";

  var ids = getSelectedIds(rowSelector);

  if (ids.length === 0) {
    toggleBulkDeleteButton({ btnId: btnId, qtdId: qtdId, rowSelector: rowSelector });
    return;
  }

  var texto = (typeof opts.textoFn === "function")
    ? opts.textoFn(ids)
    : ("Você vai excluir " + ids.length + " registro(s). " + textoBase);

  try {
    var res = await Msg.confirmar(titulo, texto, confirmTxt, cancelTxt);
    if (!res || !res.isConfirmed) return;

    Msg.carregando("Excluindo...", "Aguarde");

    // mesmo padrão do seu excluir (x-www-form-urlencoded)
    var body = "ids=" + encodeURIComponent(ids.join(","));

    var data = await fetchJSON(url, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
      body: body
    });

    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.salvoSemReload) Msg.salvoSemReload(data.msg || "Registros excluídos!");

    // pós sucesso
    if (typeof opts.onSuccess === "function") {
      await opts.onSuccess(data);
    }

    // reseta checkAll + botão
    var checkAll = document.getElementById(checkAllId);
    if (checkAll) checkAll.checked = false;

    toggleBulkDeleteButton({ btnId: btnId, qtdId: qtdId, rowSelector: rowSelector });

    return data;

  } catch (err) {
    var Msg2 = getMsg();
    if (Msg2 && Msg2.fechar) Msg2.fechar();

    var msg = (err && err.message) ? err.message : "Falha ao excluir.";
    if (Msg2 && Msg2.erro) Msg2.erro("Erro", msg);
    throw err;
  }
}




  
    return {
      showModal: showModal,
      hideModal: hideModal,
      setModalTitle: setModalTitle,
      resetForm: resetForm,
      salvar: salvar,
      bindSubmit: bindSubmit,
      bindImagePreview: bindImagePreview,
      initSelecaoEmMassa: initSelecaoEmMassa,
      excluirSelecionados: excluirSelecionados,
      toggleBulkDeleteButton: toggleBulkDeleteButton,
      getSelectedIds: getSelectedIds
    };
  })();
  


  