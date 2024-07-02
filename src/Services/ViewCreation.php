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

        // $this->indexViewCreation();
        $this->CreateViewCreation();
        // $this->EditViewCreation();


    }


    protected function generateViewForGeneralMethod()
    {
        // $this->indexViewCreation();
        $this->CreateViewCreation();
        // $this->EditViewCreation()
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
        /**
         * Preparation of get content file path
         */
        $get_content_path = $this->theme_path . '/create.stub';



        /**
         * Preparation of put content file path
         */

        // file name
        $file_name = 'create.blade.php';

        $folder_for_group = self::removeAfterBefore(str_replace('.', '/', $this->viewDirPathPrepare())); //assuming backend/setup/ return

        //derive the put content path which is the target controller
        $put_content_path = self::getModulePath(self::VIEW, $folder_for_group);

        self::directoryCreateIfNot($put_content_path);

        //finally prepared
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
        $view = 'pages.';

        if ($group_name = $this->route_info['group_name']) {
            $view .= "$group_name.";
        }
        $model_name_as_dir = self::PascalToCabab($this->model_name);
        $view .= "$model_name_as_dir.";

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

        $model_name_as_dir = self::PascalToSnacke($this->model_name);
        $base_route_name .= $model_name_as_dir . '.';

        return $base_route_name;
    }

    protected function slotCreation($is_update=null)
    {
        $fields = $this->fields;
        // dd($fields);
        $slot = '';
        foreach ($fields as $field_name => $field_properties) {
            $input_type = '';
            $i = 0;
            if (strpos($field_name, 'image') !== false) {
                $input_type = $this->dropzoneSingle($field_name);
                $i = 1;
            }

            if (strpos($field_name, 'images') !== false) {
                $input_type = $this->dropzoneMultiple($field_name);
                $i = 1;
            }


            foreach ($field_properties as $key => $value) {
                /**
                 * determining the input types
                 */
                if ($i == 0) {
                    //input types search in key
                    if (in_array($key, ['enum', 'set'])) {
                        $options = [];
                        foreach ($field_properties[$key] as $value) {
                            $option_obj = (object)['id' => $value, 'title' => $value];
                            array_push($options, $option_obj);
                        }
                        // $options = (object) $options;
                        $input_type = $this->select($field_name, $options);
                        continue;
                    }

                    //select input type (use select 2)
                    if ($value == 'foreignIdFor') {
                        $input_type = $this->select2($field_name);
                        continue;
                    }

                    //text input types
                    if (in_array($value, ['longText', 'macAddress', 'mediumText', 'string', 'text', 'tinyText', 'smallInteger', 'tinyInteger', 'ulid', 'uuid'])) {

                        $input_type = $this->textInput($field_name);
                        continue;
                    }

                    //textarea input types
                    if (in_array($value, ['longText', 'mediumText', 'text'])) {
                        $input_type = $this->textarea($field_name);
                        continue;
                    }

                    //number input types
                    if (in_array($value, ['bigInteger', 'decimal', 'double', 'float', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'])) {
                        $input_type = $this->numberInput($field_name);
                        continue;
                    }

                    //boolean input types
                    if (in_array($value, ['boolean'])) {
                        $input_type = $this->checkbox($field_name);
                        continue;
                    }
                }

                $i++;
                break;
            }
            // nullable check input types
            if (!in_array('nullable', $field_properties)) {
                $input_type = str_replace("is_required='0'", '', $input_type);
            }

            //ir update replace according to create or update file
            if(!$is_update){
                $input_type = str_replace("is_update='0'", '', $input_type);
            }

            $slot .= $input_type;
        }
        return $slot;
    }

    protected function select($field_name, $options)
    {
        $title = str()->headline($field_name);
        return "\n{{--  $title --}}\n<x-form.select name='$field_name' options='" . json_encode($options) . "' is_required='0' is_update='0' />";
    }

    protected function select2($field_name)
    {
        $title = str()->headline($field_name);
        $model = self::foreignKeyToModelName($field_name);
        return "\n{{-- $title --}}\n<x-form.select2 name='$field_name' :options='{{\$$model}}' is_required='0' is_update='0' />";
    }

    protected function textInput($field_name)
    {
        $title = str()->headline($field_name);
        return  "\n{{--  $title --}} \n <x-form.text name='$field_name' is_required='0' is_update='0'/>";
    }

    protected function textarea($field_name)
    {
        $title = str()->headline($field_name);
        return  "\n{{--  $title --}} \n <x-form.textarea name='$field_name' is_required='0' is_update='0'/>";
    }

    protected function numberInput($field_name)
    {
        $title = str()->headline($field_name);
        return  "\n{{--  $title --}} \n <x-form.number name='$field_name' is_required='0' is_update='0'/>";
    }

    protected function checkbox($field_name)
    {
        $title = str()->headline($field_name);
        return  "\n{{--  $title --}} \n <x-form.checkbox name='$field_name' is_required='0' is_update='0'/>";
    }

    protected function dropzoneSingle($field_name)
    {
        $title = str()->headline($field_name);
        return  "\n{{--  $title --}} \n <x-form.dropzone-single name='$field_name' is_required='0' is_update='0'/>";
    }

    protected function dropzoneMultiple($field_name)
    {
        $title = str()->headline($field_name);
        return  "\n{{--  $title --}} \n <x-form.dropzone-multiple name='$field_name' is_required='0' is_update='0'/>";
    }
}
