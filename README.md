# WISO EÜR Export für PrestaShop

[English](README.en.md) · [Download](https://github.com/LoQue90/prestashop-tsv-export/releases/latest)

Exportiere deine Rechnungen aus PrestaShop als TSV-Datei für den Import in **WISO EÜR & Kasse** über [Buchungsimport](https://www.buchungsimport.de/).

Für **PrestaShop 9.1.x**, einschließlich 9.1.5.

## Funktionen

- Rechnungen nach Zeitraum exportieren.
- Optional nach aktuellem Bestellstatus filtern.
- Rechnungen mit Betrag 0 auf Wunsch ausschließen; negative Beträge bleiben enthalten.
- Rechnungsnummern entsprechend der Einstellungen deines Shops übernehmen.
- Bezeichnung, Sachkonto, Geldkonto und Umsatzsteuerart festlegen.

## Installation und Update

1. Die Modul-ZIP **`wisoexport-v1.1.1.zip`** unter [Downloads](https://github.com/LoQue90/prestashop-tsv-export/releases/latest) herunterladen. Wähle die Datei unter **Assets**, nicht „Source code“.
2. In PrestaShop **Module → Modulmanager → Modul hochladen** öffnen.
3. Die ZIP hochladen und das Modul installieren oder aktualisieren.
4. Über **Konfigurieren** die Einstellungen öffnen.

Bei einem Update das Modul nicht vorher deinstallieren, damit deine Einstellungen erhalten bleiben.

## Einstellungen

| Einstellung | Beschreibung |
| --- | --- |
| Bezeichnung | Buchungstext für WISO, zum Beispiel „Warenverkauf“. |
| Sachkonto | Einnahmekonto für die Buchung. |
| Geldkonto | Bank- oder Verrechnungskonto. |
| Umsatzsteuerart | `NULL` für den Export ohne Umsatzsteuerausweis, `Brutto` oder `Netto`. Standard: `NULL`. |
| Bestellstatus verwenden | Schaltet den Filter nach dem aktuellen Bestellstatus ein oder aus. |
| Bestellstatus für Export | Gewünschter Status bei aktiviertem Filter, zum Beispiel „Versand“. |
| Rechnungen mit Betrag 0 ausschließen | Schließt Rechnungen mit exakt 0 € Bruttobetrag aus. Positive und negative Beträge bleiben enthalten. Standard: aus. |

Bei **Netto** wird der Nettorechnungsbetrag exportiert, bei **Brutto** und **NULL** der Bruttorechnungsbetrag. Wähle die Konten passend zu deiner Buchhaltung.

Die Exportseite zeigt den gewählten Bestellstatus sowie die Behandlung von Nullbeträgen und negativen Rechnungsbeträgen an.

## Rechnungen exportieren

1. **Bestellungen → WISO EÜR Export** öffnen.
2. Von- und Bis-Datum auswählen. Beide Tage sind eingeschlossen.
3. **TSV Export erstellen** anklicken.
4. Die heruntergeladene Datei mit Buchungsimport für WISO importieren.

Der Zeitraum bezieht sich auf das **Rechnungsdatum**. Wenn keine Rechnungen zu den gewählten Filtern passen, erscheint eine entsprechende Meldung.

Beispiel-Dateiname: `WISO_EUR_2025-01-01_bis_2025-12-31.tsv`.

## Exportformat

Die TSV-Datei enthält acht durch Tabulatoren getrennte Spalten, ohne Kopfzeile. Sie ist als UTF-8 ohne BOM gespeichert.

| Spalte | Inhalt |
| --- | --- |
| 1 | Buchungsart: `Einnahme` |
| 2 | Rechnungsdatum |
| 3 | Rechnungsnummer |
| 4 | Bezeichnung |
| 5 | Betrag in Euro |
| 6 | Umsatzsteuerart: `NULL`, `Brutto` oder `Netto` |
| 7 | Sachkonto |
| 8 | Geldkonto |

Beispiel mit einer generischen Rechnungsnummer:

```text
Einnahme	10.02.2025	RE000007	Warenverkauf	10,99 €	NULL	8195	1200
```

## Hinweise

- Der Export ist für Rechnungen in **Euro** vorgesehen. Eine Währungsumrechnung erfolgt nicht.
- Separate Gutschriften und Rückerstattungsbelege sind nicht enthalten. Negative Rechnungsbeträge werden als negative Einnahmen exportiert.
- Der Statusfilter berücksichtigt den **aktuellen** Bestellstatus.
- Das Rechnungsnummernformat richtet sich nach den aktuellen Shop-Einstellungen. Spätere Formatänderungen können auch die Darstellung älterer Rechnungen im Export beeinflussen.

## Lizenz

[GNU GPL-3.0-or-later](LICENSE)
