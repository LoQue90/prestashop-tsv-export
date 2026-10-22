## Änderungen

- Bis Datum ist mit heute vorbelegt; Von Datum bleibt leer.
- Manuell gewählte Datumswerte bleiben bei Validierungsfehlern erhalten.
- Bestätigter Stand 1.2.1: Kleinunternehmer exportiert sieben Spalten ohne NULL oder Ersatzfeld; sonst acht Spalten mit BRUTTO/NETTO.
- Kompakte Datumsfelder und Anzeige des letzten Exports mit Rechnungsnummer.
- Einstellungen und Export auf einer Seite, mit getrennten Schaltflächen zum Speichern und Exportieren.

## Installation

Die Datei wisoexport-v1.2.2.zip im PrestaShop-Modulmanager hochladen. Bestehendes Modul nicht vorher deinstallieren. Gespeicherte Einstellungen bleiben erhalten. Beschreibung und Konten sind bei Neuinstallation leer.

## Prüfung

PHP-Syntax, Exportregressionen und Template-Prüfung. Lokal zusätzlich isolierte HTTP-Tests für Download, Exportanzeige, Fehlerfälle und wiederholtes Neuladen. Kein vollständiger Upgrade-Test auf einer produktionsgleichen Shopinstallation.
