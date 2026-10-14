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
    private array $range;

    public function __construct(InvoiceNumberFormatter $formatter, string $description, string $account, string $bank, string $taxType, string $dateFrom, string $dateTo, array $range = [])
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
        $this->range = $range;
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
        ];
        // Preserve the old saved setting while omitting its column entirely.
        if ($this->taxType !== 'NULL') {
            $fields[] = $this->taxType;
        }
        $fields[] = $this->account;
        $fields[] = $this->bank;
        return implode("\t", array_map(static function ($value) {
            return str_replace(["\t", "\r", "\n"], ' ', (string) $value);
        }, $fields)) . "\r\n";
    }

    /**
     * Write the complete TSV before recording its summary or sending a download.
     * The last number comes from the actual final row, not a numeric maximum.
     */
    public function writeToStream($handle, array $invoices): array
    {
        if (!$invoices) {
            throw new \InvalidArgumentException('Keine Rechnungen zum Exportieren.');
        }
        $lastNumber = '';
        foreach ($invoices as $invoice) {
            $row = $this->formatRow($invoice);
            if (fwrite($handle, $row) !== strlen($row)) {
                throw new \RuntimeException('Exportdatei konnte nicht geschrieben werden.');
            }
            $lastNumber = explode("\t", $row)[2];
        }
        return array_merge($this->range, [
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'last_invoice_number' => $lastNumber,
            'count' => count($invoices),
        ]);
    }

    public function output(array $invoices, ?callable $onPrepared = null): void
    {
        // Prepare the complete file before sending headers, so errors cannot produce a partial download.
        $handle = fopen('php://temp', 'w+b');
        if ($handle === false) {
            throw new \RuntimeException('Exportdatei konnte nicht geöffnet werden.');
        }
        try {
            $summary = $this->writeToStream($handle, $invoices);
            rewind($handle);
            while (ob_get_level() > 0) {
                if (!ob_end_clean()) {
                    throw new \RuntimeException('Ausgabepuffer konnte nicht geleert werden.');
                }
            }
            if (headers_sent()) {
                throw new \RuntimeException('HTTP-Header wurden bereits gesendet.');
            }
            if ($onPrepared !== null) {
                $onPrepared($summary);
            }
            header('Content-Type: text/tab-separated-values; charset=UTF-8');
            $filename = $this->dateFrom . '_bis_' . $this->dateTo;
            if (($this->range['mode'] ?? 'dates') === 'invoice') {
                $filename = 'ab_Rechnung_' . substr(preg_replace('/[^a-zA-Z0-9_-]/', '_', $this->range['start_invoice_number']), 0, 80);
            }
            header('Content-Disposition: attachment; filename="WISO_EUR_' . $filename . '.tsv"');
            header('Cache-Control: no-store');
            fpassthru($handle);
        } finally {
            fclose($handle);
        }
        exit;
    }
}
