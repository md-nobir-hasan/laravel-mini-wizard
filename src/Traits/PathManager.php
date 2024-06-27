<?php

namespace Nobir\MiniWizard\Traits;

use Illuminate\Support\Facades\File;

trait PathManager
{
    use ModuleKeys;

    const pakage_root_path = __DIR__ . '/..';
    const config_path_pakage = __DIR__ . '/../bootstrap/config.php';

    public static function stubDirPath(){
        return base_path('nobir/mini-wizard/stubs');
    }

    public static function getStubFilePath($module)
    {
        $defaultStubFilePath = self::pakage_root_path. '/template/stubs/'.$module.'.stub';
        $StubFilePath = self::stubDirPath().'/'.$module.'.stub';
       if(file_exists($StubFilePath)){
        return $StubFilePath;
       }else{
        return $defaultStubFilePath;
       }
    }

    public static function getModulePath($module,$fileName=null)
    {
        $defaultPaths = (include(self::config_path_pakage))['paths'];
        $configPaths = config('mini-wizard.paths', []);
        $path_suffix = $configPaths[$module] ?? $defaultPaths[$module];
        $configPaths = config('mini-wizard.paths', []);
        $path_suffix = $configPaths[$module] ?? $defaultPaths[$module];

        switch ($module) {
            case self::MIGRATION:
                $path = database_path('migrations/' . $path_suffix);
                self::directoryCheck($path);
                break;
            case self::MODEL:
                $path = app_path('Models/'.$path_suffix);
                self::directoryCheck($path);
                break;
            case self::SEEDER:
                $path = database_path('seeders/'.$path_suffix);
                self::directoryCheck($path);
                break;
            case self::FACTORY:
                $path = database_path('factories/'.$path_suffix);
                self::directoryCheck($path);
                break;
            case self::CONTROLLER:
                $path = app_path('http/Controllers/'.$path_suffix);
                self::directoryCheck($path);
                break;
            case self::SERVICE_CLASS:
                $path = app_path('ServiceClass/'.$path_suffix);
                self::directoryCheck($path);
                break;
            case self::REQUESTS:
                $path = app_path('http/Requests/'.$path_suffix);
                self::directoryCheck($path);
                break;
            case self::VIEW:
                $path = resource_path('views/'.$path_suffix);
                self::directoryCheck($path);
                break;
        }
        if($fileName){
            $path = $path.'/'. $fileName;
        }
        return $path;

    }

    public static function getModuleNamespace($module)
    {
        $defaultPaths = (include(self::config_path_pakage))['paths'];
        $configPaths = config('mini-wizard.paths', []);
        $suffix = $configPaths[$module] ?? $defaultPaths[$module];
        if($suffix){
            $suffix = '\\'.$suffix;
        }
        switch ($module) {
            case self::MODEL:
                $namesapce = 'App\Models' . $suffix;
               break;
            case self::SEEDER:
                $namesapce = 'Database\Seeders' . $suffix;
               break;
            case self::FACTORY:
                $namesapce = 'Database\Factories' . $suffix;
               break;
            case self::CONTROLLER:
                $namesapce = 'App\Http\Controllers' . $suffix;
               break;
            case self::SERVICE_CLASS:
                $namesapce = 'App\Services' . $suffix;
               break;
            case self::REQUESTS:
                $namesapce = 'App\Http\Requests' . $suffix;
               break;
        }
        return $namesapce;
    }

    public static function directoryCheck($path){
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    public static function getConfigFilePath()
    {
        $defaultConfigFilePath = self::pakage_root_path . '/bootstrap/config.php';
        $configFilePath = config_path('mini-wizard.php');
        if (file_exists($configFilePath)) {
            return $configFilePath;
        } else {
            return $defaultConfigFilePath;
        }
    }

    public static function fileCheck($file_path){
        if(File::exists($file_path)){
            if (ConsoleHelper::confirm("The file {$file_path} already exists. Do you want to overwrite it?", true)) {
                // Overwrite the file
                return true;
            } else {
                // Skip the file
                return false;
            }
        }
        return true;
    }
}
