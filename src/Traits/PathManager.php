<?php
namespace Nobir\MiniWizard\Traits;

trait PathManager
{
    public static function getStubPath($module)
    {
        $defaultStubs = [
            'migration' => __DIR__ . '/../stubs/migration.stub',
            'controller' => __DIR__ . '/../stubs/controller.stub',
            'model' => __DIR__ . '/../stubs/model.stub',
            'view' => __DIR__ . '/../stubs/view.stub',
        ];

        $configStubs = config('mini-wizard.stubs', []);

        return $configStubs[$module] ?? $defaultStubs[$module];
    }

    public static function getStoragePath($module)
    {
        $defaultPaths = [
            'migration' => 'database/migrations',
            'controller' => 'app/Http/Controllers',
            'model' => 'app/Models',
            'view' => 'resources/views',
        ];

        $configPaths = config('mini-wizard.paths', []);

        return $configPaths[$module] ?? $defaultPaths[$module];
    }

    public static function resolveNamespace($path)
    {
        $basePath = app_path();
        $namespace = 'App';

        $path = self::normalizePath($path);

        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
            $namespace .= str_replace('/', '\\', $path);
        }

        return $namespace;
    }

    protected static function normalizePath($path)
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }
}
