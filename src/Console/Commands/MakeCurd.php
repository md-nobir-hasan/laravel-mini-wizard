<?php

namespace Nobir\CurdByCommand\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class MakeCurd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nobir:curd {model}';

    // The order of these data type is not change able
    protected $data_type = [
        'bigIncrements', 'bigInteger', 'binary', 'boolean', 'char', 'dateTimeTz', 'dateTime', 'date', 'decimal', 'double', 'enum', 'float', 'foreignId',
        'foreignIdFor', 'foreignUlid', 'foreignUuid', 'geography', 'geometry', 'id', 'increments', 'integer', 'ipAddress', 'json', 'jsonb', 'longText',
        'macAddress', 'mediumIncrements', 'mediumInteger', 'mediumText', 'morphs', 'nullableMorphs', 'nullableTimestamps', 'nullableUlidMorphs', 'nullableUuidMorphs',
        'rememberToken', 'set', 'smallIncrements', 'smallInteger', 'softDeletesTz', 'softDeletes', 'string', 'text', 'timeTz', 'time', 'timestampTz',
        'timestamp', 'timestampsTz', 'timestamps', 'tinyIncrements', 'tinyInteger', 'tinyText', 'unsignedBigInteger', 'unsignedInteger', 'unsignedMediumInteger',
        'unsignedSmallInteger', 'unsignedTinyInteger', 'ulidMorphs', 'uuidMorphs', 'ulid', 'uuid', 'year'
    ];
    protected $data = [];
    protected $migration_slot = '';
    protected $pakage_stub_path = __DIR__ . '/../../stubs/';

    //Proterties for model
    protected $model_class_name;
    protected $model_functions = '';
    protected $model_fillable = '';

    //properties for routes
    protected $route_group_prefix = '';
    protected $route_group_name = '';
    protected $route_name = '';
    protected $view_path = '';
    protected $view_name = '';

    //properties for requests
    protected $store_request_slot = '';
    protected $update_request_slot = '';

    //properties for sedder and factories
    protected $seeder_slot = '';

    //properties for views
    protected $create_input_slot = '';
    protected $edit_input_slot = '';

    //properties for icons
    protected $all_icons = '✅❗⛔⭕❓‼️⁉️⚠️❌🚫🛑💗💓💞❤️‍🩹🏠🚀✈️💺🌷💐🌻📁✏️🖋️✒️✒️🔎🔍♂️⚔️🗡️🩸💎🎈🎆🎇👏✍️👊🫵';
    protected $all_icon2 = 'ℹ️☑️🔵🟢🔴🟠🟡🟤🟥🟧🟨🟨🟩🟦🔶🔸🔺🔻🔷🔹☝️👉👈👇✌️🫲💪👀👁️😎😮';
    protected $input_icon = '✏️ ';
    protected $make_icon = '😮';
    protected $warning_icon = '⚠️ ';
    protected $info_icon = '🟢🟢';
    protected $skip_icon = '⛔ ';
    protected $success_make_icon = '💪💪💪';
    protected $success_msg_icon = '✅ ';


    //properties for messages
    protected $add_field_msg = 'Please, enter a field name';
    protected $skip_msg = '(Press enter to skip)';

    //Function for messages
    protected function successMsg($file_path)
    {
        return ($this->success_make_icon . " file '$file_path' is created Successfully");
    }
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command create the all curd facilities';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //get the model name
        $this->model_class_name = $this->argument('model');

        // checking for the same name
        $file_path = app_path("Models/{$this->model_class_name}.php");
        if (file_exists($file_path)) {
            $this->info("{$this->warning_icon} This model '{$this->model_class_name}' already exist.");
            while (true) {
                $this->model_class_name = $this->ask("{$this->input_icon} Enter a model name again:");
                $file_path = app_path("Models/{$this->model_class_name}.php");
                if (!file_exists($file_path)) {
                    break;
                } else {
                    $this->info("{$this->warning_icon} This model '{$this->model_class_name}' also already exist.");
                }
            }
        }

        // Database fields collect from the command plate
        $this->collectFields();

        //Migraton creation
        if ($this->confirm("{$this->make_icon} Are you want to make Migration", true)) {
            $this->makeMigration();
        }

        //Model creation
        if ($this->confirm("{$this->make_icon} Are you want to make Model", true)) {
            $this->makeModel();
        }

        // Route creation
        if ($this->confirm("{$this->make_icon} Are you want to make Route", true)) {
            $this->makeRoute();
        }


        // //Service Class
        if ($this->confirm("{$this->make_icon} Are you want to make Service Class", true)) {
            $this->makeServiceClass();
        }

        // //Resource Controller creation
        if ($this->confirm("{$this->make_icon} Are you want to make Resource Controller", true)) {
            $this->makeController();
        }
        //Store Request creation
        if ($this->confirm("{$this->make_icon} Are you want to make Store Request", true)) {
            $this->makeStoreRequest();
        }

        //Update Request creation
        if ($this->confirm("{$this->make_icon} Are you want to make Update Request", true)) {
            $this->makeUpdateRequest();
        }

        // View creation
        if ($this->confirm("{$this->make_icon} Are you want to make View", true)) {
            $this->makeView();
        }

        //seeder creation
        if ($this->confirm("{$this->make_icon} Are you want to make Seeder", true)) {
            $this->makeSeeder();
        }

        //factory creation
        if ($this->confirm("{$this->make_icon} Are you want to make factory", true)) {
            $this->makeFactory();
        }

        // Migration and seeding
        if ($this->confirm("{$this->make_icon} Are you want to run migration and seeding", true)) {
            $this->migrattionAndSeeding();
        }


        $this->info("\n\t\t🎇💪💪💪  Process Terminate  💪💪💪🎇");
        $this->info("\n\t\t🎇💗💓💞💞 How was  your feeling. Let me know:- nobir.wd@gmail.com 💞💞💓💗🎇\n");
    }

    protected function migrattionAndSeeding()
    {
        try {
            Artisan::call('migrate:fresh --seed');
            $this->info($this->success_msg_icon.' '.'Migration and Seeding is done');
        } catch (\Exception $e) {
            Artisan::call('migrate');
            $this->info($this->success_msg_icon . ' ' . 'Migration done');
            $this->info($this->warning_icon.' '.'Seed can not be done. Please check your seeder or factory file');
        };
    }

    protected function collectFields()
    {
        $i = 0;
        while (true) {
            if ($field_input = $this->ask($this->input_icon . ' ' . $this->add_field_msg)) {
                $i++;
                //Database field name
                $this->data[$i]['field_name'] = str($field_input)->snake()->value();
                $this->info("\t 🎆The field is taken as :{$this->data[$i]['field_name']}");
                //Data type
                $this->data[$i]['data_type'] = $this->choice(
                    'Enter a data type?',
                    $this->data_type,
                    'string'
                );
                if($this->data[$i]['data_type'] == 'foreignIdFor'){
                    $this->data[$i]['field_name'] = str()->studly($field_input);
                }
                //is nullable
                $this->data[$i]['nullable'] = $this->confirm('Is the field nullable?');

                //default values
                if ($this->confirm('Have any default values?')) {
                    $this->data[$i]['default_value'] = $this->ask('Default value is:');
                } else {
                    $this->data[$i]['default_value'] = null;
                }

                $this->add_field_msg = 'Enter another field name ' . $this->skip_msg;
            } else {
                break;
            }
        }
        $this->info('🔎 Processing the field🔍');
        $this->makeReady();
        $this->info('🚀🚀 Processing of the field is complete🚀🚀');
    }

    protected function replaceFillableField($replaceable_field)
    {
        //For migration file
        $field = str($replaceable_field)->snake()->value() . '_id';
        $this->model_fillable = str_replace($replaceable_field, $field, $this->model_fillable);

        //In request file
        $this->store_request_slot = str_replace($replaceable_field, $field, $this->store_request_slot);

        //In seeder slot
        $this->seeder_slot = str_replace("'$replaceable_field' => 'fsddf'", "'$field' => 1", $this->seeder_slot);
    }

    protected function makeReady()
    {
        foreach ($this->data as $key => $datum) {

            //indentation currection
            if ($key != 1) {
                $this->migration_slot .= "\n\t\t\t";
                $this->store_request_slot .= "\n\t\t\t";
                $this->seeder_slot .= "\n\t\t\t\t";
            }

            //model fillable properties
            $this->model_fillable .= ", '{$datum['field_name']}'";

            //seeder slot
            $this->seeder_slot .= "'{$datum['field_name']}' => 'fsddf',";

            //Requests vaildation rules
            $this->store_request_slot .= "'{$datum['field_name']}'=> [";

            switch ($datum['data_type']) {
                    //Logic for Foreign Id For
                case 'foreignIdFor':
                    //Class name to field name converstion
                    $field_name = str($datum['field_name'])->snake()->value() . '_id';

                    // for migration
                    $this->migration_slot .= "\$table->{$datum['data_type']}(App\Models\\{$datum['field_name']}::class)";

                    //Other attributes checking such as nullable, unique, foreign key id
                    $this->otherAtrributesCheck($datum);

                    $this->migration_slot .= "->constrained()->cascadeOnUpdate()->cascadeOnDelete()";

                    //Replace model class by the foreign id
                    $this->replaceFillableField($datum['field_name']);

                    //for model
                    $this->model_functions .= "public function {$datum['field_name']}(){\n\t\treturn \$this->belongsTo({$datum['field_name']}::class);\n\t}";

                    //for view
                    $this->create_input_slot .= "<div class='form-group'>
                                                    <label for='$field_name'>{$datum['field_name']}</label>star_slot
                                                    <select name='$field_name' id='$field_name' class='form-control' required_slot>
                                                        <option value=''>--Select any {$datum['field_name']}--</option>
                                                        @foreach (\${$datum['field_name']} as \$key => \$$field_name)
                                                            <option value='{{ \${$field_name}->id }}' @selected(\${$field_name}->id == old('$field_name'))>{{ \${$field_name}->title }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('$field_name')
                                                        <span class='text-danger'>{{ \$message }}</span>
                                                    @enderror
                                                </div>";

                    $this->edit_input_slot .= "<div class='form-group'>
                                                    <label for='$field_name'>{$datum['field_name']}</label>star_slot
                                                    <select name='$field_name' id='$field_name' class='form-control' required_slot>
                                                        <option value=''>--Select any {$datum['field_name']}--</option>
                                                        @foreach (\${$datum['field_name']} as \$key => \$$field_name)
                                                            <option value='{{ \${$field_name}->id }}' @selected(\${$field_name}->id == \$datum->$field_name)>{{ \${$field_name}->title }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('$field_name')
                                                        <span class='text-danger'>{{ \$message }}</span>
                                                    @enderror
                                                </div>";
                    break;

                    //For all Common Data Type
                default:
                    //field name to title
                    $field_title = str($datum['field_name'])->headline()->value();
                    $this->migration_slot .= "\$table->{$datum['data_type']}('{$datum['field_name']}')";

                    //Other attributes checking such as nullable, unique, foreign key id
                    $this->otherAtrributesCheck($datum);

                    //for view
                    $this->create_input_slot .= "<div class='form-group'>
                                                            <label for='{$datum['field_name']}' class='col-form-label'>$field_title</label>star_slot
                                                            <input id='{$datum['field_name']}' type='text' name='{$datum['field_name']}' placeholder='Exp:- Enter $field_title'
                                                                value='{{ old('{$datum['field_name']}') }}' class='form-control required_slot'>
                                                            @error('{$datum['field_name']}')
                                                                <span class='text-danger'>{{ \$message }}</span>
                                                            @enderror
                                                        </div>";
                    $this->edit_input_slot .= "<div class='form-group'>
                                                            <label for='{$datum['field_name']}' class='col-form-label'>$field_title</label>star_slot
                                                            <input id='{$datum['field_name']}' type='text' name='{$datum['field_name']}' placeholder='Exp:- Enter $field_title'
                                                                value='{{\$datum->{$datum['field_name']} ? \$datum->{$datum['field_name']} : old('{$datum['field_name']}') }}'
                                                                class='form-control required_slot'>
                                                            @error('{$datum['field_name']}')
                                                                <span class='text-danger'>{{ \$message }}</span>
                                                            @enderror
                                                        </div>";

                    break;
            }

            //Replaceable base on nullable
            $this->inputTextReplaceable($datum);

            // Ending of every variable
            $this->migration_slot .= ';';
            $this->store_request_slot .= '],';
        }

    }

    protected function inputTextReplaceable($datum)
    {
        if ($datum['nullable']) {
            //create
            $this->create_input_slot = str_replace('star_slot', ' ', $this->create_input_slot);
            $this->create_input_slot = str_replace('required_slot', ' ', $this->create_input_slot);

            //edit
            $this->edit_input_slot = str_replace('star_slot', ' ', $this->edit_input_slot);
            $this->edit_input_slot = str_replace('required_slot', ' ', $this->edit_input_slot);
        } else {
            //create
            $this->create_input_slot = str_replace('star_slot', '<span class="text-danger">*</span></label>', $this->create_input_slot);
            $this->create_input_slot = str_replace('required_slot', 'required', $this->create_input_slot);

            //edit
            $this->edit_input_slot = str_replace('star_slot', '<span class="text-danger">*</span></label>', $this->edit_input_slot);
            $this->edit_input_slot = str_replace('required_slot', 'required', $this->edit_input_slot);
        }
    }

    protected function otherAtrributesCheck($datum)
    {
        if ($datum['nullable']) {
            $this->migration_slot .= "->nullable()";
            //validation
            $this->store_request_slot .= "'nullable'";
        } else {
            //validation
            $this->store_request_slot .= "'required'";
        }
        if ($datum['default_value']) {
            $this->migration_slot .= "->default('{$datum['default_value']}')";
        }
    }

    //Migration creation
    protected function makeMigration()
    {
        //creation table name
        $table_name = str($this->model_class_name)->snake()->plural()->value();

        //Content extract from stub
        $stub_content = file_get_contents($this->pakage_stub_path . 'migration.stub');

        //Replace the table name
        $content_with_table_name = str_replace('$table_name', $table_name, $stub_content);

        //setup the migration field
        $content_ready = str_replace('$slot', $this->migration_slot, $content_with_table_name);

        $file_name = date('Y_m_d_His') . '_' . 'create_' . $table_name . '_table.php';
        $file_path = database_path('migrations/' . $file_name);
        file_put_contents($file_path, $content_ready);

        //success message
        $this->info($this->successMsg($file_path));
    }

    //Model creation
    protected function makeModel()
    {
        $model_name = $this->model_class_name;
        //model content load from stub
        $stub_content = file_get_contents($this->pakage_stub_path . 'model.stub');

        //replace the model name
        $content_with_name = str_replace('$model_name', $model_name, $stub_content);
        $content_with_fillable_properties = str_replace('$fillable_properties', $this->model_fillable, $content_with_name);

        $full_content = str_replace('$slot', $this->model_functions, $content_with_fillable_properties);
        $full_file_name = $model_name . '.php';
        $file_path = app_path('Models/' . $full_file_name);
        file_put_contents($file_path, $full_content);

        //success message
        $this->info($this->successMsg($file_path));
    }

    //Route creation
    protected function makeRoute()
    {
        $model_name = $this->model_class_name;
        $controller_name = $model_name . "Controller";
        $route_name = str($model_name)->kebab()->value();

        //Route slot creation
        $route_slot = '';
        $base_route = '';
        $route_group_first_code = '';
        $route_group_last_code = "";

        //Route group preparation
        $route_group = $this->confirm('Has the route group?', true);
        if ($route_group) {
            $route_group_first_code .= 'Route::';
            if ($middleware = $this->ask($this->make_icon . ' ' . 'Enter Middleware for the route group (Presss enter to skip)')) {
                $route_group_first_code .= "middleware('$middleware')->";
            }
            if ($prefix = $this->ask($this->make_icon . ' ' . 'Enter prefix for the route group (Presss enter to skip)')) {
                $route_group_first_code .= "prefix('$prefix')->";
                $this->route_group_prefix = $prefix;
                $this->view_name .= $prefix . '.';
                $this->view_path .= $prefix . '/';
            }
            if ($name = $this->ask($this->make_icon . ' ' . 'Enter name for the route group (Presss enter to skip)')) {
                $route_group_first_code .= "name('$name')->";
                $this->route_group_name = $name;
                $this->route_name .= $name . '.';
            }
            $route_group_first_code .= "group(function () {\n\t";
            $route_group_last_code = "});\n";
        }
        //base route
        $base_route .= "Route::resource('/$route_name','App\Http\Controllers\\{$controller_name}');\n";

        //full route, folder and path setup
        $this->route_name .= $route_name . '.';
        $this->view_name .= $route_name . '.';
        $this->view_path .= $route_name . '/';

        //Full route
        $route_slot = $route_group_first_code . $base_route . $route_group_last_code;

        $file_path = base_path('routes/mini-wizard.php');
        //route content load from stub if not exist
        if (file_exists($file_path)) {
            $wimi_wizard_content = file_get_contents($file_path);
            if ($route_group_first_code) {
                if (strpos($wimi_wizard_content, $route_group_first_code) === false) {
                    $full_content = $wimi_wizard_content . "\n" . $route_slot;
                } else {
                    $replaceable_route_with_group = $route_group_first_code . $base_route;
                    $full_content = str_replace($route_group_first_code, $replaceable_route_with_group, $wimi_wizard_content);
                }
            } else {
                $full_content = $wimi_wizard_content . "\n" . $route_slot;
            }
            file_put_contents($file_path, $full_content);

            //success message
            $this->info($this->success_make_icon.' '."The 'mini-wizard' file updated successfully'");
        } else {
            $stub_content = file_get_contents($this->pakage_stub_path . 'route.stub');

            $full_content = str_replace('$slot', $route_slot, $stub_content);

            file_put_contents($file_path, $full_content);

            //success message
            $this->info($this->successMsg('mini-wizard'));

            //including the mini-wizard.php file in the web.php
            $web_file_path = base_path('/routes/web.php');
            $web_route_content = file_get_contents($web_file_path);
            $web_route_final_content = str_replace("<?php", "<?php \n require __DIR__ . '/mini-wizard.php';", $web_route_content);
            file_put_contents($web_file_path, $web_route_final_content);
            $this->info("{$this->success_make_icon} The route file 'mini-wizard' is also included in web.php. So no tension.");
        }
    }

    protected function makeServiceClass()
    {
        $file_name = $this->model_class_name . 'Service';
        $file_path = app_path("Http/Controllers/$file_name.php");
        //Controller content load from stub
        $stub_content = file_get_contents($this->pakage_stub_path . 'service-class.stub');

        //replacing
        $content_with_model_name = str_replace('$model_name', $this->model_class_name, $stub_content);
        $content_with_view_name = str_replace('$view_name', $this->view_name, $content_with_model_name);
        $content_with_route_name = str_replace('$route_name', $this->route_name, $content_with_view_name);

        $full_content =  $content_with_route_name;
        file_put_contents($file_path, $full_content);

        //success message
        $this->info($this->successMsg($file_path));
    }

    protected function makeController()
    {
        $file_name = $this->model_class_name . 'Controller';
        $file_path = app_path("Http/Controllers/$file_name.php");
        //Controller content load from stub
        $stub_content = file_get_contents($this->pakage_stub_path . 'resource-controller.stub');

        //replacing
        $content_with_model_name = str_replace('$model_name', $this->model_class_name, $stub_content);
        $content_with_view_name = str_replace('$view_name', $this->view_name, $content_with_model_name);
        $content_with_route_name = str_replace('$route_name', $this->route_name, $content_with_view_name);

        $full_content =  $content_with_route_name;
        file_put_contents($file_path, $full_content);

        //success message
        $this->info($this->successMsg($file_path));
    }

    protected function makeStoreRequest()
    {
        $reqest_name = 'Store' . $this->model_class_name . 'Request';
        $file_path = app_path("Http/Requests/$reqest_name.php");
        //Controller content load from stub
        $stub_content = file_get_contents($this->pakage_stub_path . 'store-request.stub');

        //replace the model name
        $content_with_name = str_replace('$model_name', $this->model_class_name, $stub_content);

        //table_name
        $table_name = str($this->model_class_name)->kebab()->plural()->value();
        $content_with_table_name = str_replace('$table_name', $table_name, $content_with_name);

        $full_content = str_replace('$slot', $this->store_request_slot, $content_with_table_name);
        file_put_contents($file_path, $full_content);

        //success message
        $this->info($this->successMsg($file_path));
    }

    protected function makeUpdateRequest()
    {
        $reqest_name = 'Update' . $this->model_class_name . 'Request';
        $file_path = app_path("Http/Requests/$reqest_name.php");
        //Controller content load from stub
        $stub_content = file_get_contents($this->pakage_stub_path . 'update-request.stub');

        //replace the model name
        $content_with_name = str_replace('$model_name', $this->model_class_name, $stub_content);

        //table_name
        $table_name = str($this->model_class_name)->kebab()->plural()->value();
        $content_with_table_name = str_replace('$table_name', $table_name, $content_with_name);

        //table_name
        $model_name_snack = str($this->model_class_name)->snake()->value();
        $content_with_model_name_snack = str_replace('$model_name_snack', $model_name_snack, $content_with_table_name);
        $full_content = str_replace('$slot', $this->store_request_slot, $content_with_model_name_snack);
        file_put_contents($file_path, $full_content);

        //success message
        $this->info($this->successMsg($file_path));
    }

    protected function makeView()
    {

        $index_view_path = resource_path("views/{$this->view_path}index.blade.php");
        $create_view_path = resource_path("views/{$this->view_path}create.blade.php");
        $edit_view_path = resource_path("views/{$this->view_path}edit.blade.php");

        if (!file_exists($this->view_path)) {
            $directory = resource_path("views/$this->view_path");
            mkdir($directory, 0755, true);
        }

        $this->indexViewCreation($index_view_path);
        $this->createViewCreation($create_view_path);
        $this->editViewCreation($edit_view_path);
    }

    protected function indexViewCreation($file_path)
    {

        $th_slot = $this->thSlotCreation();
        $td_slot = $this->tdSlotCreation();

        $page_title = str($this->model_class_name)->headline()->value();

        //index view load content load from stub
        $stub_content = file_get_contents($this->pakage_stub_path . 'index.stub');

        //replace the model name
        $content_with_model_name = str_replace('$model_name', $this->model_class_name, $stub_content);

        //replace the route name
        $content_with_route = str_replace('$route_name', $this->route_name, $content_with_model_name);
        $content_with_page_name = str_replace('$page_title', $page_title, $content_with_route);
        $content_with_th_slot = str_replace('$th_slot', $th_slot, $content_with_page_name);
        $content_with_td_slot = str_replace('$td_slot', $td_slot, $content_with_th_slot);

        $full_content = $content_with_td_slot;
        file_put_contents($file_path, $full_content);

        //success message
        $this->info($this->successMsg($file_path));
    }

    protected function createViewCreation($file_path)
    {

        $page_title = str($this->model_class_name)->headline()->value();

        //create view load content load from stub
        $stub_content = file_get_contents($this->pakage_stub_path . 'create.stub');
        //replace the model name
        $content_with_modal_name = str_replace('$model_name', $this->model_class_name, $stub_content);
        //replace the route name
        $content_with_route = str_replace('$route_name', $this->route_name, $content_with_modal_name);
        $content_with_page_title = str_replace('$page_title', $page_title, $content_with_route);
        $full_content = str_replace('$slot', $this->create_input_slot, $content_with_page_title);
        file_put_contents($file_path, $full_content);

        //success message
        $this->info($this->successMsg($file_path));
    }

    protected function editViewCreation($file_path)
    {
        $page_title = str($this->model_class_name)->headline()->value();

        //edit view load content load from stub
        $stub_content = file_get_contents($this->pakage_stub_path . 'edit.stub');

        //replace the model name
        $content_with_page_title = str_replace('$page_title', $page_title, $stub_content);
        //replace the route name
        $content_with_route = str_replace('$route_name', $this->route_name, $content_with_page_title);

        $full_content = str_replace('$slot', $this->edit_input_slot, $content_with_route);
        file_put_contents($file_path, $full_content);

        //success message
        $this->info($this->successMsg($file_path));
    }

    protected function thSlotCreation()
    {
        $table_heading = '';
        foreach ($this->data as $key => $datum) {

            //Indentation currection
            if ($key != 1) {
                $table_heading .= "\n\t\t\t\t\t\t\t\t";
            }
            $table_heading .= '<th>' . str($datum['field_name'])->headline()->value() . '</th>';
        }
        return $table_heading;
    }

    protected function tdSlotCreation()
    {
        $td = '';
        foreach ($this->data as $key => $datum) {

            //Indentation currection
            if ($key != 1) {
                $td .= "\n\t\t\t\t\t\t\t\t\t";
            }
            $td .= "<td> {{ \$datum->{$datum['field_name']} }}</td>";
        }
        return $td;
    }

    protected function makeSeeder()
    {
        $file_name = $this->model_class_name . 'Seeder';
        $table_name = str($this->model_class_name)->snake()->plural()->value();
        $file_path = database_path("seeders/$file_name.php");

        //Controller content load from stub
        $stub_content = file_get_contents($this->pakage_stub_path . 'seeder.stub');

        //replace the model name
        $content_with_name = str_replace('$model_name', $this->model_class_name, $stub_content);
        $content_with_table_name = str_replace('$table_name', $table_name, $content_with_name);

        $full_content = str_replace('$slot', $this->seeder_slot, $content_with_table_name);

        file_put_contents($file_path, $full_content);

        //success message
        $this->info($this->successMsg($file_path));

        //seeder inplement in the DatabaseSeeder.php
        $database_seeder_path = database_path('seeders/DatabaseSeeder.php');
        $database_seeder_content = file_get_contents($database_seeder_path);
        $database_seeder_content_with_seeder = str_replace("]); //n", "\t$file_name::class, \n\t\t]); //n", $database_seeder_content);
        file_put_contents($database_seeder_path, $database_seeder_content_with_seeder);
        $this->info("{$this->warning_icon} The seeder '$file_name' is set to DatabaseSeeder.php file just befor ']); //n'");
    }

    protected function makeFactory()
    {
        $file_name = $this->model_class_name . 'Factory';
        $table_name = str($this->model_class_name)->snake()->plural()->value();
        $file_path = database_path("factories/$file_name.php");

        //Controller content load from stub
        $stub_content = file_get_contents($this->pakage_stub_path . 'factory.stub');

        //replace the model name
        $content_with_name = str_replace('$model_name', $this->model_class_name, $stub_content);
        $full_content = str_replace('$slot', $this->seeder_slot, $content_with_name);

        file_put_contents($file_path, $full_content);

        //success message
        $this->info($this->successMsg($file_path));

        //the factory implement
        $raws_num = (int)$this->ask($this->make_icon . ' ' . 'How many rows you want to insert');
        $raws_num = $raws_num ? $raws_num : 1;
        $database_seeder_path = database_path('seeders/DatabaseSeeder.php');
        $database_seeder_content = file_get_contents($database_seeder_path);
        $database_seeder_content_with_factory = str_replace("]); //n", "]); //n\n\n\t\t\App\Models\\{$this->model_class_name}::factory()->count($raws_num)->create();", $database_seeder_content);
        file_put_contents($database_seeder_path, $database_seeder_content_with_factory);
        $this->info("{$this->warning_icon} The  '$file_name' is set to DatabaseSeeder.php file just after ']); //n'");
    }
}
