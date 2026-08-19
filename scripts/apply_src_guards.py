#!/usr/bin/env python3
"""
Aplica el guard de RBAC a todos los archivos PHP bajo src/.
Uso: python scripts/apply_src_guards.py
"""

import os
import re
from pathlib import Path

ROOT = Path(r'c:\Apache24\htdocs\DLGC_RRHH\src')

MODULE_MAP = {
    'afiliaciones_empleados': 7,
    'area': 8,
    'arl': 9,
    'banco': 10,
    'cajacompensacion': 11,
    'cesantias': 12,
    'contratos_empleados': 13,
    'empleados': 4,
    'eps': 14,
    'nomina': 15,
    'pension': 16,
    'permisos_usuarios': 21,
    'roles_operaciones': 6,
}

ACTION_MAP = {
    'insertar': 'crear',
    'editar': 'actualizar',
    'update': 'actualizar',
    'eliminar': 'eliminar',
    'restore': 'restaurar',
}

MARKER = 'require_once __DIR__ . \'/../../app/src_guard.php\';'


def get_action(filename: str) -> str | None:
    for prefix, action in ACTION_MAP.items():
        if filename.startswith(f'{prefix}_'):
            return action
    return None


def process_file(path: Path, module_id: int) -> bool:
    content = path.read_text(encoding='utf-8')

    if MARKER in content:
        return False  # ya protegido

    action = get_action(path.stem)

    action_line = f"$requiredAction = '{action}';\n" if action else ''
    guard = (
        f"$moduleId = {module_id};\n"
        f"{action_line}"
        "require_once __DIR__ . '/../../app/src_guard.php';\n"
    )

    # Insertar justo después de la apertura <?php
    match = re.match(r'(<\?php\s*)', content)
    if not match:
        print(f'[SKIP] No se encontró apertura PHP: {path}')
        return False

    insert_pos = match.end()
    new_content = content[:insert_pos] + '\n' + guard + content[insert_pos:]

    path.write_text(new_content, encoding='utf-8')
    return True


def main() -> None:
    modified = 0
    for folder_name, module_id in MODULE_MAP.items():
        folder = ROOT / folder_name
        if not folder.exists():
            print(f'[SKIP] Carpeta no existe: {folder}')
            continue

        for php_file in folder.glob('*.php'):
            if process_file(php_file, module_id):
                print(f'[OK] {php_file}')
                modified += 1

    print(f'\nTotal archivos protegidos: {modified}')


if __name__ == '__main__':
    main()
