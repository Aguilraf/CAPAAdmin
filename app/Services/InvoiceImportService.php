<?php
namespace App\Services;
use App\Models\Invoice;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
class InvoiceImportService {
  public function import(UploadedFile $file): array {
    $imp = $dup = 0;
    if (($h = fopen($file->getRealPath(), 'r')) !== false) {
      if (fread($h, 3) !== "\xEF\xBB\xBF") rewind($h);
      if (!($hr = fgetcsv($h, 1000, ','))) { fclose($h); return ['imported' => 0, 'duplicates' => 0]; }
      $hdrs = array_map('trim', $hr);
      $uuids = $this->fIdx($hdrs, ['folio fiscal', 'uuid', 'folio_fiscal']);
      $rfce = $this->fIdx($hdrs, ['rfc emisor', 'rfc_emisor', 'emisor', 'rfc emisor rel', 'rfc emisor relacionado']);
      $rfcr = $this->fIdx($hdrs, ['rfc receptor', 'rfc_receptor', 'receptor', 'rfc receptor rel', 'rfc receptor relacionado']);
      $typs = $this->fIdx($hdrs, ['tipo', 'tipo complemento', 'tipo_complemento']);
      $facts = $this->fIdx($hdrs, ['fact', 'factura', 'numero_factura', 'factura complemento', 'factura_complemento']);
      $fchs = $this->fIdx($hdrs, ['fecha emision', 'fecha_emision', 'fecha', 'fecha complemento', 'fecha_complemento']);
      $ttls = $this->fIdx($hdrs, ['total', 'total complemento', 'total_complemento', 'imp pagado', 'imp_pagado', 'importe pagado', 'importe_pagado']);
      $docs = $this->fIdx($hdrs, ['doc relac', 'docto relac', 'docto_relac', 'doc_relac', 'uuid relacionado', 'docto relacionado']);
      $sins = $this->fIdx($hdrs, ['s insoluto', 's_insoluto', 'saldo insoluto', 'saldo_insoluto']);
      $mets = $this->fIdx($hdrs, ['meto', 'metodo_pago', 'metodo de pago']);
      while (($r = fgetcsv($h, 1000, ',')) !== false) {
        if (empty($r) || count($r) < 3) continue;
        $uuid = $this->cln($r[$uuids[0] ?? 0] ?? null);
        if (!$uuid) continue;
        if (Invoice::where('uuid', $uuid)->exists()) { $dup++; continue; }
        $docVal = $this->cln($r[$docs[0] ?? 15] ?? null);
        $isCmp = !empty($docVal);
        if ($isCmp) {
          $eIdx = $rfce[1] ?? $rfce[0] ?? 1;
          $rIdx = $rfcr[1] ?? $rfcr[0] ?? 2;
          $tIdx = $typs[1] ?? $typs[0] ?? 18;
          $fIdx = $facts[1] ?? $facts[0] ?? 19;
          $dIdx = $fchs[1] ?? $fchs[0] ?? 20;
          $ttlIdx = $ttls[1] ?? $ttls[0] ?? 24;
          $sIdx = $sins[0] ?? 25;
          $tipo = $this->cln($r[$tIdx] ?? null, 'P');
          $numF = $this->cln($r[$fIdx] ?? null);
          $fStr = $this->cln($r[$dIdx] ?? null);
          $tot = $this->clnF($r[$ttlIdx] ?? null);
          $sIns = $this->clnF($r[$sIdx] ?? null);
          $status = 'Pagado';
        } else {
          $eIdx = $rfce[0] ?? 1;
          $rIdx = $rfcr[0] ?? 2;
          $tIdx = $typs[0] ?? 3;
          $fIdx = $facts[0] ?? 4;
          $dIdx = $fchs[0] ?? 5;
          $ttlIdx = $ttls[0] ?? 9;
          $mIdx = $mets[0] ?? 14;
          $tipo = $this->cln($r[$tIdx] ?? null, 'I');
          $numF = $this->cln($r[$fIdx] ?? null);
          $fStr = $this->cln($r[$dIdx] ?? null);
          $tot = $this->clnF($r[$ttlIdx] ?? null);
          $sIns = 0.00;
          $status = ($this->cln($r[$mIdx] ?? null) === 'PUE') ? 'Pagado' : 'PPD';
        }
        $em = $this->cln($r[$eIdx] ?? null);
        $re = $this->cln($r[$rIdx] ?? null);
        if (!$em || !$re) continue;
        Invoice::create([
          'uuid' => $uuid, 'rfc_emisor' => $em, 'rfc_receptor' => $re,
          'numero_factura' => $numF, 'fecha' => $this->parseD($fStr),
          'total' => $tot, 'imp_pagado' => $isCmp ? $tot : 0.00,
          'uuid_relacionado' => $docVal, 'saldo_insoluto' => $sIns,
          'tipo' => $tipo, 'status' => $status, 'is_used' => false
        ]);
        $imp++;
      }
      fclose($h);
    }
    return ['imported' => $imp, 'duplicates' => $dup];
  }
  private function fIdx(array $hdrs, array $cands): array {
    $idxs = [];
    foreach ($hdrs as $i => $h) {
      foreach ($cands as $c) {
        if ($this->norm($h) === $this->norm($c)) { $idxs[] = $i; break; }
      }
    }
    return $idxs;
  }
  private function norm(string $s): string {
    return str_replace(['á','é','í','ó','ú','ü'], ['a','e','i','o','u','u'], mb_strtolower(trim($s, " \t\n\r\0\x0B\"'")));
  }
  private function cln(?string $v, $def = null): ?string {
    if ($v === null) return $def;
    $t = trim($v); $u = strtoupper($t);
    return ($u === '#N/A' || $u === 'N/A' || $u === 'NULL' || $t === '') ? $def : $t;
  }
  private function clnF(?string $v, float $def = 0.00): float {
    $c = $this->cln($v);
    return $c === null ? $def : floatval(str_replace(',', '', $c));
  }
  private function parseD(?string $s): string {
    if (empty($s)) return now()->format('Y-m-d');
    $s = trim($s);
    foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'Y-m-d H:i:s', 'd/m/Y H:i:s', 'd-m-Y H:i:s'] as $f) {
      try {
        $d = Carbon::createFromFormat($f, $s);
        if ($d) return $d->format('Y-m-d');
      } catch (\Exception $e) {}
    }
    try { return Carbon::parse($s)->format('Y-m-d'); } catch (\Exception $e) { return now()->format('Y-m-d'); }
  }
}

