<?php

namespace WisoExport\Export;

use WisoExport\Formatter\InvoiceNumberFormatter;


class TsvExporter
{

    private InvoiceNumberFormatter $formatter;

    private string $description;
    private string $account;
    private string $bank;
    private string $taxType;

    private string $dateFrom;
    private string $dateTo;



    public function __construct(
        InvoiceNumberFormatter $formatter,
        string $description,
        string $account,
        string $bank,
        string $taxType,
        string $dateFrom,
        string $dateTo
    ) {

        $this->formatter = $formatter;

        $this->description = $description;
        $this->account = $account;
        $this->bank = $bank;
        $this->taxType = $taxType;

        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;

    }




    private function clean($value)
    {
        return str_replace(
            [
                "\t",
                "\r",
                "\n"
            ],
            ' ',
            $value
        );
    }




    public function output(
        array $invoices
    ): void {


        while (ob_get_level()) {
            ob_end_clean();
        }



        header(
            'Content-Type: text/tab-separated-values; charset=UTF-8'
        );


        header(
            'Content-Disposition: attachment; filename="WISO_EUR_' .
            $this->dateFrom .
            '_bis_' .
            $this->dateTo .
            '.tsv"'
        );


        header(
            'Cache-Control: no-store'
        );



        $handle = fopen(
            'php://output',
            'wb'
        );



        foreach ($invoices as $invoice) {

            $line = [

                'Einnahme',

                date(
                    'd.m.Y',
                    strtotime(
                        $invoice['date_add']
                    )
                ),


                $this->clean(
                    $this->formatter->format(
                        $invoice
                    )
                ),


                $this->clean(
                    $this->description
                ),


                number_format(
                    (float)$invoice['total_paid_tax_incl'],
                    2,
                    ',',
                    ''
                )
                . ' €',


                $this->clean(
                    $this->taxType
                ),

                $this->account,

                $this->bank

            ];



            fwrite(
                $handle,
                implode(
                    "\t",
                    $line
                )
                . "\r\n"
            );

        }



        fclose($handle);

        exit;

    }

}