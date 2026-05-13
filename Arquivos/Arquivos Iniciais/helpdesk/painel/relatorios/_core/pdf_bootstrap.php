<?php
require_once __DIR__ . '/../../vendor/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function gerarPdf($html, $nomeArquivo = 'relatorio.pdf', $orientacao = 'portrait'){

  $options = new Options();
  $options->set('isRemoteEnabled', true);
  $options->set('defaultFont', 'DejaVu Sans');

  // 1º render: pega total de páginas
  $dompdf = new Dompdf($options);
  $dompdf->setPaper('A4', $orientacao);
  $dompdf->loadHtml($html, 'UTF-8');
  $dompdf->render();

  $total = $dompdf->getCanvas()->get_page_count();
  if(!$total || $total < 1) $total = 1;

  // injeta total e renderiza novamente
  $html2 = str_replace('{{TOTAL_PAGINAS}}', (string)$total, $html);

  $dompdf = new Dompdf($options);
  $dompdf->setPaper('A4', $orientacao);
  $dompdf->loadHtml($html2, 'UTF-8');
  $dompdf->render();

  $dompdf->stream($nomeArquivo, ["Attachment" => false]);
  exit;
}


