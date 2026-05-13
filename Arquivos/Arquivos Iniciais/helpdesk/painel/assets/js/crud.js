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
  
    return {
      showModal: showModal,
      hideModal: hideModal,
      setModalTitle: setModalTitle,
      resetForm: resetForm,
      salvar: salvar,
      bindSubmit: bindSubmit,
      bindImagePreview: bindImagePreview
    };
  })();
  


  