<?php

namespace Nobir\MiniWizard\Services;

use Illuminate\Support\Facades\File;

class RouteCreation extends BaseCreation
{

    protected $route_info;

    public function generate()
    {
        /**
         * logic based on mini-wizard published or not yet
         */

        $this->modifyMiniWizardRouteFile();

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
            $model_name_space = self::getModuleNamespace(self::MODEL) . "\\$this->model_name";

            //Finally the file modification if exist or creation if not exist
            $content = File::get(self::getStubFilePath(self::SERVICE_CLASS));

            $content = str_replace(
                ['{{name_space}}', '{{model_name_space}}', '{{model_name}}'],
                [$name_space, $model_name_space, $this->model_name],
                $content
            );
            File::put($file_path, $content);
            $this->info('Servide Class created successfully');


            return true;
        }


        $this->info('Skiped Servide Class creation');

        return true;
    }

    protected function modifyMiniWizardRouteFile()
    {

        $put_content_path = base_path('routes/mini-wizard.php');
        $get_content_path = $put_content_path;

        if (!File::exists($get_content_path)) {
            $get_content_path = self::getStubFilePath(self::ROUTE);
        }

        if($this->route_info['group']){
            
        }
        FileModifier::getContent($get_content_path)
            ->searchingText('{{slot}}')->replace()->insertingText($services_dir_namespace)
            ->save($mini_wizard_path);
        $this->info('Service class created');
    }

    public function parameterPass($route_info)
    {
        $this->route_info = $route_info;
        $this->generate();
    }
}
