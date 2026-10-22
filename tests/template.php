<?php
require getenv('SMARTY_AUTOLOAD');
$smarty = new Smarty();
$smarty->setCompileDir(sys_get_temp_dir());
$smarty->registerPlugin('function', 'l', static function ($p) { return $p['s']; });
$smarty->assign([
    'wiso_configuration_content' => '', 'wiso_last_export' => null,
    'wiso_use_status_filter' => false, 'wiso_status_name' => '', 'wiso_exclude_zero' => false,
    'wiso_date_from' => '', 'wiso_date_to' => '2026-10-22',
]);
$template = __DIR__ . '/../views/templates/admin/wisoexport/export.tpl';
$html = $smarty->fetch($template);
if (!preg_match('/name="date_from"\s+value=""/', $html) || !preg_match('/name="date_to"\s+value="2026-10-22"/', $html)) {
    throw new RuntimeException('Date values not rendered');
}
$smarty->assign('wiso_date_to', '"><script>alert(1)</script>');
if (str_contains($smarty->fetch($template), '<script>')) {
    throw new RuntimeException('Date value not escaped');
}
echo "Template rendering tests passed.\n";
