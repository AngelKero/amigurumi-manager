# ADR-017: Scoping de Lectura del Panel & Endpoint `mias.php`

- **Estado:** Aceptado (Subfase 4.3.1 · Feature 006)
- **Fecha:** 2026-09-15
- **Extiende:** ADR-007 (autorización IDOR multi-artesano)

## Contexto

El panel del artesano (`creaciones.php`) consumía el catálogo **público**
`GET /api/creaciones/index.php` sin Bearer ni scoping de servidor. El filtro
`artesano_id` era 100% controlado por el cliente: omitirlo devolvía todo el
catálogo (incluidas piezas ajenas) y falsearlo exponía piezas de terceros con
HTTP 200. `ensureArtisanOwnership()` (ADR-007) solo protegía **escrituras**;
la lectura no tenía autorización por propiedad. Además, el botón Restaurar era
inalcanzable: solo se listaban piezas activas.

## Decisión

1. **Nuevo endpoint protegido** `GET /api/creaciones/mias.php` (controlador
   delgado ≤60 líneas, `RoleGuard::artisanOrAdmin()` → 401 sin Bearer).
2. **`CreacionService::getOwnCreations($query, $currentUser)`** impone la
   propiedad en servidor y reutiliza el pipeline de `getCatalog()` (filtros,
   orden, paginación, enriquecido):
   - rol `artesano` → fuerza `artesano_id = user.id`, **ignorando** cualquier
     `artesano_id` del query (anti-spoof);
   - rol `admin` → visión global con `artesano_id` opcional (dropdown real).
3. **Papelera** vía `estado ∈ {activas (default seguro, incluye valores
   inválidos), inactivas, todas}`, mapeada al filtro `activo` existente del
   repositorio (cero SQL nuevo fuera de `app/Repositories/`).
4. **Índice** `idx_creaciones_artesano_activo ON creaciones(artesano_id, activo)`
   en `seed.sql` (instalaciones nuevas) + aplicado `IF NOT EXISTS` en BDs vivas.
5. El catálogo público (`index.php`, `detalle.php`, `artesanos.php`) **no cambia**:
   sigue abierto y sin scoping.

## Alternativas descartadas

- **Parámetro `propias=1` en `index.php`:** mezcla semántica pública/privada en un
  mismo endpoint y exige auditar precedencia `propias > artesano_id` en cada cambio.
- **Forzar scoping siempre que haya Bearer en `index.php`:** rompe el catálogo
  público para artesanos logueados como compradores; comportamiento implícito.

## Consecuencias

- El frontend del panel envía Bearer en **todas** las lecturas (`mias.php`);
  401 limpia la sesión y la guarda de `auth.js` redirige al catálogo.
- R-04 queda cubierto también en lectura del panel; R-01/R-02 intactos
  (la papelera solo cambia `activo`/`eliminado_en`, cero `unlink`).
- Gate: suite `tests/test-subfase-4.3.1.php` + regresión acumulada Fase 4.
