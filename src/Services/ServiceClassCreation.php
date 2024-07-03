<?php

namespace Nobir\MiniWizard\Services;

use Illuminate\Support\Facades\File;

class ServiceClassCreation extends BaseCreation
{
    public function generate()
    {
        /**
         * Service class created if not exists
         */
        $this->parentServiceClassCheck();

        // Derive file name from model name
        $FileName = $this->model_name . 'Service.php';

        // path collection
        $file_path = self::getModulePath(self::SERVICE_CLASS, $FileName);

        //overwrite or skip logic if exist the file
        if (self::fileOverwriteOrNot($file_path)) {

            /**
             * dynamic properties preparation
             */

            // Derive file name from model name
            $name_space = self::getModuleNamespace(self::SERVICE_CLASS);

            // The model namespane
            $model_name_space = self::getModuleNamespace(self::MODEL)."\\$this->model_name";

            //Finally the file modification if exist or creation if not exist
            $content = File::get(self::getStubFilePath(self::SERVICE_CLASS));

            $content = str_replace(['{{name_space}}','{{model_name_space}}', '{{model_name}}'],
            [$name_space,$model_name_space,$this->model_name], $content);
            File::put($file_path, $content);
            $this->info('Servide Class created successfully');


            return true;
        }


        $this->info('Skiped Servide Class creation');

        return true;
    }

    protected function parentServiceClassCheck(){

        $services_dir_path = self::getModulePath(self::SERVICE_CLASS);
        $services_dir_namespace = self::getModuleNamespace(self::SERVICE_CLASS);

        self::directoryCreateIfNot($services_dir_path);

        $parent_service_file_path = $services_dir_path. '/Service.php';

        if(!File::exists($parent_service_file_path)){
            FileModifier::getContent(self::getStubFilePath(self::PARENT_SERVICE_CLASS))
                ->searchingText('{{name_space}}')->replace()->insertingText($services_dir_namespace)
                ->save($parent_service_file_path);
            $this->info('Service class created');
        }

    }

}
