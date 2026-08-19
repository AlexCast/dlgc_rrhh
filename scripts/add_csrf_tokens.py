#!/usr/bin/env python3
"""
Script idempotente para inyectar tokens CSRF en formularios y validaciones
en los endpoints POST de los módulos administrativos de src/.

Uso:
    python scripts/add_csrf_tokens.py
"""

from pathlib import Path
import re

ROOT = Path(r"c:\Apache24\htdocs\DLGC_RRHH")
SRC = ROOT / "src"


def get_modules():
    return [
        d
        for d in SRC.iterdir()
        if d.is_dir() and d.name not in {"src_empleado", "src_jefe"}
    ]


def add_csrf_to_form(file_path: Path, action_prefix: str) -> bool:
    text = file_path.read_text(encoding="utf-8")
    needle = f'<form action="{action_prefix}'
    if needle not in text:
        return False
    if "csrf_input()" in text:
        return False  # ya tiene

    # Insert CSRF input immediately after the opening <form> tag.
    pattern = re.compile(rf'({re.escape(needle)}[^>]+>)\s*')
    new_text, count = pattern.subn(
        r'\1\n                    <?php echo csrf_input(); ?>\n',
        text,
        count=1,
    )
    if count:
        file_path.write_text(new_text, encoding="utf-8")
        return True
    return False


def add_csrf_to_inline_form(file_path: Path) -> bool:
    text = file_path.read_text(encoding="utf-8")
    if "csrf_input()" in text:
        return False

    # Add CSRF input right after each inline delete/restore form tag.
    pattern = re.compile(
        r'(<form method="POST" action="(?:eliminar|restore)_[^"]+\.php" style="display:inline-block;">)\s*',
        re.IGNORECASE,
    )
    new_text, count = pattern.subn(
        r'\1\n                                                            <?php echo csrf_input(); ?>\n',
        text,
    )
    if count:
        file_path.write_text(new_text, encoding="utf-8")
        return True
    return False


def add_csrf_validation(file_path: Path) -> bool:
    text = file_path.read_text(encoding="utf-8")
    if "csrf_validate()" in text:
        return False
    if "$_SERVER['REQUEST_METHOD']" not in text and "$_POST" not in text:
        return False

    # Insert validation right after src_guard.php include
    pattern = re.compile(
        r"(require_once __DIR__ . '/../../app/src_guard\.php';\r?\n)"
    )
    new_text, count = pattern.subn(
        r"\1\n// Validate CSRF token before processing mutation.\n"
        r"if ($_SERVER['REQUEST_METHOD'] === 'POST') {\n"
        r"    csrf_validate();\n"
        r"}\n",
        text,
    )
    if count:
        file_path.write_text(new_text, encoding="utf-8")
        return True
    return False


def main():
    total_changes = 0

    for module in get_modules():
        name = module.name

        forma = module / f"forma_{name}.php"
        if forma.exists() and add_csrf_to_form(forma, "insertar_"):
            print(f"[FORM] {forma.relative_to(ROOT)}")
            total_changes += 1

        editar = module / f"editar_{name}.php"
        if editar.exists() and add_csrf_to_form(editar, "update_"):
            print(f"[FORM] {editar.relative_to(ROOT)}")
            total_changes += 1

        listar = module / f"listar_{name}.php"
        if listar.exists() and add_csrf_to_inline_form(listar):
            print(f"[INLINE] {listar.relative_to(ROOT)}")
            total_changes += 1

        for action_file in module.glob("*.php"):
            if action_file.name.startswith(("insertar_", "update_", "eliminar_", "restore_")):
                if add_csrf_validation(action_file):
                    print(f"[VALIDATE] {action_file.relative_to(ROOT)}")
                    total_changes += 1

    print(f"\nTotal cambios aplicados: {total_changes}")


if __name__ == "__main__":
    main()
