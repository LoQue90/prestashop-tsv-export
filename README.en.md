# WISO EÜR Export for PrestaShop

[Deutsch](README.md) · [Releases](https://github.com/LoQue90/prestashop-tsv-export/releases) · [Changelog](CHANGELOG.md)

Export PrestaShop invoices as TSV files for **WISO EÜR & Kasse**, using [Buchungsimport](https://www.buchungsimport.de/).

## Features

- Export by invoice date with date range validation.
- Optional filtering by the current order status.
- Optional **“Exclude invoices with a zero amount”** setting; negative amounts remain included.
- Invoice numbers provided by PrestaShop's core formatting method, including optional formatting hooks.
- Configurable booking description, revenue account, bank account and tax type.
- `Netto` exports the stored net total; `Brutto` and `NULL` export the gross total.
- Eight TSV columns, UTF-8 without BOM or a header row, and CRLF line endings.

## Requirements

The declared compatibility range is **PrestaShop 9.1.x, including 9.1.5**. Use a PHP version supported by your PrestaShop installation. GitHub Actions runs syntax checks and regression tests on PHP 8.1–8.4. These tests do not include a complete PrestaShop installation with a database or a WISO import.

**No additional invoice number module is required.** This module works independently. If a formatter is registered with PrestaShop's standard hook, PrestaShop automatically applies it. There is no direct module dependency, no shared configuration access and no inclusion of another module's files.

## Installation and updates

1. Download the **`wisoexport-v1.1.0.zip`** asset from the [latest release](https://github.com/LoQue90/prestashop-tsv-export/releases/latest).
2. Open **Modules → Module Manager → Upload a module** in PrestaShop.
3. Upload the ZIP and install or update the module.
4. Open **Configure** and review your export settings.

To preserve settings, **do not uninstall** the module before updating it. The new zero-amount filter defaults to disabled, including during upgrades.

GitHub's automatic **Source code** archives are not ready-to-install module packages. The release ZIP contains a top-level `wisoexport/` directory with `wisoexport.php` directly inside it. A SHA-256 checksum is also provided.

## Settings

| Setting | Meaning |
| --- | --- |
| Exclude invoices with a zero amount | Excludes invoices whose stored gross total is exactly `0`. Positive and negative totals remain included. Default: disabled. |
| Description | Booking description used in WISO. |
| Revenue account | Revenue account used by the import. |
| Bank account | Bank or clearing account used by the import. |
| Use order status filter | Enables filtering by the current order status. |
| Order status for export | Status used when filtering is enabled. Installation searches for the German status “Versand”; otherwise select a status or disable filtering. |
| Tax type | `NULL`, `Brutto` or `Netto`. Default: `NULL`. |

The tax type controls the TSV field and selects the stored gross or net total. The module does not calculate new taxes. Account numbers and import mappings must match your accounting setup.

## Exporting

Open **Orders → WISO EÜR Export** and select a start and end date. Both days are fully included; the start must not follow the end. Filtering uses the **invoice date**, not the payment or shipping date, and respects the current shop context.

Example filename: `WISO_EUR_2025-01-01_bis_2025-12-31.tsv`.

If no invoices match the date range and enabled filters, the module displays a message instead of downloading an empty file.

### TSV format

| Column | Content |
| --- | --- |
| 1 | Booking type: `Einnahme` (income) |
| 2 | Invoice date: `DD.MM.YYYY` |
| 3 | Invoice number formatted by PrestaShop |
| 4 | Configured description |
| 5 | Amount with a decimal comma and `€`, including a minus sign where applicable |
| 6 | Tax type: `NULL`, `Brutto` or `Netto` |
| 7 | Revenue account |
| 8 | Bank account |

Generic example (tab-separated fields; import tokens remain German):

```text
Einnahme	10.02.2025	RE000007	Sales	10,99 €	NULL	8195	1200
```

### Invoice numbers

The exporter calls `OrderInvoice::getInvoiceNumberFormatted()` for each invoice using its order language and shop ID. Without another module, PrestaShop's standard format applies. When a formatting hook is active, its result is used. `RE` is an arbitrary example prefix.

Version 1.1.0 removes the export-specific prefix and number-length fields. Their previously stored values are no longer used. Formatting happens at export time using the current shop/hook configuration; a permanent historical formatted string is not stored separately. Changing the configuration can therefore change how older invoice numbers appear.

### Scope and limitations

- Numbered invoices from `order_invoice` are exported. Separate credit slips/refund documents from `order_slip` are not included.
- Negative invoice amounts are preserved as negative `Einnahme` entries. Check their mapping in your import profile.
- The zero filter tests the stored gross total for exact zero before formatting amounts to two decimals.
- The format uses euros. No currency conversion is performed; the exporter is intended for EUR invoices.
- Status filtering uses the current order status, not the status on the invoice date.

## Development and releases

Module files live directly at the repository root.

```sh
php tests/run.php
python3 scripts/build-release.py
```

The build produces `dist/wisoexport-v1.1.0.zip` and a SHA-256 checksum. Tests, Git data and GitHub workflows are excluded from the module package.

The **Test and release module** action checks pull requests and pushes. On `main`, after successful tests, it publishes a module version that does not yet have a release, including its ZIP and version tag. Further pushes of the same version do not overwrite the release. Version tags (`v1.1.0`) and manual runs on `main` are also supported. Tags must match both the PHP module version and `config.xml`.

For a new release, increase the version in `wisoexport.php` and `config.xml`, and update the changelog and `.github/release-notes.md`. Existing releases remain unchanged.

## License

[GNU GPL-3.0-or-later](LICENSE).
