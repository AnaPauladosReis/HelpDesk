<?php

function esc($v){
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function fmtDataHora($dt){
  if(!$dt) return '';
  $ts = strtotime((string)$dt);
  return $ts ? date('d/m/Y H:i', $ts) : '';
}

function normalizaData($v){
  $v = trim((string)$v);
  if ($v === '') return '';

  // YYYY-mm-dd
  if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return $v;

  // dd/mm/yyyy
  if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $v)) {
    [$d,$m,$y] = explode('/', $v);
    return "{$y}-{$m}-{$d}";
  }
  return '';
}

function imgToDataUri($absPath){
  if(!$absPath || !is_file($absPath)) return '';
  $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));

  $mime = 'image/png';
  if($ext === 'jpg' || $ext === 'jpeg') $mime = 'image/jpeg';
  elseif($ext === 'webp') $mime = 'image/webp';

  $bin = @file_get_contents($absPath);
  if($bin === false) return '';

  return 'data:' . $mime . ';base64,' . base64_encode($bin);
}
