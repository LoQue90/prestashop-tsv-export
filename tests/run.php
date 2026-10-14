<?php
// Small regression suite; no shop database or third-party formatter is required.
define('_PS_VERSION_', '9.1.5');
define('_PS_MODULE_DIR_', dirname(__DIR__, 2) . '/');
function pSQL($value) { return addslashes($value); }
function check($condition, $message) {
    if (!$condition) { throw new RuntimeException($message); }
}
class Configuration {
    public static array $values = [];
    public static function get($key) { return self::$values[$key] ?? false; }
    public static function updateValue($key, $value) { self::$values[$key] = $value; return true; }
}
class Shop { public static function getContextListShopID() { return [1]; } }
class DbQuery {
    public array $conditions = [];
    public function select($s) {} public function from($a, $b) {} public function innerJoin($a, $b, $c) {}
    public function where($s) { $this->conditions[] = $s; } public function orderBy($s) {}
}
class Db {
    public static $result = [];
    public static $query;
    public static function getInstance() { return new self(); }
    public function executeS($q) { self::$query = $q; return self::$result; }
}
class OrderInvoice {
    public $id;
    public static array $calls = [];
    public static string $formatted = 'RE000007';
    public function __construct($id) { $this->id = $id; }
    public function getInvoiceNumberFormatted($lang, $shop) {
        self::$calls[] = [$this->id, $lang, $shop];
        return self::$formatted;
    }
}
class Validate { public static function isCleanHtml($value) { return is_string($value); } public static function isLoadedObject($object) { return $object->id > 0; } }
require __DIR__ . '/../src/Export/DateRange.php';
require __DIR__ . '/../src/Export/InvoiceProvider.php';
require __DIR__ . '/../src/Formatter/InvoiceNumberFormatter.php';
require __DIR__ . '/../src/Export/TsvExporter.php';
require __DIR__ . '/../upgrade/upgrade-1.1.0.php';
use WisoExport\Export\DateRange;
use WisoExport\Export\InvoiceProvider;
use WisoExport\Export\TsvExporter;
use WisoExport\Formatter\InvoiceNumberFormatter;
check(DateRange::isValid('2024-02-29', '2024-02-29'), 'Leap date / equal endpoints');
foreach ([['2025-02-29','2025-03-01'], ['2026-04-31','2026-05-01'], ['2026-02-01','2026-01-01'], ['', '2026-01-01'], [[], '2026-01-01'], ['2026-1-01','2026-02-01'], ['0000-01-01','2026-01-01']] as $range) {
    check(!DateRange::isValid(...$range), 'Invalid range accepted');
}
$provider = new InvoiceProvider();
$provider->getInvoices('2026-01-01','2026-12-31',0,false,false);
check(!in_array('o.current_state = 0', Db::$query->conditions), 'Disabled status filter');
check(!in_array('oi.total_paid_tax_incl <> 0', Db::$query->conditions), 'Zero filter defaults off');
$provider->getInvoices('2026-01-01','2026-12-31',4,true,true);
check(in_array('o.current_state = 4', Db::$query->conditions), 'Enabled status filter');
check(in_array('oi.total_paid_tax_incl <> 0', Db::$query->conditions), 'Exclude exactly zero, not negatives');
check(in_array('o.id_shop IN (1)', Db::$query->conditions), 'Shop scope');
Db::$result = false;
try { $provider->getInvoices('2026-01-01','2026-12-31',0,false); throw new LogicException('Expected DB failure'); }
catch (RuntimeException $e) { check(!($e instanceof LogicException), 'DB failure handling'); }
$invoice = ['id_order_invoice'=>7,'id_lang'=>2,'id_shop'=>1,'date_add'=>'2025-02-10 12:00:00','total_paid_tax_incl'=>'119.000000','total_paid_tax_excl'=>'100.000000'];
$formatter = new InvoiceNumberFormatter();
check($formatter->format($invoice) === 'RE000007', 'Core formatting without custom module');
check(OrderInvoice::$calls[0] === [7,2,1], 'Invoice identity, language and shop passed to core');
OrderInvoice::$formatted = 'RE25-07';
check($formatter->format($invoice) === 'RE25-07', 'Core hook result used unchanged');
foreach (['NULL'=>'119,00 €','Brutto'=>'119,00 €','Netto'=>'100,00 €'] as $type=>$expected) {
    $exporter = new TsvExporter($formatter,"Sale\tTest\n",'8195',"1200\r",$type,'2025-01-01','2025-12-31');
    $line=$exporter->formatRow($invoice);
    $fields=explode("\t",rtrim($line,"\r\n"));
    check($fields[4] === $expected, 'Correct gross or net amount');
    if ($type === 'NULL') {
        check(count($fields) === 7 && $fields[5] === '8195' && $fields[6] === '1200 ', 'Small business has seven columns with accounts immediately after amount');
        check(!in_array('NULL', $fields, true), 'No NULL token');
    } else {
        check(count($fields) === 8 && $fields[5] === $type && $fields[6] === '8195', 'Gross/net retains eight columns');
    }
    check(str_ends_with($line,"\r\n") && !str_starts_with($line,"\xef\xbb\xbf"), 'CRLF, no BOM');
    check($fields[2] === 'RE25-07' && $fields[3] === 'Sale Test ', 'Number and sanitizing');
    $negative=$invoice; $negative['total_paid_tax_incl']='-119'; $negative['total_paid_tax_excl']='-100';
    check(str_contains($exporter->formatRow($negative), '-'.$expected), 'Negative amount retained');
}
check(upgrade_module_1_1_0(null) && Configuration::get('WISOEXPORT_EXCLUDE_ZERO') === 0, 'Upgrade default');
Configuration::updateValue('WISOEXPORT_EXCLUDE_ZERO',1);
check(upgrade_module_1_1_0(null) && Configuration::get('WISOEXPORT_EXCLUDE_ZERO') === 1, 'Upgrade preserves choice');
echo "All export regression tests passed.\n";

$stream = fopen('php://temp', 'w+b');
$summary = $exporter->writeToStream($stream, [$invoice, $invoice]);
rewind($stream);
$rows = explode("\r\n", trim(stream_get_contents($stream)));
check($summary['count'] === 2, 'Summary counts exported invoices');
check($summary['last_invoice_number'] === explode("\t", $rows[1])[2], 'Summary matches last TSV row');
check($summary['date_from'] === '2025-01-01' && $summary['date_to'] === '2025-12-31', 'Summary contains exact period');
fclose($stream);
$stream = fopen('php://temp', 'w+b');
try {
    $exporter->writeToStream($stream, []);
    throw new RuntimeException('Empty export must not produce a summary');
} catch (InvalidArgumentException $expected) {}
fclose($stream);
$broken = $invoice;
$broken['id_order_invoice'] = 0;
$stream = fopen('php://temp', 'w+b');
$summaryProduced = false;
try {
    $exporter->writeToStream($stream, [$invoice, $broken]);
    $summaryProduced = true;
} catch (RuntimeException $expected) {}
check(!$summaryProduced, 'Failed export does not produce a new summary');
fclose($stream);
echo "Export summary tests passed.\n";

class Tools {
    public static array $values = [];
    public static function getValue($key, $default = false) { return self::$values[$key] ?? $default; }
    public static function isSubmit($key) { return isset(self::$values[$key]); }
    public static function getAdminTokenLite($controller) { return 'test-token'; }
}
class OrderState {
    public static function getOrderStates($lang) { return [['id_order_state'=>4,'name'=>'Versand']]; }
}
class TestSmarty {
    public array $values = [];
    public function assign($values) { $this->values = $values; }
}
class TestModule {
    public function l($value, $source = '') { return $value; }
}
class TestLink {
    public function getAdminLink($controller) { return 'index.php?controller='.$controller.'&token=test-token'; }
}
class ModuleAdminController {
    public $context;
    public $module;
    public $bootstrap;
    public array $errors = [];
    public bool $editable = true;
    public function __construct() {
        $this->context = (object) ['language'=>(object)['id'=>1],'smarty'=>new TestSmarty(),'link'=>new TestLink()];
        $this->module = new TestModule();
    }
    public function postProcess() {}
    public function initContent() {}
    public function setTemplate($template) {}
    public function access($permission) { return $this->editable; }
}
class PrestaShopLogger { public static function addLog($message, $severity) {} }
require __DIR__.'/../controllers/admin/AdminWisoExportController.php';
Configuration::updateValue('WISOEXPORT_LAST_EXPORT', json_encode([
    'created_at'=>'2026-10-14 23:00:00','date_from'=>'2026-10-01','date_to'=>'2026-10-14',
    'last_invoice_number'=>'RE0042','count'=>18,
]));
$previous = Configuration::get('WISOEXPORT_LAST_EXPORT');
Tools::$values = ['submitWisoExport'=>1,'token'=>'test-token','date_from'=>'2026-10-01','date_to'=>'2026-10-14',
    'WISOEXPORT_DESCRIPTION'=>'Sales','WISOEXPORT_ACCOUNT'=>'8195','WISOEXPORT_BANK'=>'1200','WISOEXPORT_TAX_TYPE'=>'NULL'];
Db::$result = [];
$c = new AdminWisoExportController();
$c->postProcess();
check(Configuration::get('WISOEXPORT_DESCRIPTION') === 'Sales', 'Export page saves booking details');
check((int) Configuration::get('WISOEXPORT_USE_STATUS_FILTER') === 0, 'Unchecked status filter is saved as disabled');
check((int) Configuration::get('WISOEXPORT_EXCLUDE_ZERO') === 0, 'Unchecked zero filter is saved as disabled');
check(Configuration::get('WISOEXPORT_LAST_EXPORT') === $previous, 'No matches preserve previous export record');
$c->initContent();
check($c->context->smarty->values['wiso_last']['last_invoice_number'] === 'RE0042', 'Last record displayed');
check($c->context->smarty->values['wiso_date_from'] === '2026-10-01', 'Submitted date retained on errors');
Tools::$values['WISOEXPORT_TAX_TYPE'] = 'invalid';
Tools::$values['WISOEXPORT_DESCRIPTION'] = 'Must not save';
$c = new AdminWisoExportController();
$c->postProcess();
check(Configuration::get('WISOEXPORT_DESCRIPTION') === 'Sales', 'Invalid settings are not saved');
Tools::$values['WISOEXPORT_TAX_TYPE'] = 'NULL';
Tools::$values['token'] = 'bad';
$c = new AdminWisoExportController();
$c->postProcess();
check(Configuration::get('WISOEXPORT_DESCRIPTION') === 'Sales', 'Invalid token prevents saving');
Tools::$values['token'] = 'test-token';
$c = new AdminWisoExportController();
$c->editable = false;
$c->postProcess();
check(Configuration::get('WISOEXPORT_DESCRIPTION') === 'Sales', 'Edit permission required');
Tools::$values = [];
Configuration::updateValue('WISOEXPORT_LAST_EXPORT', '');
$c = new AdminWisoExportController();
$c->initContent();
check($c->context->smarty->values['wiso_last'] === null, 'No previous export handled');
echo "Combined export page tests passed.\n";

Db::$result = [];
$provider->getInvoices('', '', 4, true, true, $invoice);
$conditions = implode(' ', Db::$query->conditions);
check(str_contains($conditions, 'oi.date_add > "2025-02-10 12:00:00"'), 'Invoice range follows chronological order');
check(str_contains($conditions, 'oi.id_order_invoice >= 7'), 'Selected invoice included with tie-break');
check(!str_contains($conditions, '23:59:59'), 'No upper date limit');
check(str_contains($conditions, 'o.current_state = 4') && str_contains($conditions, 'oi.total_paid_tax_incl <> 0'), 'Both filters apply to invoice mode');
$rangeExporter = new TsvExporter($formatter, 'Sales', '8195', '1200', 'NULL', '2025-02-10', '2026-10-14', ['mode'=>'invoice', 'start_invoice_number'=>'RE25-07']);
$stream = fopen('php://temp', 'w+b');
$summary = $rangeExporter->writeToStream($stream, [$invoice]);
check($summary['mode'] === 'invoice' && $summary['start_invoice_number'] === 'RE25-07', 'Start invoice saved in history');
fclose($stream);
Tools::$values = ['submitWisoExport'=>1,'token'=>'test-token','export_mode'=>'invoice','start_invoice_id'=>'0','WISOEXPORT_DESCRIPTION'=>'Must not save','WISOEXPORT_ACCOUNT'=>'8195','WISOEXPORT_BANK'=>'1200','WISOEXPORT_TAX_TYPE'=>'NULL'];
$c = new AdminWisoExportController();
$c->postProcess();
check(Configuration::get('WISOEXPORT_DESCRIPTION') === 'Sales', 'Invalid starting invoice does not save settings');
check(count($c->errors) === 1, 'Missing starting invoice rejected without requiring dates');
echo "Invoice range and column tests passed.\n";
