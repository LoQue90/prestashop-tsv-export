# WISO EÜR Export for PrestaShop

[Deutsch](README.md) · [Download](https://github.com/LoQue90/prestashop-tsv-export/releases/latest)

Export your PrestaShop invoices as TSV files for import into **WISO EÜR & Kasse** using [Buchungsimport](https://www.buchungsimport.de/).

For **PrestaShop 9.1.x**, including 9.1.5.

## Features

- Export invoices for a selected date range.
- Optionally filter by the current order status.
- Exclude zero-amount invoices if needed; negative amounts remain included.
- Use invoice numbers formatted according to your shop settings.
- Configure the description, revenue account, bank account and tax type.

## Installation and updates

1. Download the module ZIP **`wisoexport-v1.1.1.zip`** from [Downloads](https://github.com/LoQue90/prestashop-tsv-export/releases/latest). Choose the file under **Assets**, not “Source code”.
2. In PrestaShop, open **Modules → Module Manager → Upload a module**.
3. Upload the ZIP and install or update the module.
4. Open **Configure** to review the settings.

When updating, do not uninstall the module first, so your settings are preserved.

## Settings

| Setting | Description |
| --- | --- |
| Description | Booking text for WISO, for example “Sales”. |
| Revenue account | Revenue account for the booking. |
| Bank account | Bank or clearing account. |
| Tax type | `NULL` for exports without VAT designation, `Brutto` (gross) or `Netto` (net). Default: `NULL`. |
| Use order status filter | Enables or disables filtering by the current order status. |
| Order status for export | The required status when filtering is enabled, for example “Shipped”. |
| Exclude invoices with a zero amount | Excludes invoices whose gross total is exactly €0. Positive and negative amounts remain included. Default: disabled. |

**Netto** exports the net invoice total; **Brutto** and **NULL** export the gross total. Choose account numbers that match your accounting setup.

The export page displays the selected order status and how zero and negative invoice amounts are handled.

## Exporting invoices

1. Open **Orders → WISO EÜR Export**.
2. Select the start and end dates. Both days are included.
3. Click **Create TSV export**.
4. Import the downloaded file into WISO using Buchungsimport.

The date range applies to the **invoice date**. If no invoices match your filters, the module displays a message.

Example filename: `WISO_EUR_2025-01-01_bis_2025-12-31.tsv`.

## Export format

The TSV file contains eight tab-separated columns without a header row. It uses UTF-8 encoding without a BOM.

| Column | Content |
| --- | --- |
| 1 | Booking type: `Einnahme` (income) |
| 2 | Invoice date |
| 3 | Invoice number |
| 4 | Description |
| 5 | Amount in euros |
| 6 | Tax type: `NULL`, `Brutto` or `Netto` |
| 7 | Revenue account |
| 8 | Bank account |

Example with a generic invoice number (import tokens remain German):

```text
Einnahme	10.02.2025	RE000007	Sales	10,99 €	NULL	8195	1200
```

## Notes

- The exporter is intended for invoices in **euros**. It does not convert currencies.
- Separate credit slips and refund documents are not included. Negative invoice amounts are exported as negative income.
- The status filter uses the **current** order status.
- Invoice number formatting follows the current shop settings. Later format changes may also affect how older invoice numbers appear in the export.

## License

[GNU GPL-3.0-or-later](LICENSE)
