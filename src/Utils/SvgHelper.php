<?php
/**
 * SVG Helper Utility (Amigurumi Micro-ERP)
 * Algodón Nórdico Design System
 * 
 * Metodología limpia y centralizada para la carga, renderizado inline y resolución
 * de URLs de recursos vectoriales SVG temáticos almacenados en assets/svg/.
 */

declare(strict_types=1);

namespace App\Utils {

    class SvgHelper {
        /**
         * Directorio base de los archivos SVG en el sistema de archivos.
         */
        protected static ?string $baseDir = null;

        /**
         * Memoria caché estática en ejecución para evitar lecturas repetidas de disco.
         * @var array<string, string>
         */
        protected static array $cache = [];

        /**
         * Subcarpetas temáticas donde buscar archivos si no se especifica ruta relativa completa.
         * @var string[]
         */
        protected static array $searchPaths = [
            '',
            'branding/',
            'creaciones/',
            'piezas/',
            'crochet/',
            'tools/',
            'badges/',
            'decorations/'
        ];

        /**
         * Obtiene y normaliza el directorio base donde residen los assets SVG.
         */
        public static function getBaseDir(): string {
            if (self::$baseDir === null) {
                self::$baseDir = realpath(__DIR__ . '/../../assets/svg');
                if (self::$baseDir === false) {
                    // Fallback directo si realpath fallara antes de crearse la ruta
                    self::$baseDir = dirname(__DIR__, 2) . '/assets/svg';
                }
            }
            return self::$baseDir;
        }

        /**
         * Configura manualmente el directorio base (útil para tests unitarios).
         */
        public static function setBaseDir(string $path): void {
            self::$baseDir = rtrim($path, '/\\');
        }

        /**
         * Resuelve la ruta física absoluta de un SVG a partir de su nombre o slug.
         * Soporta nombres con o sin extensión .svg, y rutas relativas con o sin subcarpeta.
         * Ejemplos válidos: 'dragon-ignis', 'dragon-ignis.svg', 'amigurumis/dragon-ignis'
         */
        public static function getPath(string $name): ?string {
            $base = self::getBaseDir();
            $cleanName = ltrim(trim($name), '/\\');
            
            // Asegurar extensión .svg
            if (!str_ends_with(strtolower($cleanName), '.svg')) {
                $cleanName .= '.svg';
            }

            // 1. Probar ruta directa si ya incluye subdirectorio o está en la raíz
            $directPath = $base . DIRECTORY_SEPARATOR . $cleanName;
            if (file_exists($directPath) && is_readable($directPath)) {
                return $directPath;
            }

            // 2. Probar en las subcarpetas temáticas registradas
            $filenameOnly = basename($cleanName);
            foreach (self::$searchPaths as $sub) {
                if ($sub === '') {
                    continue;
                }
                $testPath = $base . DIRECTORY_SEPARATOR . $sub . $filenameOnly;
                if (file_exists($testPath) && is_readable($testPath)) {
                    return $testPath;
                }
            }

            return null;
        }

        /**
         * Comprueba si un SVG existe en el sistema de assets.
         */
        public static function exists(string $name): bool {
            return self::getPath($name) !== null;
        }

        /**
         * Resuelve la URL web relativa para usar en etiquetas <img src="..."> o background-image CSS.
         * Ejemplo: 'dragon-ignis' -> 'assets/svg/amigurumis/dragon-ignis.svg'
         */
        public static function url(string $name): string {
            $absolutePath = self::getPath($name);
            if ($absolutePath === null) {
                return 'assets/svg/' . ltrim($name, '/');
            }

            $base = self::getBaseDir();
            $relative = str_replace([$base, '\\'], ['', '/'], $absolutePath);
            return 'assets/svg' . (str_starts_with($relative, '/') ? $relative : '/' . $relative);
        }

        /**
         * Renderiza el contenido SVG inline inyectando atributos HTML de forma segura.
         * 
         * @param string $name Nombre del archivo o slug (ej. 'dragon-ignis', 'tools/ovillo-lana')
         * @param array<string, mixed> $attributes Atributos HTML a inyectar en la etiqueta raíz <svg>
         * @return string Contenido SVG listo para imprimir en HTML
         */
        public static function render(string $name, array $attributes = []): string {
            $filePath = self::getPath($name);

            if ($filePath === null) {
                return sprintf('<!-- [SvgHelper] Archivo SVG no encontrado: "%s" -->', htmlspecialchars($name, ENT_QUOTES, 'UTF-8'));
            }

            // Cargar desde caché en memoria o leer de disco
            if (!isset(self::$cache[$filePath])) {
                $content = file_get_contents($filePath);
                if ($content === false) {
                    return sprintf('<!-- [SvgHelper] Error al leer archivo SVG: "%s" -->', htmlspecialchars($name, ENT_QUOTES, 'UTF-8'));
                }
                // Eliminar declaraciones XML innecesarias para renderizado inline (ej. <?xml ...)
                $content = preg_replace('/<\?xml[^>]*\?>/i', '', $content);
                self::$cache[$filePath] = trim((string)$content);
            }

            $svg = self::$cache[$filePath];

            // Si no se pasaron atributos, retornar el SVG limpio
            if (empty($attributes)) {
                return $svg;
            }

            // Inyectar o fusionar atributos en la etiqueta raíz <svg ...>
            return self::injectAttributes($svg, $attributes);
        }

        /**
         * Inyecta y fusiona atributos en la etiqueta <svg> de apertura.
         */
        protected static function injectAttributes(string $svg, array $attributes): string {
            return (string)preg_replace_callback('/<svg\b([^>]*)>/i', function ($matches) use ($attributes) {
                $existingAttrsString = $matches[1];
                $mergedAttrs = self::parseAttributes($existingAttrsString);

                foreach ($attributes as $key => $value) {
                    $key = strtolower(trim((string)$key));

                    if ($value === null || $value === false) {
                        unset($mergedAttrs[$key]);
                        continue;
                    }

                    if ($value === true) {
                        $value = 'true';
                    }

                    $valueStr = (string)$value;

                    // Si es la clase 'class', concatenar con clases existentes en vez de sobrescribir
                    if ($key === 'class' && isset($mergedAttrs['class'])) {
                        $existingClasses = explode(' ', trim($mergedAttrs['class']));
                        $newClasses = explode(' ', trim($valueStr));
                        $allClasses = array_unique(array_filter(array_merge($existingClasses, $newClasses)));
                        $mergedAttrs['class'] = implode(' ', $allClasses);
                    } else {
                        $mergedAttrs[$key] = $valueStr;
                    }
                }

                // Reconstruir atributos en formato HTML
                $renderedAttrs = [];
                foreach ($mergedAttrs as $attrName => $attrVal) {
                    $renderedAttrs[] = sprintf(
                        '%s="%s"',
                        htmlspecialchars($attrName, ENT_QUOTES, 'UTF-8'),
                        htmlspecialchars($attrVal, ENT_QUOTES, 'UTF-8')
                    );
                }

                return '<svg ' . implode(' ', $renderedAttrs) . '>';
            }, $svg, 1);
        }

        /**
         * Parsea la cadena de atributos de una etiqueta HTML en un array clave-valor.
         * @return array<string, string>
         */
        protected static function parseAttributes(string $attrString): array {
            $attributes = [];
            $pattern = '/([a-zA-Z0-9_\-:]+)\s*=\s*([\'"])([^\'"]*)\2/';
            if (preg_match_all($pattern, $attrString, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $attributes[strtolower($match[1])] = $match[3];
                }
            }
            return $attributes;
        }

        /**
         * Lista todos los archivos SVG disponibles organizados por categoría temática.
         * @return array<string, array<int, array{file: string, slug: string, full_slug: string, url: string}>>
         */
        public static function listAll(): array {
            $base = self::getBaseDir();
            $result = [];

            if (!is_dir($base)) {
                return $result;
            }

            $categories = ['branding', 'creaciones', 'piezas', 'tools', 'badges', 'decorations'];
            foreach ($categories as $cat) {
                $catDir = $base . DIRECTORY_SEPARATOR . $cat;
                $result[$cat] = [];

                if (is_dir($catDir)) {
                    $files = scandir($catDir);
                    if ($files !== false) {
                        foreach ($files as $file) {
                            if (str_ends_with(strtolower($file), '.svg')) {
                                $slug = substr($file, 0, -4);
                                $result[$cat][] = [
                                    'file' => $file,
                                    'slug' => $slug,
                                    'full_slug' => $cat . '/' . $slug,
                                    'url' => 'assets/svg/' . $cat . '/' . $file
                                ];
                            }
                        }
                    }
                }
            }

            return $result;
        }

        /**
         * Vacía la caché en memoria (útil durante desarrollo o pruebas).
         */
        public static function clearCache(): void {
            self::$cache = [];
        }
    }

}

// =============================================================================
// FUNCIONES GLOBALES HELPER (Fácil y Rápida Implementación en Vistas PHP)
// =============================================================================

namespace {

    if (!function_exists('svg')) {
        /**
         * Renderiza un archivo SVG inline de la biblioteca assets/svg/ con atributos opcionales.
         * 
         * Uso básico:
         *   <?= svg('dragon-ignis') ?>
         * 
         * Con clases o dimensiones:
         *   <?= svg('dragon-ignis', ['class' => 'card-product-img', 'width' => 240]) ?>
         * 
         * Con ruta explícita:
         *   <?= svg('tools/ovillo-lana', ['class' => 'icon-spin']) ?>
         * 
         * @param string $name Nombre o slug del SVG (con o sin subcarpeta, con o sin extensión .svg)
         * @param array<string, mixed> $attributes Atributos HTML a inyectar en <svg>
         * @return string Código SVG renderizado
         */
        function svg(string $name, array $attributes = []): string {
            return \App\Utils\SvgHelper::render($name, $attributes);
        }
    }

    if (!function_exists('svg_url')) {
        /**
         * Obtiene la ruta URL web relativa de un SVG para etiquetas <img src="..."> o CSS.
         * 
         * Uso:
         *   <img src="<?= svg_url('dragon-ignis') ?>" alt="Dragón Ignis">
         * 
         * @param string $name Nombre o slug del SVG
         * @return string Ruta relativa (ej. 'assets/svg/amigurumis/dragon-ignis.svg')
         */
        function svg_url(string $name): string {
            return \App\Utils\SvgHelper::url($name);
        }
    }

}
