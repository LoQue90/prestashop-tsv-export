## Installation / Update

**Deutsch:** Die Datei `wisoexport-v1.1.0.zip` unter Assets herunterladen und in PrestaShop unter **Module → Modulmanager → Modul hochladen** importieren. Die automatisch angebotenen „Source code“-Archive sind keine fertigen Modulpakete. Vorhandene Einstellungen bleiben bei einem Update erhalten; das Modul nicht deinstallieren.

**English:** Download `wisoexport-v1.1.0.zip` from Assets and upload it through **Modules → Module Manager → Upload a module**. The automatic “Source code” archives are not installable module packages. Update without uninstalling to preserve settings.

## 1.1.0

- Independent invoice formatting through PrestaShop's core API, including its optional formatter hooks.
- Optional exclusion of exactly zero invoice totals; negative totals remain included.
- Correct net amount export, optional status filter fix and strict date validation.
- PrestaShop compatibility declared for 9.1.x, including 9.1.5.
- Automated PHP regression tests and deterministic installable ZIP with SHA-256 checksum.

See README.md / README.en.md for settings and limitations. Automated tests do not replace installation and import testing in your shop.
