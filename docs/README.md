# Centro de Documentación Técnica (Documentation Hub)

Bienvenido al centro de documentación técnica de **Crochet Manager** (Plataforma Colaborativa & Micro-ERP de Creaciones en Crochet).

Toda la documentación sigue el estándar modular de **Clean Documentation & Diátaxis Framework**: organizada en dominios específicos, sin archivos monolíticos inmanejables, y vinculada a través de índices navegables.

---

## Mapa de Navegación por Dominios

```
docs/
├── README.md                      # [Este archivo] Directorio e Índice Maestro
│
├── architecture/                  # 🏛️ 1. ARQUITECTURA DEL SISTEMA & DECISIONES (ADRs)
│   ├── README.md                  # Visión general de Clean Architecture y capas
│   ├── phase-3-plan.md            # Plan maestro de la Fase 3 (6 subfases y compuertas)
│   ├── contracts.md               # Contratos de clases (Repositories, Services, Middleware)
│   ├── security.md                # Autenticación stateless HMAC-SHA256, RBAC e IDOR
│   └── decisiones/                # Registro histórico de 15 ADRs individuales y numerados
│       ├── README.md              # Índice maestro de ADRs
│       └── ADR-001 a ADR-015     # Decisiones técnicas fundamentales
│
├── api/                           # 🌐 2. ESPECIFICACIÓN DE LA API REST
│   ├── README.md                  # Estándares globales, JSON Envelope, códigos HTTP y CORS
│   ├── auth.md                    # Endpoints /api/auth/ (login, logout, me, cambiar-password)
│   ├── creaciones.md              # Endpoints /api/creaciones/ (catálogo, artesanos, CRUD, fotos)
│   ├── pedidos.md                 # Endpoints /api/pedidos/ (solicitar, crear, cancelar, WhatsApp)
│   └── usuarios.md                # Endpoints /api/usuarios/ (directorio, roles, baja y reactivación)
│
├── database/                      # 🗄️ 3. MODELO DE DATOS & PERSISTENCIA RELACIONAL
│   ├── README.md                  # Visión general y Diagrama Entidad-Relación Físico (ERD)
│   ├── schema.md                  # Sentencias DDL completas, restricciones CHECK y diccionarios
│   └── testing.md                 # Guía de verificación CLI con sqlite3 y setup.php
│
├── design-system/                 # 🎨 4. SISTEMA DE DISEÑO "ALGODÓN NÓRDICO" & UI/UX
│   ├── README.md                  # Tokens cromáticos, jerarquía tipográfica y craft detailing
│   ├── brand-identity.md          # Manual de marca v2.0.0 (isotipos, logotipos, imagotipos, isologos)
│   ├── svg-assets.md              # Biblioteca de recursos vectoriales y SvgHelper en PHP
│   ├── wireframes.md              # Especificación de vistas responsivas y componentes PHP
│   └── audits.md                  # Auditorías heurísticas y verificación de contrastes WCAG 2.1 AA
│
├── testing/                       # 🧪 5. REPORTES DE PRUEBAS & ASEGURAMIENTO DE CALIDAD
│   ├── README.md                  # Arquitectura de testing en 3 niveles y protocolo de compuertas
│   ├── subfase-3.1-core.md        # Reporte formal Subfase 3.1: Core & Infraestructura (93/93 OK)
│   ├── subfase-3.2-auth.md        # Reporte formal Subfase 3.2: Autenticación & Tokens (69/69 OK)
│   ├── subfase-3.3-usuarios.md    # Reporte formal Subfase 3.3: Usuarios & RBAC (105/105 OK)
│   └── qa-audit-report.md         # Auditoría integral multi-eje de 6 dimensiones (98.5/100)
│
└── archive/                       # 📦 6. ARCHIVO HISTÓRICO
    ├── wireframes.html            # Prototipo HTML original
    ├── index.html, detalle.html   # Mockups de vistas iniciales
    └── architecture-refactor-plan.md # Plan de refactorización de Fase 2.5
```

---

## Guía Rápida para Desarrolladores y Agentes

1. **¿Deseas entender la arquitectura de código y capas?**  
   Consulta [docs/architecture/README.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/architecture/README.md) y los [ADRs](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/architecture/decisiones/README.md).
2. **¿Deseas consumir o implementar un endpoint de la API?**  
   Consulta [docs/api/README.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/api/README.md) y el módulo correspondiente ([auth](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/api/auth.md), [creaciones](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/api/creaciones.md), [pedidos](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/api/pedidos.md), [usuarios](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/api/usuarios.md)).
3. **¿Deseas consultar o modificar la base de datos?**  
   Consulta [docs/database/README.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/database/README.md) y [docs/database/schema.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/database/schema.md).
4. **¿Deseas maquetar vistas o respetar el diseño artesanal?**  
   Consulta [docs/design-system/README.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/design-system/README.md).
5. **¿Deseas ejecutar y reportar pruebas automatizadas?**  
   Consulta [docs/testing/README.md](file:///Users/angelzaragoza/Desktop/proyecto-web/docs/testing/README.md).
