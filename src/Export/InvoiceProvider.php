<?php

namespace WisoExport\Export;

class InvoiceProvider
{
    public function getInvoices(string $dateFrom, string $dateTo, int $statusId, bool $useStatusFilter, bool $excludeZero = false): array
    {
        $query = new \DbQuery();
        $query->select('oi.id_order_invoice, oi.number, oi.date_add, oi.total_paid_tax_incl, oi.total_paid_tax_excl, o.id_lang, o.id_shop');
        $query->from('order_invoice', 'oi');
        $query->innerJoin('orders', 'o', 'o.id_order = oi.id_order');
        $query->where('oi.number > 0');
        $query->where('oi.date_add >= "' . pSQL($dateFrom) . ' 00:00:00"');
        $query->where('oi.date_add <= "' . pSQL($dateTo) . ' 23:59:59"');
        $query->where('o.id_shop IN (' . implode(',', array_map('intval', \Shop::getContextListShopID())) . ')');
        if ($useStatusFilter && $statusId > 0) {
            $query->where('o.current_state = ' . $statusId);
        }
        if ($excludeZero) {
            $query->where('oi.total_paid_tax_incl <> 0');
        }
        $query->orderBy('oi.date_add ASC, oi.id_order_invoice ASC');
        $invoices = \Db::getInstance()->executeS($query);
        if ($invoices === false) {
            throw new \RuntimeException('Rechnungen konnten nicht geladen werden.');
        }
        return $invoices;
    }
}
