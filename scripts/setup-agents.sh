#!/usr/bin/env bash
# ============================================================================
# 🧶 Crochet Manager — Agent Environment Setup
# ============================================================================
# Restaura el entorno completo de agentes IA para este proyecto.
# Uso: bash scripts/setup-agents.sh
# ============================================================================

set -euo pipefail

CYAN='\033[0;36m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color
BOLD='\033[1m'

echo ""
echo -e "${CYAN}${BOLD}🧶 Crochet Manager — Agent Environment Setup${NC}"
echo -e "${CYAN}=============================================${NC}"

ERRORS=0

# ──────────────────────────────────────────────
# 1. Verificar prerrequisitos
# ──────────────────────────────────────────────
echo ""
echo -e "${BOLD}📋 Prerrequisitos${NC}"

if command -v php &>/dev/null; then
    PHP_VER=$(php -r "echo PHP_VERSION;")
    echo -e "  ${GREEN}✅${NC} PHP $PHP_VER"
else
    echo -e "  ${RED}❌${NC} PHP no encontrado"
    ERRORS=$((ERRORS + 1))
fi

if command -v npx &>/dev/null; then
    echo -e "  ${GREEN}✅${NC} npx disponible"
    HAS_NPX=1
else
    echo -e "  ${YELLOW}⚠️${NC}  npx no encontrado (opcional — solo para restaurar skills de GitHub)"
    HAS_NPX=0
fi

# ──────────────────────────────────────────────
# 2. Verificar estructura de agentes
# ──────────────────────────────────────────────
echo ""
echo -e "${BOLD}🔍 Estructura de agentes${NC}"

REQUIRED_DIRS=(".agents/skills" ".agents/rules" ".agents/workflows")
for dir in "${REQUIRED_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        COUNT=$(find "$dir" -maxdepth 1 -type f -o -type d | wc -l | tr -d ' ')
        echo -e "  ${GREEN}✅${NC} $dir ($COUNT items)"
    else
        echo -e "  ${RED}❌${NC} $dir ${RED}(FALTANTE)${NC}"
        ERRORS=$((ERRORS + 1))
    fi
done

OPTIONAL_FILES=(".agents/skills.json" ".agents/plugins.json" ".agents/mcp_config.json")
for file in "${OPTIONAL_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo -e "  ${GREEN}✅${NC} $file"
    else
        echo -e "  ${YELLOW}⚠️${NC}  $file (no encontrado)"
    fi
done

# ──────────────────────────────────────────────
# 3. Restaurar skills desde el lockfile
# ──────────────────────────────────────────────
echo ""
echo -e "${BOLD}📦 Skills del proyecto${NC}"

if [ -f "skills-lock.json" ]; then
    echo -e "  ${GREEN}✅${NC} skills-lock.json encontrado"
    
    # Contar skills por tipo de fuente
    LOCAL_COUNT=$(grep -c '"sourceType": "local"' skills-lock.json 2>/dev/null || echo 0)
    GITHUB_COUNT=$(grep -c '"sourceType": "github"' skills-lock.json 2>/dev/null || echo 0)
    echo -e "  ${CYAN}→${NC} $LOCAL_COUNT skills locales (ya en repo), $GITHUB_COUNT skills de GitHub"
    
    if [ "$GITHUB_COUNT" -gt 0 ] && [ "$HAS_NPX" -eq 1 ]; then
        echo -e "  ${CYAN}→${NC} Restaurando skills de GitHub desde lockfile..."
        npx -y skills experimental_install 2>&1 | sed 's/^/     /' || true
        echo -e "  ${GREEN}✅${NC} Skills de GitHub restauradas"
    elif [ "$GITHUB_COUNT" -gt 0 ]; then
        echo -e "  ${YELLOW}⚠️${NC}  npx no disponible — no se pueden restaurar skills de GitHub"
    fi
    
    echo -e "  ${GREEN}✅${NC} Skills locales disponibles en .agents/skills/"
else
    echo -e "  ${YELLOW}⚠️${NC}  skills-lock.json no encontrado"
fi

# Listar skills instaladas
echo ""
echo -e "${BOLD}📋 Skills instaladas:${NC}"
if [ "$HAS_NPX" -eq 1 ]; then
    npx -y skills list 2>/dev/null | sed 's/^/  /' || echo -e "  ${YELLOW}(no se pudo listar)${NC}"
else
    find .agents/skills -name "SKILL.md" -exec dirname {} \; | xargs -I{} basename {} | sort | sed 's/^/  📌 /'
fi

# ──────────────────────────────────────────────
# 4. Verificar spec/ y base de datos
# ──────────────────────────────────────────────
echo ""
echo -e "${BOLD}🗃️  Infraestructura${NC}"

if [ -d "spec/constitution" ]; then
    echo -e "  ${GREEN}✅${NC} spec/constitution/ (SDD activo)"
else
    echo -e "  ${YELLOW}⚠️${NC}  spec/constitution/ no encontrado"
fi

if [ -f "database/database.sqlite" ]; then
    SIZE=$(du -h database/database.sqlite | cut -f1 | tr -d ' ')
    echo -e "  ${GREEN}✅${NC} database/database.sqlite ($SIZE)"
else
    echo -e "  ${YELLOW}⚠️${NC}  Base de datos no encontrada"
    echo -e "     Ejecuta: ${BOLD}php setup.php${NC}"
fi

# ──────────────────────────────────────────────
# 5. Resumen
# ──────────────────────────────────────────────
echo ""
echo -e "${CYAN}─────────────────────────────────────────────${NC}"
if [ "$ERRORS" -eq 0 ]; then
    echo -e "${GREEN}${BOLD}🎉 Setup completo. Sin errores.${NC}"
else
    echo -e "${RED}${BOLD}⚠️  Setup completado con $ERRORS error(es).${NC}"
fi
echo ""
echo -e "  Servidor local:  ${BOLD}php -S localhost:8000${NC}"
echo -e "  Init base datos: ${BOLD}php setup.php${NC}"
echo ""
