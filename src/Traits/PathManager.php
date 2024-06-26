<?php

namespace Nobir\MiniWizard\Traits;

use Illuminate\Support\Facades\File;

trait PathManager
{
    use ModuleKeys;

    const config_path_pakage = __DIR__ . '/../bootstrap/config.php';

    public static function stubPathDir(){
        return base_path('nobir/mini-wizard/stubs');
    }

    public static function getStubPath($module)
    {
        $defaultStubs = (include(self::config_path_pakage))['stubs_paths'];
        $configStubs = config('mini-wizard.stubs_paths', []);
        return $configStubs[$module] ?? $defaultStubs[$module];
    }

    public static function getModulePath($module)
    {
        $defaultPaths = (include(self::config_path_pakage))['paths'];
        $configPaths = config('mini-wizard.paths', []);
        $path_suffix = $configPaths[$module] ?? $defaultPaths[$module];

        switch ($module) {
            case self::MIGRATION:
                $path = database_path('migrations/' . $path_suffix);
                self::directoryCheck($path);
                return $path;
            case self::MODEL:
                $path = app_path('Models/'.$path_suffix);
                self::directoryCheck($path);
                return $path;
            case self::SEEDER:
                $path = database_path('seeders/'.$path_suffix);
                self::directoryCheck($path);
                return $path;
            case self::FACTORY:
                $path = database_path('factories/'.$path_suffix);
                self::directoryCheck($path);
                return $path;
            case self::CONTROLLER:
                $path = app_path('http/Controllers/'.$path_suffix);
                self::directoryCheck($path);
                return $path;
            case self::SERVICE_CLASS:
                $path = app_path('ServiceClass/'.$path_suffix);
                self::directoryCheck($path);
                return $path;
            case self::REQUESTS:
                $path = app_path('http/Requests/'.$path_suffix);
                self::directoryCheck($path);
                return $path;
            case self::VIEW:
                $path = resource_path('views/'.$path_suffix);
                self::directoryCheck($path);
                return $path;
        }
    }

    public static function getModuleNamespaceOrFolder($module)
    {
        $defaultPaths = (include(self::config_path_pakage))['paths'];
        $configPaths = config('mini-wizard.paths', []);
        $suffix = $configPaths[$module] ?? $defaultPaths[$module];

        switch ($module) {
            case self::MIGRATION:
                return $suffix;
            case self::MODEL:
                $namesapce = app_path('Models/' . $namesapce_suffix);
                return $namesapce;
            case self::SEEDER:
                $namesapce = database_path('seeders/' . $namesapce_suffix);
                return $namesapce;
            case self::FACTORY:
                $namesapce = database_path('factories/' . $namesapce_suffix);
                return $namesapce;
            case self::CONTROLLER:
                $namesapce = app_path('http/Controllers/' . $namesapce_suffix);
                return $namesapce;
            case self::SERVICE_CLASS:
                $namesapce = app_path('ServiceClass/' . $namesapce_suffix);
                return $namesapce;
            case self::REQUESTS:
                $namesapce = app_path('http/Requests/' . $namesapce_suffix);
                return $namesapce;
            case self::VIEW:
                return $suffix;
        }
    }

    public static function directoryCheck($path){
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
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
