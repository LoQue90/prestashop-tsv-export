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
class Validate { public static function isLoadedObject($object) { return $object->id > 0; } }
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
    check(count($fields) === 8 && $fields[4] === $expected && $fields[5] === $type, 'Eight TSV columns and correct amount');
    check(str_ends_with($line,"\r\n") && !str_starts_with($line,"\xef\xbb\xbf"), 'CRLF, no BOM');
    check($fields[2] === 'RE25-07' && $fields[3] === 'Sale Test ', 'Number and sanitizing');
    $negative=$invoice; $negative['total_paid_tax_incl']='-119'; $negative['total_paid_tax_excl']='-100';
    check(str_contains($exporter->formatRow($negative), '-'.$expected), 'Negative amount retained');
}
check(upgrade_module_1_1_0(null) && Configuration::get('WISOEXPORT_EXCLUDE_ZERO') === 0, 'Upgrade default');
Configuration::updateValue('WISOEXPORT_EXCLUDE_ZERO',1);
check(upgrade_module_1_1_0(null) && Configuration::get('WISOEXPORT_EXCLUDE_ZERO') === 1, 'Upgrade preserves choice');
echo "All export regression tests passed.\n";
