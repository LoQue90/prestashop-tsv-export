<?php

require_once dirname(__DIR__, 2) . '/src/Export/InvoiceProvider.php';
require_once dirname(__DIR__, 2) . '/src/Export/TsvExporter.php';
require_once dirname(__DIR__, 2) . '/src/Export/DateRange.php';
require_once dirname(__DIR__, 2) . '/src/Formatter/InvoiceNumberFormatter.php';

use WisoExport\Export\DateRange;
use WisoExport\Export\InvoiceProvider;
use WisoExport\Export\TsvExporter;
use WisoExport\Formatter\InvoiceNumberFormatter;

class AdminWisoExportController extends ModuleAdminController
{
    private const DEFAULTS = [
        'WISOEXPORT_DESCRIPTION' => '',
        'WISOEXPORT_ACCOUNT' => '',
        'WISOEXPORT_BANK' => '',
        'WISOEXPORT_TAX_TYPE' => 'NULL',
        'WISOEXPORT_ORDER_STATE' => 0,
        'WISOEXPORT_USE_STATUS_FILTER' => 1,
        'WISOEXPORT_EXCLUDE_ZERO' => 0,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    private function settings(): array
    {
        $values = [];
        foreach (self::DEFAULTS as $key => $default) {
            $value = Configuration::get($key);
            $values[$key] = $value === false ? $default : $value;
            if (Tools::isSubmit('submitWisoExport')) {
                $submitted = Tools::getValue($key, is_int($default) ? 0 : '');
                $values[$key] = is_scalar($submitted) ? (string) $submitted : '';
            }
        }
        return $values;
    }

    public function postProcess()
    {
        parent::postProcess();
        if (Tools::isSubmit('submitWisoExport')) {
            $this->export();
        }
    }

    public function initContent()
    {
        parent::initContent();
        $last = json_decode((string) Configuration::get('WISOEXPORT_LAST_EXPORT'), true);
        if (!is_array($last) || !isset($last['created_at'], $last['date_from'], $last['date_to'], $last['last_invoice_number'], $last['count'])) {
            $last = null;
        }
        if ($last) {
            $last['mode'] = $last['mode'] ?? 'dates';
            $last['start_invoice_number'] = $last['start_invoice_number'] ?? '';
            $last['created_display'] = date('d.m.Y H:i:s', strtotime($last['created_at']));
            $last['from_display'] = date('d.m.Y', strtotime($last['date_from']));
            $last['to_display'] = date('d.m.Y', strtotime($last['date_to']));
        }
        $from = Tools::getValue('date_from', date('Y-m-01'));
        $to = Tools::getValue('date_to', date('Y-m-d'));
        $choices = [];
        try {
            $formatter = new InvoiceNumberFormatter();
            foreach (array_reverse((new InvoiceProvider())->getInvoices('', '', 0, false)) as $row) {
                $choices[] = [
                    'id' => (int) $row['id_order_invoice'],
                    'label' => $formatter->format($row) . ' · ' . date('d.m.Y', strtotime($row['date_add'])),
                ];
            }
        } catch (\Throwable $exception) {
            PrestaShopLogger::addLog('WISO invoice selection: ' . $exception->getMessage(), 3);
            $this->errors[] = $this->module->l('Die Rechnungsauswahl konnte nicht geladen werden. Bitte den Export nach Zeitraum verwenden.', 'AdminWisoExportController');
        }
        $this->context->smarty->assign([
            'wiso_mode' => Tools::getValue('export_mode') === 'invoice' ? 'invoice' : 'dates',
            'wiso_start_id' => (int) Tools::getValue('start_invoice_id', 0),
            'wiso_choices' => $choices,
            'wiso_settings' => $this->settings(),
            'wiso_states' => OrderState::getOrderStates((int) $this->context->language->id),
            'wiso_date_from' => is_string($from) ? $from : '',
            'wiso_date_to' => is_string($to) ? $to : '',
            'wiso_last' => $last,
            'wiso_url' => $this->context->link->getAdminLink('AdminWisoExport'),
            'wiso_token' => Tools::getAdminTokenLite('AdminWisoExport'),
        ]);
        $this->setTemplate('wisoexport/export.tpl');
    }

    private function export()
    {
        $token = Tools::getValue('token');
        if (!$this->access('edit') || !is_string($token) || !hash_equals(Tools::getAdminTokenLite('AdminWisoExport'), $token)) {
            $this->errors[] = $this->module->l('Keine Berechtigung oder ungültige Sitzung. Bitte die Seite neu laden.', 'AdminWisoExportController');
            return;
        }
        $dateFrom = Tools::getValue('date_from');
        $dateTo = Tools::getValue('date_to');
        $mode = Tools::getValue('export_mode', 'dates');
        if (!in_array($mode, ['dates', 'invoice'], true)) {
            $this->errors[] = $this->module->l('Bitte einen Exportbereich auswählen.', 'AdminWisoExportController');
            return;
        }
        if ($mode === 'dates' && !DateRange::isValid($dateFrom, $dateTo)) {
            $this->errors[] = $this->module->l('Bitte einen gültigen Zeitraum auswählen (Von darf nicht nach Bis liegen).', 'AdminWisoExportController');
            return;
        }
        $settings = $this->settings();
        if (!in_array($settings['WISOEXPORT_TAX_TYPE'], ['NULL', 'Brutto', 'Netto'], true)) {
            $this->errors[] = $this->module->l('Ungültige Umsatzsteuerart.', 'AdminWisoExportController');
            return;
        }
        foreach (['WISOEXPORT_DESCRIPTION', 'WISOEXPORT_ACCOUNT', 'WISOEXPORT_BANK'] as $key) {
            if (!Validate::isCleanHtml($settings[$key]) || strlen($settings[$key]) > 255) {
                $this->errors[] = $this->module->l('Bitte gültige Buchungsdaten mit höchstens 255 Zeichen eingeben.', 'AdminWisoExportController');
                return;
            }
        }
        $statusId = (int) $settings['WISOEXPORT_ORDER_STATE'];
        $useStatusFilter = (bool) $settings['WISOEXPORT_USE_STATUS_FILTER'];
        $states = array_column(OrderState::getOrderStates((int) $this->context->language->id), 'id_order_state');
        if ($useStatusFilter && ($statusId <= 0 || !in_array($statusId, array_map('intval', $states), true))) {
            $this->errors[] = $this->module->l('Bitte einen Bestellstatus auswählen.', 'AdminWisoExportController');
            return;
        }
        try {
            $provider = new InvoiceProvider();
            $range = ['mode' => $mode];
            $start = null;
            if ($mode === 'invoice') {
                $startId = Tools::getValue('start_invoice_id');
                if (is_scalar($startId) && ctype_digit((string) $startId) && (int) $startId > 0) {
                    foreach ($provider->getInvoices('', '', 0, false) as $candidate) {
                        if ((int) $candidate['id_order_invoice'] === (int) $startId) {
                            $start = $candidate;
                            break;
                        }
                    }
                }
                if ($start === null) {
                    $this->errors[] = $this->module->l('Bitte eine vorhandene Startrechnung aus dem aktuellen Shop auswählen.', 'AdminWisoExportController');
                    return;
                }
                $range['start_invoice_number'] = (new InvoiceNumberFormatter())->format($start);
                $range['start_invoice_id'] = (int) $start['id_order_invoice'];
                $dateFrom = substr($start['date_add'], 0, 10);
                $dateTo = '';
            }
            foreach ($settings as $key => $value) {
                if (!Configuration::updateValue($key, $value)) {
                    throw new \RuntimeException('Einstellungen konnten nicht gespeichert werden.');
                }
            }
            $invoices = (new InvoiceProvider())->getInvoices(
                $dateFrom, $dateTo, $statusId, $useStatusFilter,
                (bool) $settings['WISOEXPORT_EXCLUDE_ZERO'], $start
            );
            if (!$invoices) {
                $this->errors[] = $this->module->l('Keine Rechnungen für den gewählten Zeitraum und die aktiven Filter gefunden.', 'AdminWisoExportController');
                return;
            }
            if ($mode === 'invoice') {
                $dateTo = substr($invoices[count($invoices) - 1]['date_add'], 0, 10);
            }
            $exporter = new TsvExporter(
                new InvoiceNumberFormatter(),
                $settings['WISOEXPORT_DESCRIPTION'],
                $settings['WISOEXPORT_ACCOUNT'],
                $settings['WISOEXPORT_BANK'],
                $settings['WISOEXPORT_TAX_TYPE'],
                $dateFrom, $dateTo, $range
            );
            $exporter->output($invoices, static function (array $summary): void {
                $summary['created_at'] = date('Y-m-d H:i:s');
                $json = json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                if (!Configuration::updateValue('WISOEXPORT_LAST_EXPORT', $json)) {
                    throw new \RuntimeException('Letzter Export konnte nicht gespeichert werden.');
                }
            });
        } catch (\Throwable $exception) {
            PrestaShopLogger::addLog('WISO export: ' . $exception->getMessage(), 3);
            $this->errors[] = $this->module->l('Der Export konnte nicht erstellt werden. Bitte die Shop-Protokolle prüfen.', 'AdminWisoExportController');
        }
    }
}
