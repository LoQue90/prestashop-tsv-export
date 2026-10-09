<?php

namespace WisoExport\Formatter;

class InvoiceNumberFormatter
{
    public function format(array $invoice): string
    {
        $orderInvoice = new \OrderInvoice((int) $invoice['id_order_invoice']);
        if (!\Validate::isLoadedObject($orderInvoice)) {
            throw new \RuntimeException('Rechnung konnte nicht geladen werden.');
        }

        // Uses the same core method and formatter hooks as the invoice PDF.
        return $orderInvoice->getInvoiceNumberFormatted(
            (int) $invoice['id_lang'],
            (int) $invoice['id_shop']
        );
    }
}
