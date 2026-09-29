#!/usr/bin/env python3
"""
Reemplaza los enlaces fijos a firstpage.php en los encab_*.php de src/
por una llamada a get_dashboard_url().
"""

from pathlib import Path

ROOT = Path(r'c:\Apache24\htdocs\DLGC_RRHH\src')
OLD_HREF = 'href="/dlgc_rrhh/templates/firstpage.php"'
NEW_HREF = 'href="<?php echo get_dashboard_url(); ?>"'


def process_file(path: Path) -> bool:
    content = path.read_text(encoding='utf-8')
    if OLD_HREF not in content:
        return False
    new_content = content.replace(OLD_HREF, NEW_HREF)
    path.write_text(new_content, encoding='utf-8')
    return True


def main() -> None:
    modified = 0
    for encab_file in ROOT.rglob('encab_*.php'):
        if process_file(encab_file):
            print(f'[OK] {encab_file}')
            modified += 1
    print(f'\nTotal archivos modificados: {modified}')


if __name__ == '__main__':
    main()
