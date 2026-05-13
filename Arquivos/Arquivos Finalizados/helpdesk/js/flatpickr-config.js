/**
 * Configuração do Flatpickr (datas) em português
 * Uso: incluir este script após Flatpickr; inputs com classe .flatpickr ou [data-flatpickr] serão inicializados
 */
document.addEventListener('DOMContentLoaded', function() {
    if (typeof flatpickr === 'undefined') return;

    // Locale pt (Brasil)
    flatpickr.localize(flatpickr.l10ns.pt);

    const defaultConfig = {
        locale: 'pt',
        dateFormat: 'd/m/Y',
        allowInput: true,
        disableMobile: false
    };

    // Inicializa todos os elementos com classe .flatpickr
    document.querySelectorAll('.flatpickr').forEach(el => {
        const opts = { ...defaultConfig };
        if (el.dataset.mode === 'datetime') {
            opts.enableTime = true;
            opts.time_24hr = true;
            opts.dateFormat = 'd/m/Y H:i';
        }
        if (el.dataset.mindate) opts.minDate = el.dataset.mindate;
        if (el.dataset.maxdate) opts.maxDate = el.dataset.maxdate;
        flatpickr(el, opts);
    });

    // Inicializa elementos com data-flatpickr (atributo vazio = opções padrão)
    document.querySelectorAll('[data-flatpickr]').forEach(el => {
        if (el._flatpickr) return;
        const opts = { ...defaultConfig };
        try {
            if (el.dataset.flatpickrOptions) Object.assign(opts, JSON.parse(el.dataset.flatpickrOptions));
        } catch (e) {}
        flatpickr(el, opts);
    });
});

/**
 * Inicializa Flatpickr em um elemento específico (para uso dinâmico)
 * @param {string|HTMLElement} selector
 * @param {Object} options
 */
function initFlatpickr(selector, options = {}) {
    if (typeof flatpickr === 'undefined') return null;
    const el = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!el) return null;
    const opts = { locale: 'pt', dateFormat: 'd/m/Y', allowInput: true, ...options };
    return flatpickr(el, opts);
}
