<?php

require_once _PS_MODULE_DIR_ . 'wisoexport/src/Export/InvoiceProvider.php';
require_once _PS_MODULE_DIR_ . 'wisoexport/src/Export/TsvExporter.php';
require_once _PS_MODULE_DIR_ . 'wisoexport/src/Export/DateRange.php';
require_once _PS_MODULE_DIR_ . 'wisoexport/src/Formatter/InvoiceNumberFormatter.php';

use WisoExport\Export\DateRange;
use WisoExport\Export\InvoiceProvider;
use WisoExport\Export\TsvExporter;
use WisoExport\Formatter\InvoiceNumberFormatter;

class AdminWisoExportController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
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
        $configurationContent = $this->module->getExportConfigurationContent();
        $useStatusFilter = (bool) Configuration::get('WISOEXPORT_USE_STATUS_FILTER');
        $statusId = (int) Configuration::get('WISOEXPORT_ORDER_STATE');
        $statusName = '';
        if ($useStatusFilter) {
            foreach (OrderState::getOrderStates((int) $this->context->language->id) as $state) {
                if ((int) $state['id_order_state'] === $statusId) {
                    $statusName = (string) $state['name'];
                    break;
                }
            }
        }
        $lastExport = json_decode((string) Configuration::get('WISOEXPORT_LAST_EXPORT'), true);
        $dateFrom = Tools::getValue('date_from');
        $dateTo = Tools::getValue('date_to');
        $this->context->smarty->assign([
            'wiso_date_from' => is_string($dateFrom) ? $dateFrom : '',
            'wiso_date_to' => is_string($dateTo) ? $dateTo : date('Y-m-d'),
            'wiso_last_export' => is_array($lastExport) && isset($lastExport['at'], $lastExport['number']) ? $lastExport : null,
            'wiso_configuration_content' => $configurationContent,
            'wiso_use_status_filter' => $useStatusFilter,
            'wiso_status_name' => $statusName,
            'wiso_exclude_zero' => (bool) Configuration::get('WISOEXPORT_EXCLUDE_ZERO'),
        ]);
        $this->setTemplate('wisoexport/export.tpl');
    }

    private function export()
    {
        $dateFrom = Tools::getValue('date_from');
        $dateTo = Tools::getValue('date_to');
        if (!DateRange::isValid($dateFrom, $dateTo)) {
            $this->errors[] = $this->module->l('Bitte einen gültigen Zeitraum auswählen (Von darf nicht nach Bis liegen).', 'AdminWisoExportController');
            return;
        }
        $statusId = (int) Configuration::get('WISOEXPORT_ORDER_STATE');
        $useStatusFilter = (bool) Configuration::get('WISOEXPORT_USE_STATUS_FILTER');
        if ($useStatusFilter && $statusId <= 0) {
            $this->errors[] = $this->module->l('Kein Bestellstatus ausgewählt.', 'AdminWisoExportController');
            return;
        }
        try {
            $invoices = (new InvoiceProvider())->getInvoices(
                $dateFrom, $dateTo, $statusId, $useStatusFilter,
                (bool) Configuration::get('WISOEXPORT_EXCLUDE_ZERO')
            );
            if (!$invoices) {
                $this->errors[] = $this->module->l('Keine Rechnungen für den gewählten Zeitraum und die aktiven Filter gefunden.', 'AdminWisoExportController');
                return;
            }
            $exporter = new TsvExporter(
                new InvoiceNumberFormatter(),
                (string) Configuration::get('WISOEXPORT_DESCRIPTION'),
                (string) Configuration::get('WISOEXPORT_ACCOUNT'),
                (string) Configuration::get('WISOEXPORT_BANK'),
                (string) Configuration::get('WISOEXPORT_TAX_TYPE'),
                $dateFrom, $dateTo
            );
            $exporter->output($invoices, static function (string $lastNumber): void {
                $record = json_encode([
                    'at' => date('d.m.Y H:i:s'),
                    'number' => $lastNumber,
                ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                if (!Configuration::updateValue('WISOEXPORT_LAST_EXPORT', $record)) {
                    throw new \RuntimeException('Letzter Export konnte nicht gespeichert werden.');
                }
            });
        } catch (\Throwable $exception) {
            PrestaShopLogger::addLog('WISO export: ' . $exception->getMessage(), 3);
            $this->errors[] = $this->module->l('Der Export konnte nicht erstellt werden. Bitte die Shop-Protokolle prüfen.', 'AdminWisoExportController');
        }
    }
}
