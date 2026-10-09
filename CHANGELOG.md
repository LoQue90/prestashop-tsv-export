# Changelog

## 1.1.0

- PrestaShop-Kompatibilität für 9.1.x einschließlich 9.1.5 deklariert; Versionsangaben vereinheitlicht.
- Rechnungsnummern über PrestaShops öffentliche Kernfunktion; keine Abhängigkeit von einem Formatter-Modul.
- Eigene Präfix- und Nummernlängeneinstellungen entfernt.
- Optionaler Ausschluss von Rechnungen mit exakt 0 Bruttobetrag; negative Beträge bleiben enthalten.
- Statusprüfung nur bei aktiviertem Statusfilter.
- Strenge Datumsprüfung einschließlich Reihenfolge und real existierender Kalendertage.
- Netto-Export verwendet den Nettorechnungsbetrag.
- Vollständige Exportvorbereitung vor dem Download; Fehlerbehandlung und Begrenzung auf den Shop-Kontext.
- Update-Skript, Regressionstests und GitHub Action mit installierbarem Release-ZIP und SHA-256-Prüfsumme.
- Deutsche und englische Dokumentation.

## 1.0.0

Erste stabile Version.

Features:

- Rechnungsexport als TSV
- Unterstützung PrestaShop 9.1.x
- Export nach Zeitraum
- Statusfilter Versand
- Buchungsimport.de Importformat
- UTF-8 ohne BOM
- konfigurierbare Konten
- konfigurierbare Bezeichnung
- dynamische Rechnungsnummer

## 1.0.2

Neu:
- Optionale Statusfilterung
- Umsatzsteuerart auswählbar (NULL/Brutto/Netto)
- Verbesserter Export-Dateiname mit Zeitraum
- Rechnungsnummernformatierung verbessert

Behoben:
- Exportfehler bei fehlendem Zeitraum
- Diverse Stabilitätsverbesserungen