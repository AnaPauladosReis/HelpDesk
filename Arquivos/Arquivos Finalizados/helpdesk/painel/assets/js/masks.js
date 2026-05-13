// assets/js/masks.js
(function () {
    function onlyDigits(str) {
      return (str || "").replace(/\D/g, "");
    }
  
    function maskCPF(v) {
      v = onlyDigits(v).slice(0, 11);
      v = v.replace(/(\d{3})(\d)/, "$1.$2");
      v = v.replace(/(\d{3})(\d)/, "$1.$2");
      v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
      return v;
    }
  
    function maskCEP(v) {
      v = onlyDigits(v).slice(0, 8);
      v = v.replace(/(\d{5})(\d)/, "$1-$2");
      return v;
    }
  
    function maskDate(v) {
      v = onlyDigits(v).slice(0, 8);
      v = v.replace(/(\d{2})(\d)/, "$1/$2");
      v = v.replace(/(\d{2})(\d)/, "$1/$2");
      return v; // dd/mm/aaaa
    }
  
    function maskPhone(v) {
      v = onlyDigits(v).slice(0, 11);
      if (v.length <= 10) {
        // (00) 0000-0000
        v = v.replace(/(\d{2})(\d)/, "($1) $2");
        v = v.replace(/(\d{4})(\d)/, "$1-$2");
      } else {
        // (00) 00000-0000
        v = v.replace(/(\d{2})(\d)/, "($1) $2");
        v = v.replace(/(\d{5})(\d)/, "$1-$2");
      }
      return v;
    }
  
    function applyMask(input) {
      const type = input.dataset.mask;
  
      let val = input.value;
      if (type === "cpf") val = maskCPF(val);
      if (type === "cep") val = maskCEP(val);
      if (type === "data") val = maskDate(val);
      if (type === "tel") val = maskPhone(val);
  
      input.value = val;
    }
  
    function bindMasks(root = document) {
      root.querySelectorAll("input[data-mask]").forEach((input) => {
        // aplica ao carregar
        applyMask(input);
  
        // aplica a cada digitação
        input.addEventListener("input", () => applyMask(input));
  
        // evita letras
        input.addEventListener("keypress", (e) => {
          const ch = String.fromCharCode(e.which);
          if (!/[0-9]/.test(ch)) e.preventDefault();
        });
      });
    }
  
    // expõe para reuso (ex: quando abrir modal e inserir HTML dinamicamente)
    window.Masks = { bind: bindMasks };
  
    document.addEventListener("DOMContentLoaded", () => bindMasks(document));
  
    // Reaplica máscara quando uma modal abrir (caso os inputs sejam resetados)
    document.addEventListener("shown.bs.modal", (e) => {
      if (e.target) bindMasks(e.target);
    });
  })();
  



  // assets/js/masks.js
(function () {
  function onlyDigits(str) {
    return (str || "").replace(/\D/g, "");
  }

  function maskCPF(v) {
    v = onlyDigits(v).slice(0, 11);
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    return v;
  }

  function maskCNPJ(v) {
    v = onlyDigits(v).slice(0, 14);
    v = v.replace(/(\d{2})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d)/, "$1/$2");
    v = v.replace(/(\d{4})(\d{1,2})$/, "$1-$2");
    return v;
  }

  // ✅ CPF/CNPJ automático (11 = CPF, 14 = CNPJ)
  function maskCpfCnpj(v) {
    const d = onlyDigits(v);
    return d.length <= 11 ? maskCPF(d) : maskCNPJ(d);
  }

  function maskCEP(v) {
    v = onlyDigits(v).slice(0, 8);
    v = v.replace(/(\d{5})(\d)/, "$1-$2");
    return v;
  }

  function maskDate(v) {
    v = onlyDigits(v).slice(0, 8);
    v = v.replace(/(\d{2})(\d)/, "$1/$2");
    v = v.replace(/(\d{2})(\d)/, "$1/$2");
    return v;
  }

  function maskPhone(v) {
    v = onlyDigits(v).slice(0, 11);
    if (v.length <= 10) {
      v = v.replace(/(\d{2})(\d)/, "($1) $2");
      v = v.replace(/(\d{4})(\d)/, "$1-$2");
    } else {
      v = v.replace(/(\d{2})(\d)/, "($1) $2");
      v = v.replace(/(\d{5})(\d)/, "$1-$2");
    }
    return v;
  }

  function applyMask(input) {
    const type = input.dataset.mask;
    let val = input.value;

    if (type === "cpf") val = maskCPF(val);
    if (type === "cnpj") val = maskCNPJ(val);
    if (type === "cpfcnpj") val = maskCpfCnpj(val); // ✅ NOVO
    if (type === "cep") val = maskCEP(val);
    if (type === "data") val = maskDate(val);
    if (type === "tel") val = maskPhone(val);

    input.value = val;
  }

  function bindMasks(root = document) {
    root.querySelectorAll("input[data-mask]").forEach((input) => {
      applyMask(input);

      input.addEventListener("input", () => applyMask(input));

      input.addEventListener("keypress", (e) => {
        const ch = String.fromCharCode(e.which);
        if (!/[0-9]/.test(ch)) e.preventDefault();
      });
    });
  }

  window.Masks = { bind: bindMasks };

  document.addEventListener("DOMContentLoaded", () => bindMasks(document));

  document.addEventListener("shown.bs.modal", (e) => {
    if (e.target) bindMasks(e.target);
  });
})();
