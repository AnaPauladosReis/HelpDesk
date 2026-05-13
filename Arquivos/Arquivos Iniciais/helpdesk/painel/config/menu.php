<?php
return [
  // Ordem global do sidebar (pode misturar menu_id e grupo_id)
  'sidebar_order' => [
    'dashboard',
    'abertura',
    'cadastros',   // grupo
    'clientes',
    'usuarios',
    'relatorios',
    'chamados',
    'logs',
    
    
    
  ],

  'grupos' => [
    'cadastros' => [
      'titulo' => 'Cadastros',
      'icone'  => 'bi-journal-text',
      'ordem'  => 1,
    ],
  ],

  'menus' => [
    [
      'id'     => 'dashboard',
      'titulo' => 'Dashboard',
      'icone'  => 'bi-speedometer2',
      'url'    => './dashboard',
      'grupo'  => null,
      'ordem'  => 1,
    ],
    [
      'id'     => 'clientes',
      'titulo' => 'Clientes',
      'icone'  => 'bi-people',
      'url'    => './clientes',
      'grupo'  => null,
      'ordem'  => 2,
    ],
    [
      'id'     => 'cargos',
      'titulo' => 'Cargos',
      'icone'  => 'bi-tags',
      'url'    => './cargos',
      'grupo'  => 'cadastros',
      'ordem'  => 1,
    ],
    [
      'id'     => 'status_abertura',
      'titulo' => 'Status Abertura',
      'icone'  => 'bi-palette',
      'url'    => './status_abertura',
      'grupo'  => 'cadastros',
      'ordem'  => 2,
    ],
    [
      'id'     => 'setores',
      'titulo' => 'Setores',
      'icone' => 'bi-diagram-3',
      'url'    => './setores',
      'grupo'  => 'cadastros',
      'ordem'  => 3,
    ],
    [
      'id'     => 'usuarios',
      'titulo' => 'Usuários',
      'icone'  => 'bi-person-gear',
      'url'    => './usuarios',
      'grupo'  => null,
      'ordem'  => 3,
    ],
    [
      'id'     => 'chamados',
      'titulo' => 'Chamados',
      'icone'  => 'bi-ticket-perforated',
      'url'    => './chamados',
      'grupo'  => null,
      'ordem'  => 4,
    ],
    [
      'id'     => 'relatorios',
      'titulo' => 'Relatórios',
      'icone'  => 'bi-bar-chart',
      'url'    => './relatorios',
      'grupo'  => null,
      'ordem'  => 5,
    ],

    [
      'id'     => 'logs',
      'titulo' => 'Logs',
      'icone' => 'bi-journal-text',
      'url'    => './logs',
      'grupo'  => null,
      'ordem'  => 6,
    ],

    [
      'id'     => 'abertura',
      'titulo' => 'Abertura de Chamados',
      'icone' => 'bi-plus-circle',
      'url'    => './abertura',
      'grupo'  => null,
      'ordem'  => 7,
    ],

    

  ],
];
