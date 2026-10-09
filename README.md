# PrestaShop WISO EÜR Export

Ein PrestaShop-Modul zum Export von Rechnungen als TSV-Datei für den Import in **WISO EÜR & Kasse**.

Der Import erfolgt über die Software [Buchungsimport](https://www.buchungsimport.de/).

---

## Unterstützte Versionen

* PrestaShop 9.1.x

---

## Funktionen

* Export von Rechnungen nach Zeitraum
* Optionaler Filter nach Bestellstatus
* Standardstatus nach Installation: Versand
* TSV-Datei ohne Kopfzeile
* UTF-8 ohne BOM
* WISO-kompatibles Format
* Unterstützung für:

  * Kleinunternehmer (`NULL`)
  * Brutto
  * Netto
* Konfigurierbare Konten
* Konfigurierbare Bezeichnung
* Automatische Rechnungsnummernformatierung
* Historische Rechnungsnummern bleiben korrekt
* Jahreswechsel wird automatisch berücksichtigt

---

## Installation

Die Moduldateien liegen direkt im Hauptverzeichnis dieses Repositories. Für die Installation muss das ZIP dagegen einen übergeordneten Ordner namens `wisoexport/` enthalten.

1. Repository über **Code → Download ZIP** herunterladen und entpacken.
2. Den entpackten Repository-Ordner in `wisoexport` umbenennen und diesen Ordner als `wisoexport.zip` komprimieren. Bei einem lokalen Git-Checkout `.git/` nicht mitpacken.

Die Installations-ZIP muss folgende Struktur enthalten:

```
wisoexport/
├── wisoexport.php
├── config.xml
├── controllers/
├── src/
├── views/
└── translations/
```

3. PrestaShop Backend öffnen:

```
Module
→ Modulmanager
→ Modul hochladen
```

4. `wisoexport.zip` auswählen und installieren.

---

## Konfiguration

Nach der Installation:

```
Module
→ WISO EÜR Export
→ Konfigurieren
```

Folgende Einstellungen stehen zur Verfügung:

| Feld                    | Beschreibung                                   |
| ----------------------- | ---------------------------------------------- |
| Basis-Präfix            | Fester Bestandteil der Rechnungsnummer         |
| Nummernlänge            | Anzahl der Stellen der laufenden Nummer        |
| Bezeichnung             | Buchungsbezeichnung für WISO                   |
| Sachkonto               | Einnahmekonto                                  |
| Geldkonto               | Verrechnungskonto / Bankkonto                  |
| Umsatzsteuerart         | NULL, Brutto oder Netto                        |
| Bestellstatus verwenden | Aktiviert oder deaktiviert die Statusfilterung |
| Bestellstatus           | Status, der exportiert werden soll             |

Der Standardwert für den Bestellstatus ist **Versand**.

---

## Export

Der Export befindet sich im Menü:

```
Bestellungen
→ WISO EÜR Export
```

Zeitraum auswählen und Export starten.

Die Datei wird als TSV-Datei heruntergeladen.

Beispiel-Dateiname:

```
WISO_EUR_2025-01-01_bis_2025-12-31.tsv
```

---

## Exportformat

Die Datei enthält keine Kopfzeile.

Beispiel:

```
Einnahme	10.02.2025	RE25000007	Warenverkauf	10,99 €	NULL	8195	1200
```

Die Felder sind:

| Position | Inhalt          |
| -------- | --------------- |
| 1        | Buchungsart     |
| 2        | Rechnungsdatum  |
| 3        | Rechnungsnummer |
| 4        | Bezeichnung     |
| 5        | Betrag          |
| 6        | Umsatzsteuerart |
| 7        | Sachkonto       |
| 8        | Geldkonto       |

---

## Rechnungsnummer

Die Rechnungsnummer wird automatisch erzeugt aus:

```
Präfix + zweistelliges Jahr + laufende Nummer
```

Generisches Beispiel mit dem frei gewählten Präfix `RE`, dem Rechnungsjahr 2025 und einer sechsstelligen laufenden Nummer:

```
RE + 25 + 000007 = RE25000007
```

Das Jahr wird aus dem Rechnungsdatum der Rechnung übernommen.

Dadurch bleiben historische Rechnungsnummern auch nach einem Jahreswechsel korrekt.

---

## Steuerarten

Die Einstellung **Umsatzsteuerart** bestimmt den Wert in der TSV-Datei:

| Einstellung                           | TSV-Wert |
| ------------------------------------- | -------- |
| Keine Umsatzsteuer (Kleinunternehmer) | NULL     |
| Brutto                                | Brutto   |
| Netto                                 | Netto    |

---

## Lizenz

[GPL-3.0-or-later](LICENSE). Der ursprüngliche Lizenzhinweis des Moduls ist in [LICENSE.notice](LICENSE.notice) enthalten.
