---
name: logo-generator
description: Generate professional, scalable SVG logos, isotipos, logotipos, imagotipos, and isologos. Standards for vector viewBox geometry, semantic paths, accessibility labels, and craft detailing.
metadata:
  version: 1.0.0
---

# SVG Logo & Identity Vector Generator

Technical standards for generating production-ready vector assets across the four brand identity archetypes.

## 1. Vector Standards
- **viewBox Consistency:** Always define appropriate `viewBox` (e.g., `0 0 100 100` for square isotipos/isologos, `0 0 280 80` for horizontal imagotipos/logotipos).
- **Clean Semantic XML:** Self-contained SVGs with `xmlns="http://www.w3.org/2000/svg"`, explicit semantic classes, and `aria-label` / `<title>`.
- **Vector Styling:** Use inline styles or SVG presentation attributes (`fill`, `stroke`, `stroke-width`, `stroke-dasharray`, `stroke-linecap="round"`, `stroke-linejoin="round"`).
- **Scalability & Crisp Rendering:** Integer grid alignment, avoid subpixel jitter, clean bezier handles.

## 2. Branding Typologies
1. **Isotipo:** Pure icon / symbol without typographic text. Highly recognizable at small sizes (16px to 64px).
2. **Logotipo:** Stylized typographic mark (Wordmark). Emphasizes bespoke lettering, ligature curves, and textile personality.
3. **Imagotipo:** Composed mark where the symbol and wordmark are balanced side-by-side or stacked, able to be disassembled.
4. **Isologo:** Unified badge, seal, or crest where typography and illustration are intrinsically fused (e.g. circular text on a path surrounding an embroidered amigurumi crest).
