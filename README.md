# WISO EÜR Export für PrestaShop

[English](README.en.md) · [Releases](https://github.com/LoQue90/prestashop-tsv-export/releases) · [Changelog](CHANGELOG.md)

Exportiert Rechnungen aus PrestaShop als TSV für **WISO EÜR & Kasse** über [Buchungsimport](https://www.buchungsimport.de/).

## Funktionen

- Export nach Rechnungsdatum mit Prüfung des gewählten Zeitraums.
- Optionaler Filter nach aktuellem Bestellstatus.
- Option **„Rechnungen mit Betrag 0 ausschließen“**; negative Beträge bleiben enthalten.
- Rechnungsnummern über die PrestaShop-Kernfunktion, einschließlich optionaler Formatierungs-Hooks.
- Einstellbare Buchungsbezeichnung, Sachkonto, Geldkonto und Umsatzsteuerart.
- Bei `Netto` wird der Nettorechnungsbetrag exportiert; bei `Brutto` und `NULL` der Bruttorechnungsbetrag.
- TSV mit acht Spalten, ohne Kopfzeile und BOM, mit UTF-8 und CRLF-Zeilenenden.

## Voraussetzungen

Die Kompatibilitätsangabe umfasst **PrestaShop 9.1.x einschließlich 9.1.5**. Die PHP-Version muss zu deiner PrestaShop-Installation passen. Die GitHub Action prüft Syntax und Regressionstests unter PHP 8.1–8.4. Ein vollständiger Installationstest mit PrestaShop-Datenbank und ein WISO-Import sind nicht Bestandteil dieser Tests.

**Kein zusätzliches Rechnungsnummern-Modul erforderlich.** WISO Export funktioniert eigenständig. Ist ein Formatter über PrestaShops regulären Hook aktiv, berücksichtigt PrestaShop ihn automatisch. Es gibt keine direkte Abhängigkeit, keine gemeinsam gelesenen Moduleinstellungen und keine eingebundenen Dateien eines anderen Moduls.

## Installation und Update

1. Unter [Releases](https://github.com/LoQue90/prestashop-tsv-export/releases/latest) das Asset **`wisoexport-v1.1.0.zip`** herunterladen.
2. In PrestaShop **Module → Modulmanager → Modul hochladen** öffnen.
3. Die ZIP hochladen und das Modul installieren beziehungsweise aktualisieren.
4. Über **Konfigurieren** die Export-Einstellungen prüfen.

Zum Aktualisieren das Modul **nicht deinstallieren**, damit vorhandene Einstellungen erhalten bleiben. Der neue Nullbetragsfilter ist standardmäßig deaktiviert, auch beim Update.

Die von GitHub automatisch angebotenen **Source code**-Archive sind keine fertig gepackten Modul-ZIPs. Das Release-Asset enthält den benötigten Ordner `wisoexport/` mit `wisoexport.php` direkt darin. Eine SHA-256-Prüfsumme liegt ebenfalls bei.

## Einstellungen

| Einstellung | Bedeutung |
| --- | --- |
| Rechnungen mit Betrag 0 ausschließen | Entfernt exakt `0` beim Bruttorechnungsbetrag. Positive und negative Beträge bleiben enthalten. Standard: aus. |
| Bezeichnung | Text für die Buchung in WISO. |
| Sachkonto | Einnahmekonto für den Import. |
| Geldkonto | Bank- oder Verrechnungskonto für den Import. |
| Bestellstatus verwenden | Aktiviert den Filter nach dem aktuellen Bestellstatus. |
| Bestellstatus für Export | Status, der bei aktiviertem Filter berücksichtigt wird. Nach Installation wird „Versand“ gesucht; andernfalls bitte einen Status auswählen oder den Filter deaktivieren. |
| Umsatzsteuerart | `NULL`, `Brutto` oder `Netto`. Standard: `NULL`. |

Die Umsatzsteuerart bestimmt das Exportfeld und die Auswahl des gespeicherten Brutto-/Nettobetrags. Das Modul berechnet keine neuen Steuern. Konten und Importzuordnung müssen zu deiner Buchhaltung passen.

## Export

Unter **Bestellungen → WISO EÜR Export** ein Von- und Bis-Datum auswählen. Beide Tage sind vollständig eingeschlossen; Von darf nicht nach Bis liegen. Die Abfrage verwendet das **Rechnungsdatum**, nicht Zahlungs- oder Versanddatum, und berücksichtigt den aktuellen Shop-Kontext.

Beispiel-Dateiname: `WISO_EUR_2025-01-01_bis_2025-12-31.tsv`.

Sind nach Anwendung der Filter keine Rechnungen vorhanden, erscheint eine Meldung statt einer leeren Datei.

### TSV-Format

| Spalte | Inhalt |
| --- | --- |
| 1 | Buchungsart: `Einnahme` |
| 2 | Rechnungsdatum: `TT.MM.JJJJ` |
| 3 | Von PrestaShop formatierte Rechnungsnummer |
| 4 | Konfigurierte Bezeichnung |
| 5 | Betrag mit Dezimalkomma und `€`, einschließlich eines möglichen Minuszeichens |
| 6 | Umsatzsteuerart: `NULL`, `Brutto` oder `Netto` |
| 7 | Sachkonto |
| 8 | Geldkonto |

Generisches Beispiel (die Felder sind durch Tabulatoren getrennt):

```text
Einnahme	10.02.2025	RE000007	Warenverkauf	10,99 €	NULL	8195	1200
```

### Rechnungsnummern

Der Export ruft `OrderInvoice::getInvoiceNumberFormatted()` für die jeweilige Rechnung mit Bestellsprache und Shop-ID auf. Ohne zusätzliches Modul gilt das PrestaShop-Standardformat; mit aktivem Formatierungs-Hook dessen Ergebnis. Das Präfix `RE` im Beispiel ist frei gewählt.

Seit Version 1.1.0 entfallen die eigenen Exportfelder für Präfix und Nummernlänge. Alte gespeicherte Werte werden nicht mehr verwendet. Die Nummer wird zur Exportzeit anhand der aktuellen Shop-/Hook-Konfiguration formatiert; eine unveränderliche historische Zeichenfolge wird nicht separat gespeichert. Nach späteren Formatänderungen kann sich deshalb auch die Darstellung älterer Rechnungen ändern.

### Umfang und Grenzen

- Exportiert werden nummerierte Rechnungen aus `order_invoice`. Separate Gutschriften/Rückerstattungsbelege aus `order_slip` sind nicht enthalten.
- Negative Rechnungsbeträge bleiben erhalten und werden als negative `Einnahme` ausgegeben. Die Zuordnung sollte im verwendeten Importprofil geprüft werden.
- Der Nullbetragsfilter prüft den gespeicherten Bruttobetrag auf exakt null, vor der Ausgabe mit zwei Dezimalstellen.
- Das Exportformat verwendet Euro. Es findet keine Währungsumrechnung statt; der Export ist für EUR-Rechnungen vorgesehen.
- Der Statusfilter prüft den aktuellen Bestellstatus, nicht den Status am Rechnungsdatum.

## Entwicklung und Releases

Die Moduldateien liegen direkt im Hauptverzeichnis des Repositories.

```sh
php tests/run.php
python3 scripts/build-release.py
```

Der Build erzeugt `dist/wisoexport-v1.1.0.zip` und eine SHA-256-Datei. Tests, Git-Daten und GitHub-Workflows werden nicht ins Modulpaket aufgenommen.

Die Action **Test and release module** prüft Pull Requests und Pushes. Auf `main` veröffentlicht sie nach erfolgreichen Tests eine noch nicht vorhandene Modulversion als GitHub Release samt ZIP und Versions-Tag. Weitere Pushes derselben Version ersetzen das Release nicht. Auch Versions-Tags (`v1.1.0`) und ein manueller Start auf `main` werden unterstützt. Bei Tags muss die Tag-Version mit Modul und `config.xml` übereinstimmen.

Für eine neue Veröffentlichung Versionsnummer in `wisoexport.php` und `config.xml` erhöhen sowie Changelog und `.github/release-notes.md` aktualisieren. Ein bestehendes Release bleibt unverändert.

## Lizenz

[GNU GPL-3.0-or-later](LICENSE).
