<?php

namespace WisoExport\Formatter;

class InvoiceNumberFormatter
{

    private string $prefix;

    private int $length;



    public function __construct(
        string $prefix,
        int $length
    ) {

        $this->prefix = $prefix;

        $this->length = $length;

    }



    public function format(array $invoice): string
    {

        $year = date(
            'y',
            strtotime(
                $invoice['date_add']
            )
        );


        $number = str_pad(
            (string)$invoice['number'],
            $this->length,
            '0',
            STR_PAD_LEFT
        );


        return
            $this->prefix
            . $year
            . $number;

    }

}