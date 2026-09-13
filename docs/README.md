# Centro de Documentación Técnica (Documentation Hub)

Bienvenido al centro de documentación técnica de **Crochet Manager** (Plataforma Colaborativa & Micro-ERP de Creaciones en Crochet).

Toda la documentación sigue el estándar modular de **Clean Documentation & Diátaxis Framework**: organizada en dominios específicos, sin archivos monolíticos inmanejables, y vinculada a través de índices navegables.

---

## 🧭 Mapa de Navegación por Dominios

| Dominio | Descripción | Acceso Directo |
| :--- | :--- | :--- |
| 🏛️ **Arquitectura & Fases** | Ciclo de vida de todas las fases (0 a 5), diseño Clean Architecture, especificación de seguridad 3.6 y 15 ADRs. | [Explorar Arquitectura](./architecture/README.md) • [Proceso de Todas las Fases](./architecture/proceso-desarrollo-fases.md) • [Plan Fase 3](./architecture/phase-3-plan.md) • [Seguridad 3.6](./architecture/subfase-3.6-auditoria-seguridad.md) • [Ver ADRs](./architecture/decisiones/README.md) |
| 🌐 **Especificación API REST** | Estándares HTTP, envelope JSON y contratos de endpoints por recurso. | [Explorar API](./api/README.md) • [Auth](./api/auth.md) • [Creaciones](./api/creaciones.md) • [Pedidos](./api/pedidos.md) • [Usuarios](./api/usuarios.md) |
| 🗄️ **Base de Datos & DDL** | Diagrama ERD físico en Mermaid, sentencias DDL completas y tests CLI. | [Explorar Base de Datos](./database/README.md) • [Esquema DDL](./database/schema.md) • [Pruebas](./database/testing.md) |
| 🎨 **Sistema de Diseño** | Tokens Algodón Nórdico, tipografías, manual de marca y auditorías WCAG. | [Explorar Diseño](./design-system/README.md) • [Marca](./design-system/brand-identity.md) • [SVGs](./design-system/svg-assets.md) • [Vistas](./design-system/wireframes.md) |
| 🧪 **Reportes de Testing** | Protocolo en 3 niveles y reportes ejecutivos de subfases (3.1 a 3.5 y 3.6.1). | [Explorar Testing](./testing/README.md) • [Core 3.1](./testing/subfase-3.1-core.md) • [Auth 3.2](./testing/subfase-3.2-auth.md) • [Usuarios 3.3](./testing/subfase-3.3-usuarios.md) • [Creaciones 3.4](./testing/subfase-3.4-creaciones.md) • [Pedidos 3.5](./testing/subfase-3.5-pedidos.md) • [IDOR 3.6.1](./testing/subfase-3.6.1-idor-access-control.md) |
| 📦 **Archivo Histórico** | Mockups HTML preliminares y planes anteriores de refactorización. | [Ver Histórico](./archive/architecture-refactor-plan.md) |

---

## 🌳 Árbol Estructural de Documentación

```
docs/
├── README.md                      # [Este archivo] Directorio e Índice Maestro
│
├── architecture/                  # 🏛️ 1. Arquitectura Clean, Proceso de Fases & ADRs
│   ├── README.md, proceso-desarrollo-fases.md, phase-3-plan.md, subfase-3.6-auditoria-seguridad.md, contracts.md, security.md
│   └── decisiones/ (ADR-001 a ADR-015 + README.md)
│
├── api/                           # 🌐 2. Especificación REST por Recursos
│   └── README.md, auth.md, creaciones.md, pedidos.md, usuarios.md
│
├── database/                      # 🗄️ 3. Modelo Físico Relacional & DDL
│   └── README.md (ERD Mermaid), schema.md, testing.md
│
├── design-system/                 # 🎨 4. Sistema de Diseño "Algodón Nórdico"
│   └── README.md, brand-identity.md, svg-assets.md, wireframes.md, audits.md
│
├── testing/                       # 🧪 5. Reportes de Pruebas & Calidad
│   └── README.md, subfase-3.1-core.md, subfase-3.2-auth.md, subfase-3.3-usuarios.md, subfase-3.4-creaciones.md, subfase-3.5-pedidos.md, subfase-3.6.1-idor-access-control.md, qa-audit-report.md
│
└── archive/                       # 📦 6. Archivo Histórico
    └── wireframes.html, index.html, architecture-refactor-plan.md
```

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
   Consulta el [Sistema de Diseño](./design-system/README.md) y el [Manual de Identidad Visual](./design-system/brand-identity.md).
5. **¿Deseas ejecutar y reportar pruebas automatizadas?**  
   Consulta el [Protocolo de Testing](./testing/README.md) y los reportes de las subfases [3.1](./testing/subfase-3.1-core.md), [3.2](./testing/subfase-3.2-auth.md), [3.3](./testing/subfase-3.3-usuarios.md), [3.4](./testing/subfase-3.4-creaciones.md), [3.5](./testing/subfase-3.5-pedidos.md) y [3.6.1](./testing/subfase-3.6.1-idor-access-control.md).
