<?php

namespace WisoExport\Export;

class InvoiceProvider
{
    public function getInvoices(
        string $dateFrom,
        string $dateTo,
        int $statusId,
        bool $useStatusFilter
    ): array {

        $query = new \DbQuery();


        $query->select('
            oi.id_order_invoice,
            oi.number,
            oi.date_add,
            oi.total_paid_tax_incl
        ');


        $query->from(
            'order_invoice',
            'oi'
        );


        $query->innerJoin(
            'orders',
            'o',
            'o.id_order = oi.id_order'
        );


        $query->where(
            'oi.date_add >= "'
            . pSQL($dateFrom)
            . ' 00:00:00"'
        );


        $query->where(
            'oi.date_add <= "'
            . pSQL($dateTo)
            . ' 23:59:59"'
        );


        if ($useStatusFilter && $statusId > 0) {

            $query->where(
             'o.current_state='
             . (int)$statusId
            );

        }


        $query->orderBy(
            'oi.date_add ASC'
        );


        return \Db::getInstance()
            ->executeS($query);
    }
}