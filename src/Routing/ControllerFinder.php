<?php

namespace PhpDevCommunity\Michel\Core\Routing;

use PhpDevCommunity\Michel\Core\Controller\Controller;

final class ControllerFinder
{
    private array $sources = [];
    private ?string $cacheDir;

    public function __construct(array $sources, ?string $cacheDir = null)
    {
        foreach ($sources as $source) {
            if (!is_dir($source) && !class_exists($source)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The source "%s" does not exist or is not a directory.',
                        $source
                    )
                );
            }
            $this->sources[] = $source;
        }
        $this->cacheDir = $cacheDir;
        if ($this->cacheDir && !is_dir($this->cacheDir)) {
            throw  new \InvalidArgumentException(sprintf(
                'Cache directory "%s" does not exist',
                $this->cacheDir
            ));
        }
    }

    public function findControllerClasses(): array
    {
        $classes = [];
        foreach ($this->sources as $source) {
            if (class_exists($source, true) && is_subclass_of($source, Controller::class)) {
                $classes[] = $source;
                continue;
            }

            $classes = array_merge($classes, $this->findControllerClassesInDir($source));
        }

        return array_unique($classes);
    }


    private function findControllerClassesInDir(string $directory): array
    {
        if ($this->cacheIsEnabled()) {
            $cacheFile = $this->getCacheFile($directory);
            if (is_file($cacheFile)) {
                return require $cacheFile;
            }
        }

        $classes = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $className = self::extractNamespaceAndClass($file->getPathname());
            if ($className && class_exists($className, true) && is_subclass_of($className, Controller::class)) {
                $classes[] = $className;
            }
        }

        $classes = array_values($classes);
        if ($this->cacheIsEnabled()) {
            $content = "<?php\n\nreturn " . var_export($classes, true) . ";\n";
            file_put_contents($this->getCacheFile($directory), $content);
        }
        return $classes;
    }

    private function cacheIsEnabled(): bool
    {
        return $this->cacheDir !== null;
    }
    private function getCacheFile(string $dir): string
    {
        return rtrim($this->cacheDir, '/') . '/' . md5($dir) . '.php';
    }

    private static function extractNamespaceAndClass(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('File not found: ' . $filePath);
        }

        $contents = file_get_contents($filePath);
        $namespace = '';
        $class = '';
        $isExtractingNamespace = false;
        $isExtractingClass = false;

        foreach (token_get_all($contents) as $token) {
            if (is_array($token) && $token[0] == T_NAMESPACE) {
                $isExtractingNamespace = true;
            }

            if (is_array($token) && $token[0] == T_CLASS) {
                $isExtractingClass = true;
            }

            if ($isExtractingNamespace) {
                if (is_array($token) && in_array($token[0], [T_STRING, T_NS_SEPARATOR,  265 /* T_NAME_QUALIFIED For PHP 8*/])) {
                    $namespace .= $token[1];
                } else if ($token === ';') {
                    $isExtractingNamespace = false;
                }
            }

            if ($isExtractingClass) {
                if (is_array($token) && $token[0] == T_STRING) {
                    $class = $token[1];
                    break;
                }
            }
        }
        return $namespace ? $namespace . '\\' . $class : $class;
    }

}
