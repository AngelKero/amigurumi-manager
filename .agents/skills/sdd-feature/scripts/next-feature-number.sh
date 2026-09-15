#!/usr/bin/env sh
# Asiste al skill sdd-feature: devuelve el siguiente número NNN de feature
# disponible a partir de los directorios existentes en spec/features/.
#
# Compatible con opencode y Antigravity (sin dependencias, POSIX sh).
set -eu

SCRIPT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
ROOT=$(CDPATH= cd -- "$SCRIPT_DIR/../../../../" && pwd)
FEATURES="$ROOT/spec/features"

usage() {
    cat <<'EOF'
Uso: next-feature-number.sh [--help]

Devuelve (stdout) el siguiente número NNN de feature (3 dígitos, p.ej. 009)
basado en los directorios existentes en spec/features/ con formato NNN-nombre-feature.

Sin directorios previos devuelve 001. Es idempotente: no crea nada.

Ejemplo:
  $ scripts/next-feature-number.sh
  009

Opciones:
  -h, --help   Muestra esta ayuda.
EOF
}

case "${1:-}" in
    -h|--help)
        usage
        exit 0
        ;;
esac

[ -d "$FEATURES" ] || { printf '001\n'; exit 0; }

max=0
for d in "$FEATURES"/[0-9][0-9][0-9]-*; do
    [ -d "$d" ] || continue
    n=$(basename "$d" | cut -c1-3)
    case "$n" in
        *[!0-9]*) continue ;;
    esac
    if [ "$n" -gt "$max" ]; then
        max=$n
    fi
done

next=$((max + 1))
printf '%03d\n' "$next"