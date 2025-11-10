<?php

namespace Tunr\FluxFontAwesome\Services;

class FontAwesomePathResolver
{
    /**
     * Common Font Awesome installation paths to check.
     *
     * @var array<int, string>
     */
    protected array $commonPaths = [
        'node_modules/@fortawesome/fontawesome-free',
        'node_modules/@fortawesome/fontawesome-pro',
        'node_modules/@fortawesome/pro-solid-svg-icons',
        'vendor/fortawesome/font-awesome',
    ];

    /**
     * Resolve the Font Awesome installation path.
     *
     * Checks in order:
     * 1. Explicit config override
     * 2. Auto-detection of common paths
     * 3. Default fallback
     */
    public function resolve(?string $configPath = null): string
    {
        // If explicitly configured, use that path
        if ($configPath && $this->isValidPath($configPath)) {
            return $configPath;
        }

        // Auto-detect from common installation paths
        $basePath = base_path();

        foreach ($this->commonPaths as $relativePath) {
            $fullPath = $basePath.'/'.$relativePath;

            if ($this->isValidPath($fullPath)) {
                return $fullPath;
            }
        }

        // Return default fallback (even if it doesn't exist yet)
        return $basePath.'/node_modules/@fortawesome/fontawesome-free';
    }

    /**
     * Check if a path is a valid Font Awesome installation.
     */
    protected function isValidPath(string $path): bool
    {
        return is_dir($path) && is_dir($path.'/svgs');
    }

    /**
     * Get the SVGs directory path.
     */
    public function getSvgsPath(?string $configPath = null): string
    {
        return $this->resolve($configPath).'/svgs';
    }

    /**
     * Check if Font Awesome is installed.
     */
    public function isInstalled(?string $configPath = null): bool
    {
        return $this->isValidPath($this->resolve($configPath));
    }

    /**
     * Get all detected Font Awesome installations.
     *
     * @return array<int, array{path: string, type: string}>
     */
    public function getAllInstallations(): array
    {
        $basePath = base_path();
        $installations = [];

        foreach ($this->commonPaths as $relativePath) {
            $fullPath = $basePath.'/'.$relativePath;

            if ($this->isValidPath($fullPath)) {
                $installations[] = [
                    'path' => $fullPath,
                    'type' => $this->detectType($fullPath),
                ];
            }
        }

        return $installations;
    }

    /**
     * Detect if installation is Free or Pro.
     */
    protected function detectType(string $path): string
    {
        if (str_contains($path, 'fontawesome-pro')) {
            return 'pro';
        }

        if (str_contains($path, 'fontawesome-free')) {
            return 'free';
        }

        // Check package.json if exists
        $packageJson = $path.'/package.json';
        if (file_exists($packageJson)) {
            $contents = file_get_contents($packageJson);
            if ($contents === false) {
                return 'free';
            }
            $package = json_decode($contents, true);
            $name = $package['name'] ?? '';

            if (str_contains((string) $name, 'pro')) {
                return 'pro';
            }
        }

        return 'free';
    }
}
