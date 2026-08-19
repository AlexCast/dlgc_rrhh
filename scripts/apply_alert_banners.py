#!/usr/bin/env python3
"""
Aplica el patrón de banners de alerta + validación JS a todos los módulos SRC.
"""
from pathlib import Path
import re

SRC_ROOT = Path('c:/Apache24/htdocs/DLGC_RRHH/src')

# Módulos que deben ignorarse (vacíos u obsoletos)
IGNORED_DIRS = {'src_empleado', 'src_jefe'}

# Género de cada módulo para concordancia en mensajes ('m' o 'f')
GENERO = {
    'afiliaciones_empleados': 'f',
    'area': 'f',
    'arl': 'f',
    'banco': 'm',
    'cajacompensacion': 'f',
    'cesantias': 'f',
    'comunicados': 'm',
    'contratos_empleados': 'm',
    'empleados': 'm',
    'eps': 'f',
    'modulos': 'm',
    'nomina': 'f',
    'operaciones': 'f',
    'pension': 'f',
    'permisos_usuarios': 'm',
    'roles': 'm',
    'roles_operaciones': 'm',
    'sst': 'm',
    'usuarios': 'm',
}

NOMBRE_AMIGABLE = {
    'afiliaciones_empleados': 'Afiliación',
    'area': 'Área',
    'arl': 'ARL',
    'banco': 'Banco',
    'cajacompensacion': 'Caja de compensación',
    'cesantias': 'Cesantías',
    'comunicados': 'Comunicado',
    'contratos_empleados': 'Contrato',
    'empleados': 'Empleado',
    'eps': 'EPS',
    'modulos': 'Módulo',
    'nomina': 'Nómina',
    'operaciones': 'Operación',
    'pension': 'Pensión',
    'permisos_usuarios': 'Permiso de usuario',
    'roles': 'Rol',
    'roles_operaciones': 'Rol-Operación',
    'sst': 'SST',
    'usuarios': 'Usuario',
}


def palabra_con_genero(raiz, genero):
    return raiz + ('o' if genero == 'm' else 'a')


def get_module_name(path):
    return path.relative_to(SRC_ROOT).parts[0]


def get_entity_label(module, sub_entity=None):
    if module == 'sst':
        if sub_entity and 'comite' in sub_entity.lower():
            return 'Miembro del comité', 'm'
        if sub_entity and 'queja' in sub_entity.lower():
            return 'Queja/Sugerencia', 'f'
        return 'Registro', 'm'
    return NOMBRE_AMIGABLE.get(module, 'Registro'), GENERO.get(module, 'm')


def success_message(module, action, sub_entity=None):
    label, genero = get_entity_label(module, sub_entity)
    if action == 'insert':
        return f"{label} {palabra_con_genero('cread', genero)} correctamente."
    if action == 'update':
        return f"{label} {palabra_con_genero('actualizad', genero)} correctamente."
    if action == 'delete':
        return f"{label} {palabra_con_genero('eliminad', genero)} correctamente."
    if action == 'restore':
        return f"{label} {palabra_con_genero('restaurad', genero)} correctamente."
    if action == 'status':
        return 'Estado actualizado correctamente.'
    return 'Operación realizada correctamente.'


ALERT_HELPER_INCLUDE = "<?php require_once __DIR__ . '/../../app/alert_helper.php'; ?>\n"
RENDER_BANNER = "<?php render_alert_banner(); ?>\n"


def find_modules():
    return sorted([d for d in SRC_ROOT.iterdir() if d.is_dir() and d.name not in IGNORED_DIRS])


def update_header(header_path):
    content = header_path.read_text(encoding='utf-8')
    if 'alert-banner.js' in content:
        return False

    new_content, count = re.subn(
        r'(</head>)',
        r'    <script src="/dlgc_rrhh/assets/js/alert-banner.js" defer></script>\n\1',
        content,
        count=1,
        flags=re.IGNORECASE,
    )
    if count == 0:
        print(f"WARN: no se encontró </head> en {header_path}")
        return False

    header_path.write_text(new_content, encoding='utf-8')
    return True


def add_banner_to_page(page_path):
    content = page_path.read_text(encoding='utf-8')
    if 'render_alert_banner' in content:
        return False

    pattern = re.compile(r"(include_once\s+'encab_[^']+\.php';\s*\n?)")
    match = pattern.search(content)
    if not match:
        print(f"WARN: no se encontró encab_* en {page_path}")
        return False

    insert_pos = match.end()
    insertion = f"\n{ALERT_HELPER_INCLUDE}{RENDER_BANNER}"
    new_content = content[:insert_pos] + insertion + content[insert_pos:]

    page_path.write_text(new_content, encoding='utf-8')
    return True


def add_form_validation(form_path):
    content = form_path.read_text(encoding='utf-8')

    if 'data-validate' in content:
        return False

    new_content, count = re.subn(
        r'\bnovalidate\b',
        'novalidate data-validate',
        content,
        count=1,
    )
    if count == 0:
        new_content, count2 = re.subn(
            r'(<form\s+[^>]*method="POST"[^>]*)>',
            r'\1 novalidate data-validate>',
            content,
            count=1,
        )
        if count2 == 0:
            print(f"WARN: no se encontró formulario en {form_path}")
            return False
        content = new_content
    else:
        content = new_content

    if 'comunicados' in str(form_path):
        content, _ = re.subn(
            r'(<form\s+[^>]*data-validate[^>]*)>',
            r'\1 data-min-length-field="#contenido" data-min-length="10">',
            content,
            count=1,
        )

    form_path.write_text(content, encoding='utf-8')
    return True


def update_processor_success(processor_path):
    content = processor_path.read_text(encoding='utf-8')
    module = get_module_name(processor_path)
    stem = processor_path.stem

    action = None
    sub_entity = None
    if stem.startswith('insertar_'):
        action = 'insert'
        sub_entity = stem.replace('insertar_', '')
    elif stem.startswith('update_'):
        action = 'update'
        sub_entity = stem.replace('update_', '')
    elif stem.startswith('eliminar_'):
        action = 'delete'
        sub_entity = stem.replace('eliminar_', '')
    elif stem.startswith('restore_'):
        action = 'restore'
        sub_entity = stem.replace('restore_', '')
    elif stem == 'cambiar_estado_queja':
        action = 'status'
        sub_entity = 'queja'

    if not action:
        return False

    msg = success_message(module, action, sub_entity)

    if f"?success={msg}" in content or f"urlencode('{msg}')" in content:
        return False

    # Caso 1: header('Location: listar_xxx.php'); sin parámetros -> agregar ?success=
    pattern = re.compile(r"header\('Location:\s*(listar_\w+\.php)'\);\s*exit\(\);")

    def repl(m):
        listar = m.group(1)
        return f"header('Location: {listar}?success=' . urlencode('{msg}'));\n    exit();"

    new_content, count = pattern.subn(repl, content)
    if count > 0:
        processor_path.write_text(new_content, encoding='utf-8')
        return True

    # Caso 2: ?restaurado=1 o ?ok=1 -> cambiar a ?success=...
    pattern2 = re.compile(r"header\('Location:\s*(listar_[^\']+\.php)\?restaurado=1'\);\s*exit\(\);")
    pattern3 = re.compile(r"header\('Location:\s*(listar_[^\']+\.php)\?ok=1'\);\s*exit\(\);")

    def repl2(m):
        return f"header('Location: {m.group(1)}?success=' . urlencode('{msg}'));\n    exit();"

    new_content, count = pattern2.subn(repl2, content)
    new_content, count2 = pattern3.subn(repl2, new_content)
    if count + count2 > 0:
        processor_path.write_text(new_content, encoding='utf-8')
        return True

    return False


def main():
    modules = find_modules()
    stats = {
        'headers': 0,
        'pages': 0,
        'forms': 0,
        'processors': 0,
    }

    for module_dir in modules:
        php_files = list(module_dir.glob('*.php'))

        for header in php_files:
            if header.name.startswith('encab_') and header.name.endswith('.php'):
                if update_header(header):
                    stats['headers'] += 1

        for page in php_files:
            if page.name.startswith(('forma_', 'editar_', 'listar_')):
                if add_banner_to_page(page):
                    stats['pages'] += 1

        for form in php_files:
            if form.name.startswith(('forma_', 'editar_')):
                if add_form_validation(form):
                    stats['forms'] += 1

        for proc in php_files:
            if proc.name.startswith(('insertar_', 'update_', 'eliminar_', 'restore_')) or proc.name == 'cambiar_estado_queja.php':
                if update_processor_success(proc):
                    stats['processors'] += 1

    print("Aplicación completada:")
    print(f"  Encabezados actualizados: {stats['headers']}")
    print(f"  Páginas con banner:       {stats['pages']}")
    print(f"  Formularios validados:    {stats['forms']}")
    print(f"  Procesadores con éxito:   {stats['processors']}")


if __name__ == '__main__':
    main()
