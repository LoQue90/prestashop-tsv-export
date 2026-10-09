<?php

require_once _PS_MODULE_DIR_ . 'wisoexport/src/Export/InvoiceProvider.php';
require_once _PS_MODULE_DIR_ . 'wisoexport/src/Export/TsvExporter.php';
require_once _PS_MODULE_DIR_ . 'wisoexport/src/Formatter/InvoiceNumberFormatter.php';


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



    public function initContent()
    {
        parent::initContent();


        if (Tools::isSubmit('submitWisoExport')) {

            $this->export();

        }


        $this->setTemplate(
            'wisoexport/export.tpl'
        );
    }




    private function export()
    {

        $dateFrom = Tools::getValue('date_from');

        $dateTo = Tools::getValue('date_to');



        $statusId = (int)Configuration::get(
            'WISOEXPORT_ORDER_STATE'
        );


        $useStatusFilter = (bool)Configuration::get(
            'WISOEXPORT_USE_STATUS_FILTER'
        );


        if (!$statusId) {

            $this->errors[] = $this->module->l(
                'Kein Bestellstatus ausgewählt.',
                'AdminWisoExportController'
            );

            return;

        }

        if (!$dateFrom || !$dateTo) {

            $this->errors[] = $this->module->l(
                'Bitte einen gültigen Zeitraum auswählen.',
                'AdminWisoExportController'
            );

            return; 

        }


        $provider = new InvoiceProvider();



        $invoices = $provider->getInvoices(
            $dateFrom,
            $dateTo,
            $statusId,
            $useStatusFilter
        );



        if (empty($invoices)) {

            $this->errors[] = sprintf(
                $this->module->l(
                    'Keine Rechnungen im Zeitraum %s bis %s mit dem ausgewählten Status gefunden.',
                    'AdminWisoExportController'
                ),
                Tools::displayDate($dateFrom),
                Tools::displayDate($dateTo)
            );


            return;

        }



        $formatter = new InvoiceNumberFormatter(
            (string)Configuration::get(
                'WISOEXPORT_PREFIX'
            ),
            (int)Configuration::get(
                'WISOEXPORT_NUMBER_LENGTH'
            )
        );


        $exporter = new TsvExporter(
            $formatter,

            Configuration::get(
                'WISOEXPORT_DESCRIPTION'
            ),

            Configuration::get(
                'WISOEXPORT_ACCOUNT'
            ),

            Configuration::get(
                'WISOEXPORT_BANK'
            ),

            Configuration::get(
                'WISOEXPORT_TAX_TYPE'
            ),

            $dateFrom,

            $dateTo
        );



        $exporter->output(
            $invoices
        );

    }

}