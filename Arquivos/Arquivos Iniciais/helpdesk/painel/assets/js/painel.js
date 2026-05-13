const app = document.querySelector('.app');

// toggle desktop
const btnToggle = document.getElementById('btnToggleSidebar');
if (btnToggle) {
  btnToggle.addEventListener('click', () => {
    app.classList.toggle('sidebar-collapsed');
    localStorage.setItem('sidebar-collapsed', app.classList.contains('sidebar-collapsed') ? '1' : '0');
  });

  if (localStorage.getItem('sidebar-collapsed') === '1') {
    app.classList.add('sidebar-collapsed');
  }
}

// toggle mobile
const btnMobile = document.getElementById('btnToggleSidebarMobile');
if (btnMobile) {
  btnMobile.addEventListener('click', () => app.classList.toggle('sidebar-open'));
}
document.addEventListener('click', (e) => {
  if (!app.classList.contains('sidebar-open')) return;
  if (e.target === app) app.classList.remove('sidebar-open');
});

// Chart exemplo no dashboard
const canvas = document.getElementById('chartChamados');
if (canvas && window.Chart) {
  new Chart(canvas, {
    type: 'line',
    data: {
      labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
      datasets: [{
        label: 'Chamados',
        data: [4, 7, 6, 8, 8, 10],
        tension: 0.35,
        fill: true
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });
}










document.addEventListener('DOMContentLoaded', () => {

  // Clique abre/fecha apenas o submenu clicado
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.sidebar-link-btn[data-submenu]');
    if (!btn) return;

    const group = btn.closest('.sidebar-group');
    if (!group) return;

    const isOpen = group.classList.toggle('open');
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });

});



//select2
function initSelect2Modal(modalId = "#modalForm") {
  if (!window.jQuery || !jQuery.fn || !jQuery.fn.select2) return;

  const $modal = jQuery(modalId);
  if (!$modal.length) return;

  // inicializa somente os selects que ainda não foram inicializados
  $modal.find("select.js-select2").each(function () {
    const $s = jQuery(this);
    if ($s.hasClass("select2-hidden-accessible")) return;

    $s.select2({
      theme: "bootstrap-5",
      width: "100%",
      dropdownParent: $modal,
      placeholder: $s.data("placeholder") || "",
      allowClear: String($s.data("allow-clear")) === "1"
    });
  });
}

// Quando abrir a modal, inicializa (garante posição correta)
document.addEventListener("shown.bs.modal", function (e) {
  if (!e.target) return;
  if (e.target.id === "modalForm") {
    initSelect2Modal("#modalForm");
  }
});
