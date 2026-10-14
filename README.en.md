# WISO EÜR Export for PrestaShop

[Deutsch](README.md) · [Download](https://github.com/LoQue90/prestashop-tsv-export/releases/latest)

Export invoices from **PrestaShop 9.1.x** as TSV files for **WISO EÜR & Kasse** using [Buchungsimport](https://www.buchungsimport.de/).

## Installation and updates

1. Download the **wisoexport-v1.2.0.zip** asset from [Releases](https://github.com/LoQue90/prestashop-tsv-export/releases/latest), not “Source code”.
2. Open **Modules → Module Manager → Upload a module** in PrestaShop and upload the ZIP.
3. When updating, do not uninstall first, so your settings are preserved.

## Exporting

Open **Orders → WISO EÜR Export** for all settings and exporting. **Configure** in the Module Manager opens the same page.

Choose an export range:

- **Date range:** Select start and end dates. Both days are fully included, using the invoice date.
- **Starting from an invoice (inclusive):** Search by invoice number or date and select a starting invoice. It and all subsequently created invoices are included, without an additional date limit. Ordering uses invoice date, then internal order when timestamps match. This also works across year changes when numbering restarts.

Review filters and booking details, then click **Export TSV**. Filters and booking details are saved for next time. Import the downloaded file into WISO using Buchungsimport.

## Settings

| Setting | Description |
| --- | --- |
| Use order status filter | Filter by current order status. When disabled, all statuses are included. |
| Order status for export | Required status, for example “Shipped”. |
| Exclude zero-amount invoices | Excludes exactly €0 gross totals; negative amounts remain included. Default: disabled. |
| Description | Booking text, for example “Sales”. |
| Revenue account | Revenue account number. |
| Bank account | Bank or clearing account. |
| Tax type | Small business, gross or net. Default: small business. |

Filters apply to both export modes. The starting invoice itself may also be excluded by a filter. If no invoices match, a message is displayed.

## Last export

The page shows creation date and time, date range or starting invoice, invoice count and the invoice number from the final file row.

After downloading, click **Refresh display**. Errors and empty results preserve the previous record. This records file creation, not import into WISO. Exports made before version 1.2.0 are not recorded retroactively.

## Export format

Tab-separated, without a header, UTF-8 without BOM.

| Position | Small business | Gross / Net |
| --- | --- | --- |
| 1 | Einnahme | Einnahme |
| 2 | Invoice date | Invoice date |
| 3 | Invoice number | Invoice number |
| 4 | Description | Description |
| 5 | Gross amount | Gross or net amount |
| 6 | Revenue account | Brutto or Netto |
| 7 | Bank account | Revenue account |
| 8 | — | Bank account |

**Small business:** seven columns. The revenue account follows the amount directly, without an extra or empty column.

```text
Einnahme	10.02.2025	RE000007	Sales	10,99 €	8195	1200
```

**Gross / Net:** eight columns. Your selection also determines which amount is exported. Import tokens remain German.

```text
Einnahme	10.02.2025	RE000007	Sales	119,00 €	Brutto	8195	1200
Einnahme	10.02.2025	RE000007	Sales	100,00 €	Netto	8195	1200
```

When upgrading to 1.2.0, adjust your small-business import mapping in Buchungsimport to the new seven-column format.

## Notes

- Intended for EUR invoices; no currency conversion.
- Separate credit slips and refund documents are not included. Negative invoice amounts are exported as negative income.
- Invoice numbers follow current shop settings. Later formatting changes may affect older invoices.
- Repeated exports may include the same invoices. The last export is informational; previously exported invoices are not automatically excluded.

## License

[GNU GPL-3.0-or-later](LICENSE)
