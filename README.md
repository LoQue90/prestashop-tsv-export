# WISO EÜR Export für PrestaShop

[English](README.en.md) · [Download](https://github.com/LoQue90/prestashop-tsv-export/releases/latest)

Exportiere Rechnungen aus **PrestaShop 9.1.x** als TSV-Datei für **WISO EÜR & Kasse** über [Buchungsimport](https://www.buchungsimport.de/).

## Installation und Update

1. Unter [Releases](https://github.com/LoQue90/prestashop-tsv-export/releases/latest) das Asset **wisoexport-v1.2.0.zip** herunterladen, nicht „Source code“.
2. In PrestaShop **Module → Modulmanager → Modul hochladen** öffnen und die ZIP hochladen.
3. Zum Aktualisieren nicht vorher deinstallieren, damit deine Einstellungen erhalten bleiben.

## Exportieren

Unter **Bestellungen → WISO EÜR Export** findest du alle Einstellungen und den Export. **Konfigurieren** im Modulmanager öffnet dieselbe Seite.

Wähle einen Exportbereich:

- **Zeitraum:** Von- und Bis-Datum auswählen. Beide Tage sind vollständig eingeschlossen; maßgeblich ist das Rechnungsdatum.
- **Ab Rechnung (einschließlich):** Nach Rechnungsnummer oder Datum suchen und eine Startrechnung auswählen. Diese und alle danach erstellten Rechnungen werden berücksichtigt, ohne zusätzliche Datumsgrenze. Die Reihenfolge richtet sich nach Rechnungsdatum und bei gleichem Zeitpunkt nach der internen Reihenfolge. Das funktioniert auch über Jahreswechsel mit zurückgesetzten Nummern hinweg.

Prüfe die Filter und Buchungsdaten und klicke auf **TSV exportieren**. Filter und Buchungsdaten werden für den nächsten Export gespeichert. Die heruntergeladene Datei kannst du über Buchungsimport in WISO importieren.

## Einstellungen

| Einstellung | Beschreibung |
| --- | --- |
| Bestellstatus verwenden | Aktiviert die Auswahl nach aktuellem Bestellstatus. Ohne Filter werden alle Status berücksichtigt. |
| Bestellstatus für Export | Gewünschter Status, zum Beispiel „Versand“. |
| Rechnungen mit Betrag 0 ausschließen | Entfernt exakt 0 € Bruttobetrag; negative Beträge bleiben enthalten. Standard: aus. |
| Bezeichnung | Buchungstext, zum Beispiel „Warenverkauf“. |
| Sachkonto | Einnahmekonto. |
| Geldkonto | Bank- oder Verrechnungskonto. |
| Umsatzsteuerart | Kleinunternehmer, Brutto oder Netto. Standard: Kleinunternehmer. |

Die Filter gelten für beide Exportarten. Auch die ausgewählte Startrechnung kann durch einen Filter ausgeschlossen werden. Ohne Treffer erscheint eine Meldung.

## Letzter Export

Die Exportseite zeigt Datum und Uhrzeit, Zeitraum oder Startrechnung, Anzahl der Rechnungen und die Rechnungsnummer aus der letzten Zeile der Datei.

Nach dem Download **Anzeige aktualisieren** anklicken. Fehler und leere Ergebnisse überschreiben den letzten Export nicht. Die Anzeige dokumentiert die Erstellung der Datei, nicht den Import in WISO. Exporte vor Version 1.2.0 werden nicht nachträglich erfasst.

## Exportformat

Tabulatorgetrennt, ohne Kopfzeile, UTF-8 ohne BOM.

| Position | Kleinunternehmer | Brutto / Netto |
| --- | --- | --- |
| 1 | Einnahme | Einnahme |
| 2 | Rechnungsdatum | Rechnungsdatum |
| 3 | Rechnungsnummer | Rechnungsnummer |
| 4 | Bezeichnung | Bezeichnung |
| 5 | Bruttobetrag | Brutto- bzw. Nettobetrag |
| 6 | Sachkonto | Brutto bzw. Netto |
| 7 | Geldkonto | Sachkonto |
| 8 | — | Geldkonto |

**Kleinunternehmer:** sieben Spalten. Nach dem Betrag folgt direkt das Sachkonto, ohne zusätzliche oder leere Spalte.

```text
Einnahme	10.02.2025	RE000007	Warenverkauf	10,99 €	8195	1200
```

**Brutto / Netto:** acht Spalten. Die Auswahl bestimmt auch den exportierten Betrag.

```text
Einnahme	10.02.2025	RE000007	Warenverkauf	119,00 €	Brutto	8195	1200
Einnahme	10.02.2025	RE000007	Warenverkauf	100,00 €	Netto	8195	1200
```

Beim Update auf 1.2.0 die Spaltenzuordnung für Kleinunternehmer in Buchungsimport auf das neue siebenspaltige Format einstellen.

## Hinweise

- Für EUR-Rechnungen vorgesehen; keine Währungsumrechnung.
- Separate Gutschriften und Rückerstattungsbelege sind nicht enthalten. Negative Rechnungsbeträge werden als negative Einnahmen ausgegeben.
- Rechnungsnummern entsprechen den aktuellen Shop-Einstellungen. Spätere Formatänderungen können ältere Rechnungen beeinflussen.
- Wiederholte Exporte können dieselben Rechnungen enthalten. Der letzte Export dient zur Orientierung; es gibt keinen automatischen Ausschluss bereits exportierter Rechnungen.

## Lizenz

[GNU GPL-3.0-or-later](LICENSE)
