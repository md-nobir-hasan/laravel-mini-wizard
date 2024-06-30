<?php

namespace Nobir\MiniWizard\Services;

use Illuminate\Support\Facades\File;

class ViewCreation extends BaseCreation
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
    // protected $routes_info = [
    //     'group_name' => 'setup',
    //     'group_middleware' => 'admin', //(this middleware for the main route)
    //     'middleware' => '', //(this middleware for the main route)
    //     'is_resource' => false,
    //     'general_routes' => [
    //         ['url' => 'index', 'name' => 'index', 'route_method' => 'get', 'controller_method' => 'index', 'middleware' => ''],
    //         ['url' => 'create', 'name' => 'create', 'route_method' => 'get', 'controller_method' => 'create', 'middleware' => ''],
    //         ['url' => 'store', 'name' => 'store', 'route_method' => 'post', 'controller_method' => 'store', 'middleware' => 'auth'],
    //     ]
    // ];


    protected $theme_path;

    public function generate()
    {
        /**
         * Theme seraching
         */
        $theme_name = self::nameInConfig(self::THEME) ?? 'nobir';
        $theme_path = self::stub_path("view/$theme_name");
        if ($theme_path) {
            $this->theme_path = $theme_path;
        } else {
            $this->info('Your providing theme is not found');
            return false;
        }



        /**
         * logic based on routes . is the routes resourece or not
         */
        $this->route_info['is_resource'] ? $this->generateViewForResourceMethod() : $this->generateViewForGeneralMethod();
    }

    protected function generateViewForResourceMethod()
    {

        $this->indexViewCreation();
        // $this->CreateViewCreation();
        // $this->EditViewCreation();


    }


    protected function generateViewForGeneralMethod()
    {
        $this->indexViewCreation();
        // $this->CreateViewCreation();
        // $this->EditViewCreation();

        // Derive controller name from model name
        $file_name = $this->model_name . 'Controller.php';

        //derive get content path (the stub file for the resource controller)
        $get_content_path = self::getStubFilePath(self::CONTROLLER);

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

            /**
             * Slot preparation
             */
            $slot = '';
            foreach ($this->route_info['general_routes'] as $general_route) {
                switch ($general_route['route_method']) {
                    case 'get':
                        $slot .= "\n\n" . FileModifier::getContent(self::getStubFilePath(self::CODE_FOR_GET_METHOD))
                            ->searchingText('{{method}}')->replace()->insertingText($general_route['controller_method'])
                            ->searchingText('{{model_name}}')->replace()->insertingText($model_name)
                            ->searchingText('{{view_dir_path}}')->replace()->insertingText($view_dir_path)
                            // ->searchingText('{{route_name}}')->replace()->insertingText($route_name)
                            // ->searchingText('{{request_namespace}}')->replace()->insertingText($request_namespace)
                            ->gettingContent();
                        break;

                    case 'post':
                        $slot .= "\n\n" . FileModifier::getContent(self::getStubFilePath(self::CODE_FOR_POST_METHOD))
                            ->searchingText('{{method}}')->replace()->insertingText($general_route['controller_method'])
                            ->searchingText('{{request_namespace}}')->replace()->insertingText($request_namespace)
                            ->searchingText('{{model_name}}')->replace()->insertingText($model_name)
                            ->searchingText('{{route_name}}')->replace()->insertingText($route_name)
                            ->gettingContent();
                        break;

                    case 'put':
                        $slot .= "\n\n" . FileModifier::getContent(self::getStubFilePath(self::CODE_FOR_PUT_METHOD))
                            ->searchingText('{{method}}')->replace()->insertingText($general_route['controller_method'])
                            ->searchingText('{{model_name}}')->replace()->insertingText($model_name)
                            ->searchingText('{{view_dir_path}}')->replace()->insertingText($view_dir_path)
                            ->gettingContent();
                        break;

                    case 'put':
                        $slot .= "\n\n" . FileModifier::getContent(self::getStubFilePath(self::CODE_FOR_PUT_METHOD))
                            ->searchingText('{{method}}')->replace()->insertingText($general_route['controller_method'])
                            ->searchingText('{{model_name}}')->replace()->insertingText($model_name)
                            ->searchingText('{{view_dir_path}}')->replace()->insertingText($view_dir_path)
                            ->gettingContent();
                        break;

                    case 'patch':
                        $slot .= "\n\n" . FileModifier::getContent(self::getStubFilePath(self::CODE_FOR_PUT_METHOD))
                            ->searchingText('{{method}}')->replace()->insertingText($general_route['controller_method'])
                            ->searchingText('{{model_name}}')->replace()->insertingText($model_name)
                            ->searchingText('{{view_dir_path}}')->replace()->insertingText($view_dir_path)
                            ->gettingContent();
                        break;

                    case 'delete':
                        $slot .= "\n\n" . FileModifier::getContent(self::getStubFilePath(self::CODE_FOR_DELETE_METHOD))
                            ->searchingText('{{method}}')->replace()->insertingText($general_route['controller_method'])
                            ->searchingText('{{model_name}}')->replace()->insertingText($model_name)
                            ->gettingContent();
                        break;
                }
            }

            //file creation
            FileModifier::getContent($get_content_path)
                ->searchingText('{{name_space}}')->replace()->insertingText($name_space)
                ->searchingText('{{model_namespace}}')->replace()->insertingText($model_namespace)
                ->searchingText('{{service_class_namespace}}')->replace()->insertingText($service_class_namespace)
                ->searchingText('{{model_name}}')->replace()->insertingText($model_name)
                ->searchingText('{{slot}}')->replace()->insertingText($slot)
                ->save($put_content_path);
            $this->info("Generel Controller ({$model_name}Controller) created successfully. method code of this controller userd by GPT please modify the code according to your need");
            return true;
        }
        $this->info("Skiped General Controller creation");
        return true;
    }


    protected function indexViewCreation()
    {
        // Derive controller name from model name
        $file_name = 'index.blade.php';
        $folder_for_group = self::removeAfterBefore(str_replace('.', '/', $this->viewDirPathPrepare())); //assuming backend/setup/ return
        //derive get content path (the stub file for the resource controller)
        $get_content_path = $this->theme_path . '/index.stub';

        //derive the put content path which is the target controller
        $put_content_path = self::getModulePath(self::VIEW, $folder_for_group);
        self::directoryCreateIfNot($put_content_path);

        $put_content_file_path = $put_content_path . "/$file_name";
        //if the file exist overright or not
        if (self::fileOverwriteOrNot($put_content_file_path)) {
            /**
             * preparation of of the dynamic values for the resource controller
             */
            //prepare the namespace

            //Own namspace
            $page_title = str()->headline($this->model_name) . " List";


            //Base route preparation
            $route_name = $this->BaseRouteNamePrepare();

            //file creation
            FileModifier::getContent($get_content_path)
                ->searchingText('{{page_title}}')->replace()->insertingText($page_title)
                ->searchingText('{{route_name}}')->replace()->insertingText($route_name)
                ->save($put_content_file_path);

            $this->info("$file_name file created successfully");

            return true;
        }
        $this->info("Skiped $file_name creation");
        return true;
    }

    protected function createViewCreation()
    {
        // Derive controller name from model name
        $file_name = 'create.blade.php';
        $folder_for_group = self::removeAfterBefore(str_replace('.', '/', $this->viewDirPathPrepare())); //assuming backend/setup/ return
        //derive get content path (the stub file for the resource controller)
        $get_content_path = $this->theme_path . '/create.stub';

        //derive the put content path which is the target controller
        $put_content_path = self::getModulePath(self::VIEW, $folder_for_group);
        self::directoryCreateIfNot($put_content_path);

        $put_content_file_path = $put_content_path . "/$file_name";
        //if the file exist overright or not
        if (self::fileOverwriteOrNot($put_content_file_path)) {
            /**
             * preparation of of the dynamic values for the resource controller
             */
            //prepare the namespace

            //Own namspace
            $page_title = str()->headline($this->model_name) . " List";

            //Base route preparation
            $route_name = $this->BaseRouteNamePrepare();

            //Base route preparation
            $slot = $this->slotCreation();

            //file creation
            FileModifier::getContent($get_content_path)
                ->searchingText('{{page_title}}')->replace()->insertingText($page_title)
                ->searchingText('{{route_name}}')->replace()->insertingText($route_name)
                ->searchingText('{{slot}}')->replace()->insertingText($slot)
                ->save($put_content_file_path);

            $this->info("$file_name file created successfully");

            return true;
        }
        $this->info("Skiped $file_name creation");
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
        if ($group_name = $this->route_info['group_name']) {
            $view .= $group_name . '.';
        }
        return $view;
    }


    protected function BaseRouteNamePrepare()
    {
        $base_route_name = '';
        if ($suffix = self::getModuleSuffix(self::ROUTE)) {
            $base_route_name .= $suffix . '.';
        }
        if ($group_name = $this->route_info['group_name']) {
            $base_route_name .= $group_name . '.';
        }
        return $base_route_name;
    }

    protected function slotCreation(){
        $fields = $this->fields;
    }

    protected function stringfield(){
        
    }
}
