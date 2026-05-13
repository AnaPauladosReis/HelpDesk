// assets/js/funcoes.js
(function () {
    function onlyDigits(str) {
      return (str || "").replace(/\D/g, "");
    }


    async function gerarToken(id, scope = "default") {
      const v = parseInt(id, 10);
      if (!v) throw new Error("ID inválido.");
    
      const resp = await fetch("config/token.php", {   // ✅ AQUI
        method: "POST",
        headers: { "Content-Type": "application/json; charset=utf-8" },
        body: JSON.stringify({ id: v, scope }),
        cache: "no-store"
      });
    
      const json = await resp.json().catch(() => null);
      if (!resp.ok || !json || !json.ok) {
        throw new Error((json && json.msg) ? json.msg : "Falha ao gerar token.");
      }
    
      return json.token;
    }
    
  
    async function buscarCEP(cep, onSuccess, onError) {
      const clean = onlyDigits(cep);
      if (clean.length !== 8) {
        onError && onError("CEP inválido.");
        return;
      }
  
      try {
        const resp = await fetch(`https://viacep.com.br/ws/${clean}/json/`, {
          method: "GET",
        });
  
        if (!resp.ok) throw new Error("Falha ao consultar CEP.");
        const data = await resp.json();
  
        if (data.erro) {
          onError && onError("CEP não encontrado.");
          return;
        }
  
        onSuccess && onSuccess(data);
      } catch (err) {
        onError && onError(err.message || "Erro ao consultar CEP.");
      }
    }
  
    // Auto-bind: procura inputs com data-cep e preenche campos com data-cep-target
    function bindCEP(root = document) {
      const cepInput = root.querySelector("[data-cep]");
      if (!cepInput) return;
  
      const btn = root.querySelector("[data-cep-btn]");
  
      const targets = {
        endereco: root.querySelector("[data-cep-endereco]"),
        bairro: root.querySelector("[data-cep-bairro]"),
        cidade: root.querySelector("[data-cep-cidade]"),
        estado: root.querySelector("[data-cep-estado]"),
        complemento: root.querySelector("[data-cep-complemento]"),
      };
  
      const fill = (data) => {
        if (targets.endereco) targets.endereco.value = data.logradouro || "";
        if (targets.bairro) targets.bairro.value = data.bairro || "";
        if (targets.cidade) targets.cidade.value = data.localidade || "";
        
        if (targets.estado) {
          const uf = (data.uf || "").toUpperCase();
        
          // seta o valor no <select>
          targets.estado.value = uf;
        
          // se for select2, atualiza a UI
          if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
            jQuery(targets.estado).val(uf).trigger("change.select2");
          } else {
            targets.estado.dispatchEvent(new Event("change", { bubbles: true }));
          }
        }
        

        if (targets.complemento && !targets.complemento.value)
          targets.complemento.value = data.complemento || "";
  
        // força label do form-floating “subir” (se estiver usando placeholder)
        Object.values(targets).forEach((el) => {
          if (el) el.dispatchEvent(new Event("input", { bubbles: true }));
        });
      };
  
      const fail = (msg) => {
        // aqui você pode trocar por Swal/toast depois
        console.warn(msg);
      };
  
      const action = () => {
        buscarCEP(cepInput.value, fill, fail);
      };
  
      // ao sair do campo
      cepInput.addEventListener("blur", action);
  
      // botão buscar, se existir
      if (btn) btn.addEventListener("click", action);
    }
  
    window.Funcoes = { buscarCEP, bindCEP, gerarToken };

  
    document.addEventListener("DOMContentLoaded", () => bindCEP(document));
    document.addEventListener("shown.bs.modal", (e) => {
      if (e.target) bindCEP(e.target);
    });
  })();
  



//select2
  (function () {
    function toBool(v) {
      return v === true || v === "1" || v === 1 || v === "true";
    }
  
    // Inicializa select2 para selects dentro de um "scope" (documento, modal, container etc.)
    window.UI = window.UI || {};
  
    window.UI.initSelect2 = function (scope = document) {
      if (!window.jQuery || !jQuery.fn || !jQuery.fn.select2) return;
  
      const $scope = scope instanceof HTMLElement ? jQuery(scope) : jQuery(scope);
  
      $scope.find("select.js-select2").each(function () {
        const $el = jQuery(this);
  
        // evita duplicar
        if ($el.hasClass("select2-hidden-accessible")) return;
  
        const placeholder = $el.data("placeholder") || "Selecione uma opção";
        const parentSel = $el.data("dropdown-parent"); // ex: "#modalForm"
        const allowClear = toBool($el.data("allow-clear"));
  
        $el.select2({
          theme: "bootstrap-5",
          width: "100%",
          placeholder,
          allowClear,
          dropdownParent: parentSel ? jQuery(parentSel) : undefined
        });
      });
    };
  
    // Helper genérico para setar valor (funciona com select normal e com select2)
    window.UI.setValue = function (selectorOrEl, value) {
      const el = typeof selectorOrEl === "string"
        ? document.querySelector(selectorOrEl)
        : selectorOrEl;
  
      if (!el) return;
  
      el.value = value ?? "";
  
      // se for select2, dispara change pro UI atualizar
      if (window.jQuery && jQuery(el).hasClass("select2-hidden-accessible")) {
        jQuery(el).trigger("change");
      }
    };
  
    // Auto: quando QUALQUER modal Bootstrap abrir, inicializa select2 dentro dela
    document.addEventListener("DOMContentLoaded", function () {
      document.querySelectorAll(".modal").forEach((modal) => {
        modal.addEventListener("shown.bs.modal", function () {
          window.UI.initSelect2(modal);
        });
      });
  
      // também pode iniciar selects fora de modal (filtros etc.)
      window.UI.initSelect2(document);
    });
  })();
  


  