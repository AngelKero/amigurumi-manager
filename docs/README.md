# Centro de Documentación Técnica (Documentation Hub)

Bienvenido al centro de documentación técnica de **Crochet Manager** (Plataforma Colaborativa & Micro-ERP de Creaciones en Crochet).

Toda la documentación sigue el estándar modular de **Clean Documentation & Diátaxis Framework**: organizada en dominios específicos, sin archivos monolíticos inmanejables, y vinculada a través de índices navegables.

> **Integridad del índice (H-013):** cada archivo `.md` bajo `docs/` está enlazado en este índice maestro (árbol estructural exhaustivo). La verificación es automática y obligatoria en la suite de gobernanza (`tests/test-gobernanza-accion-5.php`); cualquier `docs/**/*.md` huérfano es un defecto de Cierre Documental.
>
> **Cifras de aserciones (H-006):** el total de aserciones de la Fase 3 es un comando regenerable, nunca un número copiado: `php tests/cuenta-aserciones.php` (valor verificado actual: **1,287** sobre semilla limpia).

---

## 🧭 Mapa de Navegación por Dominios

| Dominio | Descripción | Acceso Directo |
| :--- | :--- | :--- |
| 🏛️ **Arquitectura & Fases** | Ciclo de vida de todas las fases (0 a 5), diseño Clean Architecture, especificación de seguridad 3.6 y 16 ADRs. | [Explorar Arquitectura](./architecture/README.md) • [Proceso de Todas las Fases](./architecture/proceso-desarrollo-fases.md) • [Plan Fase 3](./architecture/phase-3-plan.md) • [Seguridad 3.6](./architecture/subfase-3.6-auditoria-seguridad.md) • [Contratos](./architecture/contracts.md) • [Matriz de Seguridad](./architecture/security.md) • [Ver ADRs](./architecture/decisiones/README.md) |
| 🌐 **Especificación API REST** | Estándares HTTP, envelope JSON y contratos de endpoints por recurso. | [Explorar API](./api/README.md) • [Auth](./api/auth.md) • [Creaciones](./api/creaciones.md) • [Pedidos](./api/pedidos.md) • [Usuarios](./api/usuarios.md) |
| 🗄️ **Base de Datos & DDL** | Diagrama ERD físico en Mermaid, sentencias DDL completas y tests CLI. | [Explorar Base de Datos](./database/README.md) • [Esquema DDL](./database/schema.md) • [Pruebas](./database/testing.md) |
| 🎨 **Sistema de Diseño** | Tokens Algodón Nórdico, tipografías, manual de marca, auditorías WCAG y heurísticas CR/QW. | [Explorar Diseño](./design-system/README.md) • [Marca](./design-system/brand-identity.md) • [SVGs](./design-system/svg-assets.md) • [Vistas](./design-system/wireframes.md) • [Auditorías & Heurísticas](./design-system/audits.md) |
| 🧪 **Reportes de Testing** | Protocolo en 3 niveles, reportes ejecutivos de subfases 3.1–3.6.5, protocolo de divergencia CLI/HTTP, auditorías context7 y reportes de gobernanza. | [Explorar Testing](./testing/README.md) • [Core 3.1](./testing/subfase-3.1-core.md) • [Auth 3.2](./testing/subfase-3.2-auth.md) • [Usuarios 3.3](./testing/subfase-3.3-usuarios.md) • [Creaciones 3.4](./testing/subfase-3.4-creaciones.md) • [Pedidos 3.5](./testing/subfase-3.5-pedidos.md) • [IDOR 3.6.1](./testing/subfase-3.6.1-idor-access-control.md) • [Criptografía 3.6.2](./testing/subfase-3.6.2-criptografia-autenticacion.md) • [Inyección 3.6.3](./testing/subfase-3.6.3-inyeccion-medios.md) • [Precios 3.6.4](./testing/subfase-3.6.4-logica-precios.md) • [Rendimiento 3.6.5](./testing/subfase-3.6.5-rendimiento-regresion.md) • [Auth Fase 4.1](./testing/subfase-4.1-auth-sesion.md) • [Triaje CLI/HTTP](./testing/protocolo-divergencia-cli-http.md) • [QA](./testing/qa-audit-report.md) |
| 🔐 **Seguridad & Sanitización** | Auditorías de XSS/sanitización de JavaScript y blindaje de dominio. | [Explorar Seguridad](./security/auditoria-sanitizacion-js.md) • [Auditoría Sanitización JS](./security/auditoria-sanitizacion-js.md) |
| 📦 **Archivo Histórico** | Mockups HTML preliminares y planes anteriores de refactorización (obsoletos, no autoritativos). | [Ver Histórico](./archive/README.md) |

---

## 🌳 Árbol Estructural de Documentación

```
docs/
├── README.md                              # [Este archivo] Directorio e Índice Maestro
│
├── architecture/                          # 🏛️ 1. Arquitectura Clean, Proceso de Fases & ADRs
│   ├── README.md                          # https://… → ./architecture/README.md
│   ├── contracts.md                       # ./architecture/contracts.md
│   ├── phase-3-plan.md                    # ./architecture/phase-3-plan.md
│   ├── proceso-desarrollo-fases.md        # ./architecture/proceso-desarrollo-fases.md
│   ├── security.md                        # ./architecture/security.md
│   ├── subfase-3.6-auditoria-seguridad.md # ./architecture/subfase-3.6-auditoria-seguridad.md
│   └── decisiones/
│       ├── README.md                      # ./architecture/decisiones/README.md
│       ├── ADR-001 … ADR-016              # ver lista completa abajo
│       └── ADR-016-token-revocacion-y-brute-force-guard.md
│
├── api/                                   # 🌐 2. Especificación REST por Recursos
│   ├── README.md                          # ./api/README.md
│   ├── auth.md                            # ./api/auth.md
│   ├── creaciones.md                      # ./api/creaciones.md
│   ├── pedidos.md                         # ./api/pedidos.md
│   └── usuarios.md                        # ./api/usuarios.md
│
├── database/                              # 🗄️ 3. Modelo Físico Relacional & DDL
│   ├── README.md                          # ./database/README.md
│   ├── schema.md                          # ./database/schema.md
│   └── testing.md                         # ./database/testing.md
│
├── design-system/                         # 🎨 4. Sistema de Diseño "Algodón Nórdico"
│   ├── README.md                          # ./design-system/README.md
│   ├── audits.md                          # ./design-system/audits.md
│   ├── brand-identity.md                  # ./design-system/brand-identity.md
│   ├── svg-assets.md                      # ./design-system/svg-assets.md
│   └── wireframes.md                      # ./design-system/wireframes.md
│
├── testing/                               # 🧪 5. Reportes de Pruebas & Calidad
│   ├── README.md                          # ./testing/README.md
│   ├── subfase-3.1-core.md                # ./testing/subfase-3.1-core.md
│   ├── subfase-3.2-auth.md                # ./testing/subfase-3.2-auth.md
│   ├── subfase-3.3-usuarios.md            # ./testing/subfase-3.3-usuarios.md
│   ├── subfase-3.4-creaciones.md          # ./testing/subfase-3.4-creaciones.md
│   ├── subfase-3.5-pedidos.md             # ./testing/subfase-3.5-pedidos.md
│   ├── subfase-3.6.1-idor-access-control.md # ./testing/subfase-3.6.1-idor-access-control.md
│   ├── subfase-3.6.2-criptografia-autenticacion.md # ./testing/subfase-3.6.2-criptografia-autenticacion.md
│   ├── subfase-3.6.3-inyeccion-medios.md  # ./testing/subfase-3.6.3-inyeccion-medios.md
│   ├── subfase-3.6.4-logica-precios.md    # ./testing/subfase-3.6.4-logica-precios.md
│   ├── subfase-3.6.5-rendimiento-regresion.md # ./testing/subfase-3.6.5-rendimiento-regresion.md
│   ├── protocolo-divergencia-cli-http.md # ./testing/protocolo-divergencia-cli-http.md
│   ├── subfase-4.1-auth-sesion.md # ./testing/subfase-4.1-auth-sesion.md
│   ├── subfase-4.2-catalogo.md    # ./testing/subfase-4.2-catalogo.md
│   ├── subfase-4.3-creaciones.md  # ./testing/subfase-4.3-creaciones.md
│   ├── subfase-4.3.1-panel-scoping.md # ./testing/subfase-4.3.1-panel-scoping.md
│   ├── subfase-4.3.2-contadores.md # ./testing/subfase-4.3.2-contadores.md
│   ├── subfase-4.3.3-centavos-upload.md # ./testing/subfase-4.3.3-centavos-upload.md
│   ├── subfase-4.4-pedidos.md     # ./testing/subfase-4.4-pedidos.md
│   ├── subfase-4.5-usuarios.md    # ./testing/subfase-4.5-usuarios.md
│   ├── subfase-4.6-whatsapp-artesano.md # ./testing/subfase-4.6-whatsapp-artesano.md
│   ├── qa-audit-report.md                 # ./testing/qa-audit-report.md
│   ├── auditoria-context7-fase-1.md       # ./testing/auditoria-context7-fase-1.md
│   ├── auditoria-context7-fase-2.md       # ./testing/auditoria-context7-fase-2.md
│   ├── auditoria-context7-fase-3.md       # ./testing/auditoria-context7-fase-3.md
│   ├── gobernanza-accion-1-fuente-verdad.md # ./testing/gobernanza-accion-1-fuente-verdad.md
│   ├── gobernanza-accion-2-token-seguridad.md # ./testing/gobernanza-accion-2-token-seguridad.md
│   ├── gobernanza-accion-3-gate-fase-4.md # ./testing/gobernanza-accion-3-gate-fase-4.md
│   ├── gobernanza-accion-4-consolidacion-reglas.md # ./testing/gobernanza-accion-4-consolidacion-reglas.md
│   ├── gobernanza-accion-5-resincronizacion-docs.md # ./testing/gobernanza-accion-5-resincronizacion-docs.md
│   └── gobernanza-accion-6-desacople-fases.md   # ./testing/gobernanza-accion-6-desacople-fases.md
│
├── security/                             # 🔐 7. Seguridad & Sanitización de Frontend
│   └── auditoria-sanitizacion-js.md      # ./security/auditoria-sanitizacion-js.md
│
└── archive/                               # 📦 6. Archivo Histórico (obsoleto, no autoritativo)
    ├── README.md                          # ./archive/README.md
    ├── architecture-refactor-plan.md      # ./archive/architecture-refactor-plan.md
    ├── index.html                         # ./archive/index.html (mockup)
    ├── detalle.html                       # ./archive/detalle.html (mockup)
    ├── formulario.html                    # ./archive/formulario.html (mockup)
    ├── pedidos.html                       # ./archive/pedidos.html (mockup)
    ├── wireframes.html                    # ./archive/wireframes.html (mockup)
    └── reporte-flujo-004-auth.md          # ./archive/reporte-flujo-004-auth.md (histórico)
```

### Catálogo de Decisiones de Arquitectura (ADRs)

| ADR | Enlace |
| :--- | :--- |
| ADR-001 · Clean Architecture PHP + SQLite | [./architecture/decisiones/ADR-001-clean-architecture-php-sqlite.md](./architecture/decisiones/ADR-001-clean-architecture-php-sqlite.md) |
| ADR-002 · Stateless HMAC Bearer Tokens | [./architecture/decisiones/ADR-002-stateless-hmac-bearer-tokens.md](./architecture/decisiones/ADR-002-stateless-hmac-bearer-tokens.md) |
| ADR-003 · Zero HTML Error Leaks | [./architecture/decisiones/ADR-003-zero-html-error-leaks.md](./architecture/decisiones/ADR-003-zero-html-error-leaks.md) |
| ADR-004 · Universal Soft-Delete | [./architecture/decisiones/ADR-004-universal-soft-delete.md](./architecture/decisiones/ADR-004-universal-soft-delete.md) |
| ADR-005 · Exact Integer Cents Currency | [./architecture/decisiones/ADR-005-exact-integer-cents-currency.md](./architecture/decisiones/ADR-005-exact-integer-cents-currency.md) |
| ADR-006 · SQLite Busy Timeout & Concurrency | [./architecture/decisiones/ADR-006-sqlite-busy-timeout-concurrency.md](./architecture/decisiones/ADR-006-sqlite-busy-timeout-concurrency.md) |
| ADR-007 · Multi-Artisan IDOR Authorization | [./architecture/decisiones/ADR-007-multi-artisan-idor-authorization.md](./architecture/decisiones/ADR-007-multi-artisan-idor-authorization.md) |
| ADR-008 · Image Lifecycle & Preservation | [./architecture/decisiones/ADR-008-image-lifecycle-preservation.md](./architecture/decisiones/ADR-008-image-lifecycle-preservation.md) |
| ADR-009 · Idempotent Order Cancellation | [./architecture/decisiones/ADR-009-idempotent-order-cancellation.md](./architecture/decisiones/ADR-009-idempotent-order-cancellation.md) |
| ADR-010 · Root Admin ID #1 Lockout | [./architecture/decisiones/ADR-010-root-admin-id1-lockout.md](./architecture/decisiones/ADR-010-root-admin-id1-lockout.md) |
| ADR-011 · Three-Tier Testing Architecture | [./architecture/decisiones/ADR-011-three-tier-testing-architecture.md](./architecture/decisiones/ADR-011-three-tier-testing-architecture.md) |
| ADR-012 · Self-Service & Admin Password Recovery | [./architecture/decisiones/ADR-012-self-service-and-admin-password-recovery.md](./architecture/decisiones/ADR-012-self-service-and-admin-password-recovery.md) |
| ADR-013 · User Reactivation & Filtering | [./architecture/decisiones/ADR-013-user-reactivation-and-filtering.md](./architecture/decisiones/ADR-013-user-reactivation-and-filtering.md) |
| ADR-014 · Public Active Artisans Endpoint | [./architecture/decisiones/ADR-014-public-active-artisans-endpoint.md](./architecture/decisiones/ADR-014-public-active-artisans-endpoint.md) |
| ADR-015 · Creation Restoration Lifecycle | [./architecture/decisiones/ADR-015-creation-restoration-lifecycle.md](./architecture/decisiones/ADR-015-creation-restoration-lifecycle.md) |
| ADR-016 · Token Revocación & Brute-Force Guard | [./architecture/decisiones/ADR-016-token-revocacion-y-brute-force-guard.md](./architecture/decisiones/ADR-016-token-revocacion-y-brute-force-guard.md) |

---

## ⚡ Guía Rápida para Desarrolladores y Agentes

1. **¿Deseas entender el ciclo de vida de fases y la arquitectura?**
   Consulta el [Proceso de Todas las Fases (1 a 5)](./architecture/proceso-desarrollo-fases.md), el [Plan de la Fase 3](./architecture/phase-3-plan.md), la [Especificación de Seguridad 3.6](./architecture/subfase-3.6-auditoria-seguridad.md) y el catálogo de [Decisiones ADR](./architecture/decisiones/README.md).
2. **¿Deseas consumir o implementar un endpoint de la API?**
   Consulta los [Estándares de API](./api/README.md) y las especificaciones de cada recurso:
   • [Autenticación](./api/auth.md)
   • [Creaciones & Catálogo](./api/creaciones.md)
   • [Pedidos & Encargos](./api/pedidos.md)
   • [Usuarios & Roles](./api/usuarios.md)
3. **¿Deseas consultar o modificar la base de datos?**
   Consulta el [Modelo Relacional](./database/README.md), el [Esquema DDL](./database/schema.md) y las [Pruebas SQLite](./database/testing.md).
4. **¿Deseas maquetar vistas o respetar el diseño artesanal?**
   Consulta el [Sistema de Diseño](./design-system/README.md), el [Manual de Identidad Visual](./design-system/brand-identity.md) y las [Auditorías & Heurísticas CR/QW](./design-system/audits.md).
5. **¿Deseas ejecutar y reportar pruebas automatizadas?**
   Consulta el [Protocolo de Testing](./testing/README.md), el [Triaje CLI/HTTP](./testing/protocolo-divergencia-cli-http.md) y los reportes de subfase [3.1](./testing/subfase-3.1-core.md) a [3.6.5](./testing/subfase-3.6.5-rendimiento-regresion.md), el [Informe QA](./testing/qa-audit-report.md) y las [Auditorías context7](./testing/auditoria-context7-fase-3.md). El total de aserciones de Fase 3 se regenera con `php tests/cuenta-aserciones.php` (H-006).
6. **¿Deseas consultar decisiones técnicas previas?**
   Consulta el [Catálogo de ADRs](./architecture/decisiones/README.md) (ADR-001 a ADR-016).