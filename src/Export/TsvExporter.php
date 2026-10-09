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

    public function __construct(InvoiceNumberFormatter $formatter, string $description, string $account, string $bank, string $taxType, string $dateFrom, string $dateTo)
    {
        if (!in_array($taxType, ['NULL', 'Brutto', 'Netto'], true)) {
            throw new \InvalidArgumentException('Ungültige Umsatzsteuerart.');
        }
        $this->formatter = $formatter;
        $this->description = $description;
        $this->account = $account;
        $this->bank = $bank;
        $this->taxType = $taxType;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function formatRow(array $invoice): string
    {
        $amount = $invoice[$this->taxType === 'Netto' ? 'total_paid_tax_excl' : 'total_paid_tax_incl'];
        $fields = [
            'Einnahme',
            date('d.m.Y', strtotime($invoice['date_add'])),
            $this->formatter->format($invoice),
            $this->description,
            number_format((float) $amount, 2, ',', '') . ' €',
            $this->taxType,
            $this->account,
            $this->bank,
        ];
        return implode("\t", array_map(static function ($value) {
            return str_replace(["\t", "\r", "\n"], ' ', (string) $value);
        }, $fields)) . "\r\n";
    }

    public function output(array $invoices): void
    {
        // Prepare the complete file before sending headers, so errors cannot produce a partial download.
        $handle = fopen('php://temp', 'w+b');
        if ($handle === false) {
            throw new \RuntimeException('Exportdatei konnte nicht geöffnet werden.');
        }
        try {
            foreach ($invoices as $invoice) {
                $row = $this->formatRow($invoice);
                if (fwrite($handle, $row) !== strlen($row)) {
                    throw new \RuntimeException('Exportdatei konnte nicht geschrieben werden.');
                }
            }
            rewind($handle);
            while (ob_get_level() > 0) {
                if (!ob_end_clean()) {
                    throw new \RuntimeException('Ausgabepuffer konnte nicht geleert werden.');
                }
            }
            if (headers_sent()) {
                throw new \RuntimeException('HTTP-Header wurden bereits gesendet.');
            }
            header('Content-Type: text/tab-separated-values; charset=UTF-8');
            header('Content-Disposition: attachment; filename="WISO_EUR_' . $this->dateFrom . '_bis_' . $this->dateTo . '.tsv"');
            header('Cache-Control: no-store');
            fpassthru($handle);
        } finally {
            fclose($handle);
        }
        exit;
    }
}
