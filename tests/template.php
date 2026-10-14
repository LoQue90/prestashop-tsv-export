<?php
require getenv('SMARTY_AUTOLOAD');
$smarty = new Smarty();
$smarty->setCompileDir(sys_get_temp_dir() . '/wiso-smarty-compiled');
$smarty->registerPlugin('function', 'l', static function ($params) { return htmlspecialchars($params['s'], ENT_QUOTES, 'UTF-8'); });
$smarty->registerPlugin('modifier', 'intval', 'intval');
$smarty->assign([
    'wiso_url'=>'index.php?controller=AdminWisoExport&token=abc', 'wiso_token'=>'abc',
    'wiso_settings'=>['WISOEXPORT_USE_STATUS_FILTER'=>1,'WISOEXPORT_ORDER_STATE'=>4,'WISOEXPORT_EXCLUDE_ZERO'=>1,
        'WISOEXPORT_DESCRIPTION'=>'Sales " <test>', 'WISOEXPORT_ACCOUNT'=>'8195','WISOEXPORT_BANK'=>'1200','WISOEXPORT_TAX_TYPE'=>'NULL'],
    'wiso_states'=>[['id_order_state'=>4,'name'=>'Versand']], 'wiso_start_id'=>7,
    'wiso_choices'=>[['id'=>7,'label'=>'RE000007 · 14.10.2026']],
    'wiso_date_from'=>'2026-10-01','wiso_date_to'=>'2026-10-14',
]);
$template = __DIR__.'/../views/templates/admin/wisoexport/export.tpl';
foreach (['dates','invoice'] as $mode) {
    $smarty->assign('wiso_mode', $mode);
    $smarty->assign('wiso_last', ['mode'=>$mode,'start_invoice_number'=>'RE000007','created_display'=>'14.10.2026 23:00:00',
        'from_display'=>'01.10.2026','to_display'=>'14.10.2026','last_invoice_number'=>'RE000042','count'=>18]);
    $html = $smarty->fetch($template);
    if (str_contains($html, 'Sales " <test>') || !str_contains($html, 'RE000042') || !str_contains($html, 'Kleinunternehmer')) {
        throw new RuntimeException('Template rendering/escaping failed');
    }
    $doc = new DOMDocument();
    @$doc->loadHTML($html);
    $xpath = new DOMXPath($doc);
    $date = $xpath->query('//*[@id="wiso-from"]')->item(0);
    $invoice = $xpath->query('//*[@id="wiso-start-invoice"]')->item(0);
    if ($date->hasAttribute('disabled') !== ($mode === 'invoice') || $invoice->hasAttribute('disabled') !== ($mode === 'dates')) {
        throw new RuntimeException('Inactive range must be disabled');
    }
}
$smarty->assign('wiso_last', null);
if (!str_contains($smarty->fetch($template), 'Noch kein Export erfasst.')) { throw new RuntimeException('Empty history missing'); }
echo "Template rendering tests passed.\n";
