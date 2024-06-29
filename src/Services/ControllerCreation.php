<?php

namespace Nobir\MiniWizard\Services;

use Illuminate\Support\Facades\File;

class ControllerCreation extends BaseCreation
{

    protected $route_info;
    /**
     * Array will be
     *
     * $routes_info = [
     *    group_name => '',
    *     group_middleware => '', //(this middleware for the main route)
    *     middleware => '', //(this middleware for the main route)
    *     is_resource => '',
    *     general_routes => [
    *        [url=>'',name=>'',route_method=>'',controller_method=>'','middleware'=>'']
    *        ..........................
    *        ..........................
    *    ]
    * ]
    */


    public function generate()
    {
        /**
         * logic based on mini-wizard published or not yet
         */
        if($this->route_info['is_resource']){
            $this->generateResourceController();
            return true;
        }


        /**
         * Include the mini-wizard.php in web.php in not
         */
        $web_path = base_path('routes/web.php');

        FileModifier::getContent($web_path)->searchingText("mini-wizard.php")
            ->ifNotExist()->searchingText('<?php')->insertAfter()->insertingText("\nrequire __DIR__ . '/mini-wizard.php';")
            ->save();

        return true;
    }

    protected function generateResourceController()
    {
        // Derive controller name from model name
        $file_name = $this->model_name . 'Controller.php';

        //derive get content path (the stub file for the resource controller)
        $get_content_path = self::getStubFilePath(self::RESOURCE_CONTROLLER);

        //derive the put content path which is the target controller
        $put_content_path = self::getModulePath(self::CONTROLLER, $file_name);

        //if the file exist overright or not
        if (self::fileOverwriteOrNot($put_content_path)) {

            /**
             * preparation of of the dynamic values for the resource controller
             */
            //prepare the namespace

            //Own namspace
            $name_space = self::getModuleNamespace(self::CONTROLLER);

            //request namspace
            $request_namespace = self::getModuleNamespace(self::REQUESTS);

            //Own namspace
            $model_namespace = self::getModuleNamespace(self::MODEL);

            //Own namspace
            $service_class_namespace = self::getModuleNamespace(self::SERVICE_CLASS);

            //geting model name
            $model_name = $this->model_name;

            //view directory path preparation
            $view_dir_path = $this->viewDirPathPrepare();


            //Base route preparation
            $route_name = $this->BaseRouteNamePrepare();

            //file creation
            FileModifier::getContent($get_content_path)
                ->searchingText('{{name_space}}')->replace()->insertingText($name_space)
                ->searchingText('{{request_namespace}}')->replace()->insertingText($request_namespace)
                ->searchingText('{{model_namespace}}')->replace()->insertingText($model_namespace)
                ->searchingText('{{service_class_namespace}}')->replace()->insertingText($service_class_namespace)
                ->searchingText('{{model_name}}')->replace()->insertingText($model_name)
                ->searchingText('{{view_dir_path}}')->replace()->insertingText($view_dir_path)
                ->searchingText('{{route_name}}')->replace()->insertingText($route_name)
                ->save($put_content_path);
            echo 'Resource Controller created successfully';
            return true;
        }
        echo 'Skiped Resource Controller creation';
        return true;
    }

    public function parameterPass($route_info)
    {
        $this->route_info = $route_info;
        $this->generate();
    }

    protected function viewDirPathPrepare()
    {
        $view = '';
        if($suffix = self::getModuleSuffix(self::VIEW)){
            $view .= $suffix.'.';
        }
        if($group_name = $this->route_info['group_name']){
            $view .= $group_name.'.';
        }
        return $view;
    }

    protected function BaseRouteNamePrepare()
    {
        $base_route_name = '';
        if($suffix = self::getModuleSuffix(self::ROUTE)){
            $base_route_name .= $suffix.'.';
        }
        if($group_name = $this->route_info['group_name']){
            $base_route_name .= $group_name.'.';
        }
        return $base_route_name;
    }
}
