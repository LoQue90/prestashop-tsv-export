#!/usr/bin/env python3
"""Build a deterministic, directly installable PrestaShop module ZIP."""
from pathlib import Path
import hashlib
import re
import sys
import zipfile
import xml.etree.ElementTree as ET

root = Path(__file__).resolve().parents[1]
version = re.search(r"\$this->version = '([^']+)'", (root / 'wisoexport.php').read_text()).group(1)
assert re.fullmatch(r'\d+\.\d+\.\d+', version), 'Invalid version'
assert ET.parse(root / 'config.xml').findtext('version') == version, 'Version mismatch'
if len(sys.argv) > 1:
    assert sys.argv[1] == 'v' + version, 'Tag must match module version'
files = [root / name for name in ['wisoexport.php', 'config.xml', 'index.php', 'LICENSE', 'README.md', 'README.en.md', 'CHANGELOG.md']]
for directory in ['controllers', 'src', 'translations', 'views', 'upgrade']:
    files.extend(p for p in (root / directory).rglob('*') if p.is_file())
out = root / 'dist'
out.mkdir(exist_ok=True)
archive = out / f'wisoexport-v{version}.zip'
with zipfile.ZipFile(archive, 'w', compression=zipfile.ZIP_DEFLATED) as z:
    for path in sorted(files):
        entry = zipfile.ZipInfo('wisoexport/' + path.relative_to(root).as_posix(), (2026, 1, 1, 0, 0, 0))
        entry.compress_type = zipfile.ZIP_DEFLATED
        entry.external_attr = 0o100644 << 16
        z.writestr(entry, path.read_bytes())
with zipfile.ZipFile(archive) as z:
    assert z.testzip() is None
    assert 'wisoexport/wisoexport.php' in z.namelist()
    assert all(name.startswith('wisoexport/') for name in z.namelist())
checksum = hashlib.sha256(archive.read_bytes()).hexdigest()
archive.with_suffix('.zip.sha256').write_text(f'{checksum}  {archive.name}\n')
print(archive.name)
