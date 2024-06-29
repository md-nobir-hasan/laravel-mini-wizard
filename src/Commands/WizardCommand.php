<?php

namespace Nobir\MiniWizard\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Nobir\MiniWizard\Services\AllFunctionalityClass;
use Nobir\MiniWizard\Services\FileModifier;
use Nobir\MiniWizard\Traits\ModuleKeys;
use Nobir\MiniWizard\Traits\PathManager;
use Nobir\MiniWizard\Traits\StringManipulation;

class WizardCommand extends Command
{
    use PathManager, StringManipulation;
    protected $signature = 'nobir:wizard {model}';
    protected $description = 'Generate a complete set of files for a given model';

    protected $model_class_name;
    protected $models_name = [];
    protected $fields = [];

    protected $data_type_functions = [
        'stop' => [],
        'bigIncrements' => ['autoIncrement', 'unique', 'primary', 'unsigned'],
        'bigInteger' => ['autoIncrement', 'unique', 'primary', 'unsigned', 'nullable', 'default'],
        'binary' => ['nullable', 'default'],
        'boolean' => ['default'],
        'char' => ['length', 'nullable', 'default', 'unique'],
        'dateTimeTz' => ['nullable', 'default'],
        'dateTime' => ['nullable', 'default'],
        'date' => ['nullable', 'default'],
        'decimal' => ['total', 'places', 'nullable', 'default', 'unsigned'],
        'double' => ['total', 'places', 'nullable', 'default', 'unsigned'],
        'enum' => ['default', 'nullable'],
        'float' => ['total', 'places', 'nullable', 'default', 'unsigned'],
        'foreignId' => ['nullable', 'constrained', 'cascadeOnDelete', 'cascadeOnUpdate', 'restrictOnDelete', 'restrictOnUpdate'],
        'foreignIdFor' => ['nullable', 'constrained', 'cascadeOnDelete', 'cascadeOnUpdate', 'restrictOnDelete', 'restrictOnUpdate'],
        'foreignUlid' => ['nullable', 'constrained', 'cascadeOnDelete', 'cascadeOnUpdate', 'restrictOnDelete', 'restrictOnUpdate'],
        'foreignUuid' => ['nullable', 'constrained', 'cascadeOnDelete', 'cascadeOnUpdate', 'restrictOnDelete', 'restrictOnUpdate'],
        'geography' => ['nullable', 'default'],
        'geometry' => ['nullable', 'default'],
        'id' => ['autoIncrement', 'unique', 'primary', 'unsigned'],
        'increments' => ['autoIncrement', 'unique', 'primary', 'unsigned'],
        'integer' => ['autoIncrement', 'unique', 'primary', 'unsigned', 'nullable', 'default'],
        'ipAddress' => ['nullable', 'default', 'unique'],
        'json' => ['nullable', 'default'],
        'jsonb' => ['nullable', 'default'],
        'longText' => ['nullable'],
        'macAddress' => ['nullable', 'default', 'unique'],
        'mediumIncrements' => ['autoIncrement', 'unique', 'primary', 'unsigned'],
        'mediumInteger' => ['autoIncrement', 'unique', 'primary', 'unsigned', 'nullable', 'default'],
        'mediumText' => ['nullable'],
        'morphs' => ['nullable', 'index'],
        'nullableMorphs' => ['index'],
        'nullableTimestamps' => [],
        'nullableUlidMorphs' => ['index'],
        'nullableUuidMorphs' => ['index'],
        'rememberToken' => [],
        'set' => ['nullable', 'default'],
        'smallIncrements' => ['autoIncrement', 'unique', 'primary', 'unsigned'],
        'smallInteger' => ['autoIncrement', 'unique', 'primary', 'unsigned', 'nullable', 'default'],
        'softDeletesTz' => ['nullable'],
        'softDeletes' => ['nullable'],
        'string' => ['length', 'nullable', 'default', 'unique'],
        'text' => ['nullable'],
        'timeTz' => ['nullable', 'default'],
        'time' => ['nullable', 'default'],
        'timestampTz' => ['nullable', 'default'],
        'timestamp' => ['nullable', 'default'],
        'timestampsTz' => [],
        'timestamps' => [],
        'tinyIncrements' => ['autoIncrement', 'unique', 'primary', 'unsigned'],
        'tinyInteger' => ['autoIncrement', 'unique', 'primary', 'unsigned', 'nullable', 'default'],
        'tinyText' => ['nullable'],
        'unsignedBigInteger' => ['autoIncrement', 'unique', 'primary', 'nullable', 'default'],
        'unsignedInteger' => ['autoIncrement', 'unique', 'primary', 'nullable', 'default'],
        'unsignedMediumInteger' => ['autoIncrement', 'unique', 'primary', 'nullable', 'default'],
        'unsignedSmallInteger' => ['autoIncrement', 'unique', 'primary', 'nullable', 'default'],
        'unsignedTinyInteger' => ['autoIncrement', 'unique', 'primary', 'nullable', 'default'],
        'ulidMorphs' => ['nullable', 'index'],
        'uuidMorphs' => ['nullable', 'index'],
        'ulid' => ['nullable', 'default', 'unique'],
        'uuid' => ['nullable', 'default', 'unique'],
        'year' => ['nullable', 'default']
    ];

    public function handle()
    {
        //Store model class name
        $this->model_class_name = self::mdoelNameFormat($this->argument('model'));

        //bootstraping the mini-wizard
        $this->bootstrap();

        //fields collection and making an array
        $this->collectFields();

        //filtering the collected data
        $this->dataFilter();

        //wizard functionality call all together or sequencely and see the mystery
        $this->wizard();
    }

    protected function collectFields()
    {
        while (true) {
            $type = $this->choice('Choose the data type', array_keys($this->data_type_functions));

            if ($type == 'stop') {
                break;
            }

            $fieldNameValues = $this->getFieldName($type);
            $fieldName = $fieldNameValues['fname'];
            if (isset($fieldNameValues['values'])) {
                $fieldData = [$type => $fieldNameValues['values']];
            } else {
                $fieldData = [$type];
            }

            $options = $this->data_type_functions[$type];
            foreach ($options as $option) {
                $dataTypeProperty = $this->dataTypeProperty($option, $type);
                if ($dataTypeProperty) {
                    if ($dataTypeProperty === true) {
                        $fieldData[] = $option;
                    } else {
                        $fieldData[$option] = $dataTypeProperty;
                    }
                }
            }

            $this->fields[$fieldName] = $fieldData;
        }
    }

    protected function getFieldName($type)
    {
        if ($type === 'foreignIdFor') {
            $modelClass = self::mdoelNameFormat($this->ask("Enter the related model name for $type")); //we transfer it to field name when we need
            array_push($this->models_name, $modelClass);
            return ['fname' => self::modelToForeignKey($modelClass)];
        } elseif (in_array($type, ['enum', 'set'])) {
            $fieldName = $this->ask("Enter the field name for $type");
            $fieldvlaue = explode(',', $this->ask("Enter the values for $type (comma separated)"));
            return ['fname' => $fieldName, 'values' => $fieldvlaue];
        } else {
            return ['fname' => $this->ask("Enter the field name")];
        }
    }

    protected function dataTypeProperty($option, $type)
    {
        switch ($option) {
            case 'default':
            case 'length':
            case 'total':
            case 'places':
                return $this->ask("Enter the $option value for $type");
            case 'unsigned':
            case 'constrained':
            case 'cascadeOnDelete':
            case 'cascadeOnUpdate':
                return $this->confirm("Is this field $option?", true);
            case 'nullable':
            case 'primary':
            case 'unique':
            case 'autoIncrement':
            case 'index':
            case 'restrictOnDelete':
            case 'restrictOnUpdate':
                return $this->confirm("Is this field $option?", false);
            default:
                return null;
        }
    }
    protected function dataFilter()
    {
        $this->fields = array_filter($this->fields);
        $this->models_name = array_filter($this->models_name);
    }
    protected function bootstrap()
    {
        if (!file_exists(config_path('mini-wizard.php'))) {
            Artisan::call('vendor:publish', [
                '--tag' => 'wizard-config',
            ]);
            echo Artisan::output();
        }
        // if(!file_exists(self::getModulePath(self::MODEL) . '/NSidebar.php')){
        //     Artisan::call('vendor:publish', [
        //         '--tag' => 'wizard-sidebar',
        //     ]);
        //     echo Artisan::output();
        // }

        // if(!is_dir((self::stubPathDir()))){
        //     Artisan::call('vendor:publish', [
        //         '--tag' => 'wizard-stubs',
        //     ]);
        //     echo Artisan::output();
        // }
    }

    protected function wizard()
    {

        $allFunctionality = new AllFunctionalityClass($this->fields, $this->model_class_name, $this->models_name);



        //Model creation
        // if ($this->confirm('Do you want to create the model?', true)) {
        //     $allFunctionality->createModel();
        // }




        // //migration creation
        // if ($this->confirm('Do you want to create the migration?', true)) {
        //     $allFunctionality->createMigration();
        // }




        // //seeder creation
        // if ($this->confirm('Do you want to create the seeder?', true)) {
        //     $allFunctionality->createSeeder();
        // }




        // //factory creation
        // if ($this->confirm('Do you want to create the factory?', true)) {
        //     $allFunctionality->createFactory();
        // }





        /**
         *  requests creation
         * */
        // if ($this->confirm('Do you want to create the requests?', true)) {
        //     $allFunctionality->createRequests();
        // }




        /**
         *  Service class for controller creation
         * */
        // if ($this->confirm('Do you want to create the service class for controller?', true)) {
        //     $allFunctionality->createServiceClass();
        // }




        /**
         *  Route creation for the module
         * */
        if ($this->confirm('Do you want to create the route  for the module?', true)) {

            /**
             * Array will be
             *
             * $routes_info = [
             *                  group_name => '',
             *                  group_middleware => '', //(this middleware for the main route)
             *                  middleware => '', //(this middleware for the main route)
             *                  is_resource => '',
             *                  general_routes => [
             *                         [url=>'',name=>'',route_method=>'',controller_method=>'','middleware'=>'']
             *                          ..........................
             *                          ..........................
             *                  ]
             *                 ]
             */
            $route_info = $this->routeInfoCollection();

            $allFunctionality->createRoute($route_info);
        }





        /**
         *  Controller Creation
         * */
        if ($this->confirm('Do you want to create Controller?', true)) {
            $allFunctionality->createController($route_info);
        }


    }


    public function routeInfoCollection()
    {

        //route array
        $route_info = ['group_name' => '', 'group_middleware' => '', 'is_resource' => true, 'middleware'=>'', 'general_routes' => ''];

        //Route group preparation
        $route_group_name = $this->ask('Enter route group (press enter to skip)',);

        $route_info['group_name'] = $route_group_name;

        if ($route_group_name) {
            $middleware = $this->ask('Enter middleware for the group (press enter to skip)');
            $route_info['group_middleware'] = $middleware;
        }

        //Route type choice
        $route_type = $this->choice('Are you want to create resource route or general route', ['resource route', 'generale route']);

        if ($route_type == 'resource route') {
            //set the is_resource value false
            $route_info['is_resource'] = true;

            //sometimes it need to validate the target route
            $middleware = $this->ask('Enter middleware for the resource route');
            $route_info['middleware'] = $middleware;
        }
        //In case of generale route
        else {

            //set the is_resource value false
            $route_info['is_resource'] = false;

            //general route's url, name, method, controller method collection
            $general_route_info = [];

            // $general_route['url'] = $this->ask('Enter general route url');
            // $general_route['name'] = $this->ask('Enter general route name ');
            // $general_route['route_method'] = $this->ask('Enter general route method');
            // $general_route['controller_method'] = $this->ask('Enter general route middlerware (press enter to skip)');

            // $general_route_info[] = $general_route;
            $i = 1;
            while (true) {
                /**
                 *  general route's url collection
                 */
                //at first one route url have to provide
                if ($i == 1) {
                    $general_route_url_first = $this->ask('Enter general route url');
                    if (!$general_route_url_first) {
                        continue;
                    }
                    $general_route_url = $general_route_url_first;
                } else {
                    $general_route_url = $this->ask('Enter general route url (press enter to skip)');
                }

                /**
                 * Stop execution in the basis of empty url
                 */
                if (!$general_route_url) {
                    break;
                }

                $general_route['url'] = $general_route_url;


                /**
                 *  general route's name collection
                 */
                $general_route_name = $this->ask("Enter general route name for url '$general_route_url'");
                if (!$general_route_name) {
                    //contineous looping if route not exist
                    while (true) {
                        $general_route_name = $this->ask("Enter general route name for url '$general_route_url'");
                        if ($general_route_name) {
                            break;
                        }
                    }
                }
                $general_route['name'] = $general_route_name;
                $i++;

                /**
                 *  general route's method collection
                 */
                $general_route_method = $this->choice("Enter general route method for url '$general_route_url'", ['get', 'post', 'put', 'delete', 'patch']);
                $general_route['route_method'] = $general_route_method;


                /**
                 *  general route's controller method collection
                 */
                $general_route_controller_method = $this->ask("Enter controller method for the route '$general_route_url'");

                if (!$general_route_controller_method) {

                    //contineous looping if  not exist
                    while (true) {
                        $general_route_controller_method = $this->ask("Enter controller method for the route '$general_route_url'");

                        if ($general_route_controller_method) {
                            break;
                        }
                    }
                }
                $general_route['controller_method'] = $general_route_controller_method;

                /**
                 * general routes middleware
                 */

                //sometimes it need to validate the target route
                $middleware = $this->ask("Enter middleware for the route '$general_route_url' (press enter to skip)");
                $general_route['middleware'] = $middleware;

                /**
                 * push the array in the route info array
                 */
                $general_route_info[] = $general_route;
            }

            //add general routes info to main route
            $route_info['general_routes'] = $general_route_info;
        }




        return $route_info;
    }
}
