<?php

function pdfCssPadrao(){
return <<<CSS
@page { margin: 18px 18px 22px 18px; }
body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }

/* HEADER PADRÃO */
.header-wrap{
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 10px 12px;
  margin-bottom: 12px;
}
.header-top{
  width: 100%;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 8px;
  margin-bottom: 8px;
}
.brand{ width:100%; }
.brand td{ vertical-align: middle; }
.brand-logo{
  max-width: 140px;
  max-height: 70px;
  width: auto;
  height: auto;
  display: block;
}
.brand-name{
  font-size: 14px;
  font-weight: 700;
  margin: 0;
  color: #0f172a;
}
.brand-sub{
  font-size: 10px;
  color: #475569;
  margin-top: 2px;
  line-height: 1.2;
}
.report-title{ text-align:right; }
.report-title .t1{ font-size: 15px; font-weight: 800; margin: 0; }
.report-title .t2{ font-size: 10px; color: #475569; margin-top: 2px; }

.filters{ width: 100%; }
.filters td{
  font-size: 10.5px;
  color: #334155;
  padding: 2px 0;
}
.filters b{ color:#0f172a; }

.chip{
  display: inline-block;
  padding: 2px 8px;
  border-radius: 999px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  font-size: 10px;
  color: #0f172a;
  margin-left: 6px;
}

/* TABELA PADRÃO */
/* TABELA PADRÃO */
table{ width: 100%; border-collapse: collapse; }

/* ✅ só a tabela do relatório */
table.tbl{
  width: 100%;
  table-layout: fixed;     /* essencial */
}

/* ✅ faz o DomPDF respeitar o width que está no TH */
table.tbl thead th{
  table-layout: fixed;
}

/* ✅ garantir que os widths do TH sejam “válidos” pro DomPDF */
table.tbl th{
  overflow: hidden;
  white-space: nowrap;     /* evita quebrar e alterar cálculo */
}

/* ✅ nas TD pode quebrar (não estoura) */
table.tbl td{
  overflow: hidden;
  word-wrap: break-word;
  word-break: break-word;
}



img { max-width: 100%; }


thead th{
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  padding: 8px 6px;
  text-align: left;
  font-size: 10.5px;
}
tbody td{
  border: 1px solid #e2e8f0;
  padding: 4px 5px;   /* antes era 7px */
  vertical-align: middle;
  font-size: 10px;    /* menor */
}


.badge{
  display:inline-block;
  padding:2px 8px;
  border-radius:999px;
  font-size:10px;
  font-weight:700;
}
.b-success { background:#dcfce7; color:#166534; }
.b-warning { background:#fef3c7; color:#92400e; }
.b-primary { background:#dbeafe; color:#1e40af; }
.b-info    { background:#e0f2fe; color:#075985; }
.b-danger  { background:#fee2e2; color:#991b1b; }
.b-secondary{ background:#e2e8f0; color:#334155; }

.muted{ color:#64748b; }
.nowrap{ white-space:nowrap; }
.small{ font-size:10px; }

/* FOOTER */
.footer{
  position: fixed;
  bottom: -8px;
  left: 0;
  right: 0;
  font-size: 10px;
  color: #64748b;
  text-align: right;
}


/* =========================
   FIX IMAGENS / TABELA (DomPDF)
   ========================= */


/* garante que qualquer <img> não estoure o container */
img { max-width: 100%; }

/* foto pequena padrão (clientes etc.) */
.foto-box{
  width: 28px;
  height: 28px;
  border-radius: 8px;
  overflow: hidden;     /* ✅ obrigatório */
}

.foto-box img{
  width: 28px;
  height: 28px;
  object-fit: cover;    /* ✅ obrigatório */
  display: block;
}




.pagenum:before { content: counter(page); }
CSS;
}

function pdfHeaderPadrao($tituloRelatorio, array $info, $filtrosHtml){
  $logoHtml = $info['logoHtml'] ?? '';
  $empresaNome = $info['empresaNome'] ?? '';
  $linhaContato = $info['linhaContato'] ?? '';
  $empresaEndereco = $info['empresaEndereco'] ?? '';
  $geradoEm = $info['geradoEm'] ?? date('d/m/Y H:i');

  return <<<HTML
<div class="header-wrap">
  <div class="header-top">
    <table class="brand" cellspacing="0" cellpadding="0">
      <tr>
        <td style="width:150px;">{$logoHtml}</td>
        <td>
          <div class="brand-name">{$empresaNome}</div>
          <div class="brand-sub">
            {$linhaContato}<br>
            {$empresaEndereco}
          </div>
        </td>
        <td class="report-title" style="width:260px;">
          <div class="t1">{$tituloRelatorio}</div>
          <div class="t2">Gerado em: {$geradoEm}</div>
        </td>
      </tr>
    </table>
  </div>

  {$filtrosHtml}
</div>
HTML;
}

function pdfPaginaHtml($tituloRelatorio, array $info, $filtrosHtml, $conteudoHtml){
  $css = pdfCssPadrao();
  $header = pdfHeaderPadrao($tituloRelatorio, $info, $filtrosHtml);

  return <<<HTML
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <style>{$css}</style>
</head>
<body>

{$header}

{$conteudoHtml}

<div class="footer">
  Página <span class="pagenum"></span> de {{TOTAL_PAGINAS}}
</div>

</body>
</html>
HTML;
}


