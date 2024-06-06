<?php

namespace Nobir\CurdByCommand\Console\Commands;

use App\Models\NSidebar;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Nobir\CurdByCommand\Module\Navbar;

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
    protected $global_prefix = '';
    protected $pakage_stub_path = __DIR__ . '/../../stubs/';

    //File base path
    const MODEL_PATH = '';
    const MIGRATION_PATH = '';
    const SEEDER_PATH = '';
    const FACTORY_PATH = '';
    const CONTROLLER_PATH = '';
    const REQUESTS_PATH = '';
    const SERVICE_PATH = '';

    //Proterties for model
    protected $model_class_name;
    protected $table_name;
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

    //sidebar properties
    protected $parent_navbar = '';
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
        $this->table_name = str($this->model_class_name)->snake()->plural()->value();
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

        // Collecting global prefix such as backend, frontend
        $global_prefix = $this->choice('Choice a global prefix (Press enter to skip)', ['Backend', 'Frontend', 'None'], 0);
        if ($global_prefix != 'None') {
            $this->global_prefix =  $global_prefix;
        }

        // Database fields collect from the command plate
        $this->collectFields();

        //=====================================================================
        // ========================= Operation Start ========================
        // 1. Model creation
        if ($this->confirm("{$this->make_icon} Are you want to make Model", true)) {
            $this->makeModel();
        }

        // 2. Migraton creation
        if ($this->confirm("{$this->make_icon} Are you want to make Migration", true)) {
            $this->makeMigration();
        }

        // 3. Route creation
        if ($this->confirm("{$this->make_icon} Are you want to make Route", true)) {
            $this->makeRoute();
        }

        // 4. Service Class
        if ($this->confirm("{$this->make_icon} Are you want to make Service Class", true)) {
            $this->makeServiceClass();
        }

        // 5. Resource Controller creation
        if ($this->confirm("{$this->make_icon} Are you want to make Resource Controller", true)) {
            $this->makeController();
        }

        // 6. Store Request creation
        if ($this->confirm("{$this->make_icon} Are you want to make Store Request", true)) {
            $this->makeStoreRequest();
        }

        //7. Update Request creation
        if ($this->confirm("{$this->make_icon} Are you want to make Update Request", true)) {
            $this->makeUpdateRequest();
        }

        // 8. View creation
        if ($this->confirm("{$this->make_icon} Are you want to make View", true)) {
            $this->makeView();
        }

        // 9. seeder creation
        if ($this->confirm("{$this->make_icon} Are you want to make Seeder", true)) {
            $this->makeSeeder();
        }

        // 10. factory creation
        if ($this->confirm("{$this->make_icon} Are you want to make factory", true)) {
            $this->makeFactory();
        }

        // 11. Sidebar(menu) creation
        if ($this->confirm("{$this->make_icon} Are you want to make Sidebar", true)) {
            $this->makeSidebar();
        }

        // 12. Migration and seeding
        if ($this->confirm("{$this->make_icon} Are you want to run migration and seeding", true)) {
            $this->migrattionAndSeeding();
        }

        $this->info("\n\t\t🎇💪💪💪  Process Terminate  💪💪💪🎇");
        $this->info("\n\t\t🎇💗💓💞💞 How was  your feeling. Let me know:- nobir.wd@gmail.com 💞💞💓💗🎇\n");
    }

    protected function makeDirectory($directory_path)
    {
        if (!is_dir($directory_path)) {
            mkdir($directory_path, 0755, true);
            $this->info($directory_path . ' created successfully');
        } else {
            $this->info("$directory_path folder already exists");
        }
    }

    protected function makeDirectoryWithValidation($file_base_path, $folder_name)
    {
        if (!empty($file_base_path) && !is_dir($file_base_path)) {
            $this->makeDirectory($file_base_path);
        }

        if (!$folder_name) {
            return $file_base_path;
        }

        $directory_path = $file_base_path . "/$folder_name";
        if ($this->confirm("Are you want to create the file under $folder_name", true)) {

            $this->makeDirectory($directory_path);

            return $directory_path;
        } else {
            return $file_base_path;
        }
    }

    protected function getContentAndReplaceText($content_path, array $replacing_texts = [])
    {

        $content = file_get_contents($content_path);

        if (count($replacing_texts) > 0) {
            foreach ($replacing_texts as $key => $value) {
                $content = str_replace($key, $value, $content);
            }
        }

        return $content;
    }

    protected function fileMakingAndPutingContent($file_path, $content)
    {
        if (!file_exists($file_path)) {
            file_put_contents($file_path, $content);
            $this->info($file_path . ' created successfully');
        } else {
            if ($this->confirm("$file_path is already exists. Are you want to replace", false)) {
                file_put_contents($file_path, $content);
                $this->info($file_path . '
                created successfully');
            }
        }
    }

    protected function migrattionAndSeeding()
    {
        // try {
        //     Artisan::call('migrate:fresh --seed');
        //     $this->info($this->success_msg_icon . ' ' . 'Migration and Seeding is done');
        // } catch (\Exception $e) {
        Artisan::call('migrate');
        $this->info($this->success_msg_icon . ' ' . 'Migration done');
        $this->info($this->warning_icon . ' ' . 'Seed can not be done. Please check your seeder or factory file');
        // };
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
                if ($this->data[$i]['data_type'] == 'foreignIdFor') {
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
                    $this->create_input_slot .= "<div class='flex-shrink max-w-full px-4 w-full md:w-1/2 mb-6'>
                                                    <label for='$field_name'>{$datum['field_name']}</label>star_slot
                                                    <select name='$field_name' id='$field_name' class='inline-block w-full leading-5 relative py-2 pl-3 pr-8 rounded text-gray-800 bg-white border border-gray-300 overflow-x-auto focus:outline-none focus:border-gray-400 focus:ring-0 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-700 dark:focus:border-gray-600 select-caret appearance-none' required_slot>
                                                        <option value='' selected>--Select any {$datum['field_name']}--</option>
                                                        @foreach (\${$datum['field_name']} as \$key => \$$field_name)
                                                            <option value='{{ \${$field_name}->id }}' @selected(\${$field_name}->id == old('$field_name'))>{{ \${$field_name}->title }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('$field_name')
                                                        <span class='text-[red]'>{{ \$message }}</span>
                                                    @enderror
                                                </div>";

                    $this->edit_input_slot .= "<div class='flex-shrink max-w-full px-4 w-full md:w-1/2 mb-6'>
                                                    <label for='$field_name'>{$datum['field_name']}</label>star_slot
                                                    <select name='$field_name' id='$field_name'class='inline-block w-full leading-5 relative py-2 pl-3 pr-8 rounded text-gray-800 bg-white border border-gray-300 overflow-x-auto focus:outline-none focus:border-gray-400 focus:ring-0 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-700 dark:focus:border-gray-600 select-caret appearance-none' required_slot>
                                                        <option value='' selected>--Select any {$datum['field_name']}--</option>
                                                        @foreach (\${$datum['field_name']} as \$key => \$$field_name)
                                                            <option value='{{ \${$field_name}->id }}' @selected(\${$field_name}->id == \$datum->$field_name)>{{ \${$field_name}->title }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('$field_name')
                                                        <span class='text-[red]'>{{ \$message }}</span>
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
                    $this->create_input_slot .= "<div class='flex-shrink max-w-full px-4 w-full md:w-1/2 mb-6'>
                                                            <label for='{$datum['field_name']}' class='inline-block mb-2'>$field_title</label>star_slot
                                                            <input id='{$datum['field_name']}' type='text' name='{$datum['field_name']}' placeholder='Exp:- Enter $field_title'
                                                                value='{{ old('{$datum['field_name']}') }}' class='w-full leading-5 relative py-2 px-4 rounded text-gray-800 bg-white border border-gray-300 overflow-x-auto focus:outline-none focus:border-gray-400 focus:ring-0 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-700 dark:focus:border-gray-600'>
                                                            @error('{$datum['field_name']}')
                                                                <span class='text-[red]'>{{ \$message }}</span>
                                                            @enderror
                                                        </div>";
                    $this->edit_input_slot .= "<div class='flex-shrink max-w-full px-4 w-full md:w-1/2 mb-6'>
                                                            <label for='{$datum['field_name']}' class='inline-block mb-2'>$field_title</label>star_slot
                                                            <input id='{$datum['field_name']}' type='text' name='{$datum['field_name']}' placeholder='Exp:- Enter $field_title'
                                                                value='{{\$datum->{$datum['field_name']} ? \$datum->{$datum['field_name']} : old('{$datum['field_name']}') }}'
                                                                class='w-full leading-5 relative py-2 px-4 rounded text-gray-800 bg-white border border-gray-300 overflow-x-auto focus:outline-none focus:border-gray-400 focus:ring-0 dark:text-gray-300 dark:bg-gray-700 dark:border-gray-700 dark:focus:border-gray-600'>
                                                            @error('{$datum['field_name']}')
                                                                <span class='text-[red]'>{{ \$message }}</span>
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
            $this->create_input_slot = str_replace('star_slot', '<span class="text-[red]">*</span></label>', $this->create_input_slot);
            $this->create_input_slot = str_replace('required_slot', 'required', $this->create_input_slot);

            //edit
            $this->edit_input_slot = str_replace('star_slot', '<span class="text-[red]">*</span></label>', $this->edit_input_slot);
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
        // step- 1 => making directory path (using global prefix such as backend,..) for the service class
        $dir_base_path = database_path('migrations');
        $dir_final_path = $this->makeDirectoryWithValidation($dir_base_path, strtolower($this->global_prefix));


        //Step-2 => Making stub file path and file path for the files thats are needed to create
        $table_name = str($this->model_class_name)->snake()->plural()->value();
        $file_name = date('Y_m_d_His') . '_' . 'create_' . $table_name . '_table.php';
        $file_path = $dir_final_path . "/$file_name";
        $stub_file_path = $this->pakage_stub_path . 'migration.stub';

        //Step-3 => geting the file content and replacing the certain text if needed
        $stub_content = $this->getContentAndReplaceText($stub_file_path, [
            '$table_name' => $table_name,
            '$slot' => $this->migration_slot,
        ]);

        //Step-4 => making the file
        $this->fileMakingAndPutingContent($file_path, $stub_content);
    }

    protected function makeNameSpace($base_path)
    {
        if ($this->global_prefix != 'None') {
            return $base_path . "\\$this->global_prefix";
        }
        return $base_path;
    }

    //Model creation
    protected function makeModel()
    {
        // step- 1 => making directory path (using global prefix such as backend,..) for the service class
        $dir_base_path = app_path('Models');
        $dir_final_path = $this->makeDirectoryWithValidation($dir_base_path, $this->global_prefix);

        //Step-2 => Making stub file path and file path for the files thats are needed to create
        $file_name = $this->model_class_name . '.php';
        $file_path = $dir_final_path . "/$file_name";
        $stub_file_path = $this->pakage_stub_path . 'model.stub';

        //Step-3 => geting the file content and replacing the certain text if needed
        $stub_content = $this->getContentAndReplaceText($stub_file_path, [
            '$name_space' => $this->makeNameSpace('App\Models'),
            '$model_name' => $this->model_class_name,
            '$fillable_properties' => $this->model_fillable,
            '$slot' => $this->model_functions,
        ]);

        //Step-4 => making the file
        $this->fileMakingAndPutingContent($file_path, $stub_content);
    }

    //Route creation
    protected function  makeRoute()
    {
        // step- 1 => making directory path (using global prefix such as backend,..) for the service class
        $dir_base_path = base_path('routes');
        $dir_final_path = $dir_base_path;

        //Step-2 => Making stub file path and file path for the files thats are needed to create
        $file_name = 'mini-wizard.php';
        if ($this->global_prefix) {
            $file_name = strtolower($this->global_prefix) . '.php';
        }

        $file_path = $dir_final_path . "/$file_name";
        $stub_file_path = $this->pakage_stub_path . 'route.stub';

        //Step-extra => Processing
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
                $this->parent_navbar = $prefix;
            }
            if ($name = $this->ask($this->make_icon . ' ' . 'Enter name for the route group (Presss enter to skip)')) {
                $route_group_first_code .= "name('$name.')->";
                $this->route_group_name = $name;
                $this->route_name .= $name . '.';
            }
            $route_group_first_code .= "group(function () {\n\t";
            $route_group_last_code = "});\n";
        }
        //base route
        $base_route .= "Route::resource('/$route_name','App\Http\Controllers\\{$controller_name}');\n";
        if ($this->global_prefix) {
            $base_route .= "Route::resource('/$route_name','App\Http\Controllers\\$this->global_prefix\\{$controller_name}');\n";
        }
        //full route, folder and path setup
        $this->route_name .= $route_name . '.';
        $this->view_name .= $route_name . '.';
        $this->view_path .= $route_name . '/';

        //Full route
        $route_slot = $route_group_first_code . $base_route . $route_group_last_code;
        if (file_exists($file_path)) {
            $file_path_content = file_get_contents($file_path);

            if ($route_group_first_code) {
                if (strpos($file_path_content, $route_group_first_code) === false) {
                    $full_content = $file_path_content . "\n" . $route_slot;
                } else {
                    $replaceable_route_with_group = $route_group_first_code . $base_route;
                    $full_content = str_replace($route_group_first_code, $replaceable_route_with_group, $file_path_content);
                }
            } else {
                $full_content = $file_path_content . "\n" . $route_slot;
            }
            file_put_contents($file_path, $full_content);

            //success message
            $this->info($this->success_make_icon . ' ' . "The $file_name file updated successfully'");
        } else {
            $stub_content = file_get_contents($stub_file_path);
            //global prefix added to the route
            if ($this->global_prefix !== 'None') {
                $global_prefix_lowercase = strtolower($this->global_prefix);
                $global_route_prefix_code = "Route::prefix('$global_prefix_lowercase')->group(function(){\n \$slot \n });";
                $stub_content = str_replace('$slot', $global_route_prefix_code, $stub_content);
            }

            $full_content = str_replace('$slot', $route_slot, $stub_content);

            file_put_contents($file_path, $full_content);

            //success message
            $this->info($this->successMsg($file_name));

            //including the mini-wizard.php file in the web.php
            $web_file_path = base_path('/routes/web.php');
            $web_route_content = file_get_contents($web_file_path);
            $web_route_final_content = str_replace("<?php", "<?php \n require __DIR__ . '/$file_name';", $web_route_content);
            file_put_contents($web_file_path, $web_route_final_content);
            $this->info("{$this->success_make_icon} The route file '$file_name' is also included in web.php. So no tension.");
        }
    }

    //makeServiceClass
    protected function makeServiceClass()
    {
        // step- 1 => Making directory and directory path (using global prefix such as backend,..) for the service class
        $dir_name = 'Services';
        $dir_base_path = app_path($dir_name);
        $directory_final_path = $this->makeDirectoryWithValidation($dir_base_path, $this->global_prefix);


        //Step-2 => Making stub file path and file path for the files thats are needed to create
        //parent file
        $parent_file_name = 'Service.php';
        $parent_file_path = $dir_base_path . "/$parent_file_name";
        $parent_stub_file_path = $this->pakage_stub_path . 'parent-service-class.stub';
        //responsible file
        $file_name = $this->model_class_name . 'Service.php';
        $file_path = $directory_final_path . "/$file_name";
        $stub_file_path = $this->pakage_stub_path . 'service-class.stub';

        //Step-3 => geting the file content and replacing the certain text if needed
        //parent files contents
        $parent_stub_content = $this->getContentAndReplaceText($parent_stub_file_path);
        //targent (responsible) file contents
        $stub_content = $this->getContentAndReplaceText($stub_file_path, [
            '$model_name' => $this->model_class_name,
            '$name_space' => $this->makeNameSpace('App\Services'),
        ]);

        //Step-4 => making the file
        //paretn files
        $this->fileMakingAndPutingContent($parent_file_path, $parent_stub_content);
        //target or responsible file
        $this->fileMakingAndPutingContent($file_path, $stub_content);
    }

    protected function makeController()
    {
        // step- 1 => making directory path (using global prefix such as backend,..) for the service class
        $dir_base_path = app_path('Http/Controllers');
        $dir_final_path = $this->makeDirectoryWithValidation($dir_base_path, $this->global_prefix);

        //Step-2 => Making stub file path and file path for the files thats are needed to create
        $file_name = $this->model_class_name . 'Controller.php';
        $file_path = $dir_final_path . "/$file_name";
        $stub_file_path = $this->pakage_stub_path . 'resource-controller.stub';

        //Step-3 => geting the file content and replacing the certain text if needed
        if ($this->global_prefix) {
            $view_name = strtolower($this->global_prefix) . '.pages.' . $this->view_name;
        }
        $stub_content = $this->getContentAndReplaceText($stub_file_path, [
            '$name_space' => $this->makeNameSpace('App\Http\Controllers'),
            '$model_name' => $this->model_class_name,
            '$view_name' => $view_name,
            '$route_name' => $this->route_name,
        ]);

        //Step-4 => making the file
        $this->fileMakingAndPutingContent($file_path, $stub_content);

        // //Old
        // $file_name = $this->model_class_name . 'Controller';
        // $file_path = app_path("Http/Controllers/$file_name.php");
        // //Controller content load from stub
        // $stub_content = file_get_contents($this->pakage_stub_path . 'resource-controller.stub');

        // //replacing
        // $content_with_model_name = str_replace('$model_name', $this->model_class_name, $stub_content);
        // $content_with_view_name = str_replace('$view_name', $this->view_name, $content_with_model_name);
        // $content_with_route_name = str_replace('$route_name', $this->route_name, $content_with_view_name);

        // $full_content =  $content_with_route_name;
        // file_put_contents($file_path, $full_content);

        // //success message
        // $this->info($this->successMsg($file_path));
    }

    protected function makeStoreRequest()
    {
        // step- 1 => making directory path (using global prefix such as backend,..) for the service class
        $dir_base_path = app_path('Http/Requests');
        $dir_final_path = $this->makeDirectoryWithValidation($dir_base_path, $this->global_prefix);

        //Step-2 => Making stub file path and file path for the files thats are needed to create
        $file_name = 'Store' . $this->model_class_name . 'Request.php';
        $file_path = $dir_final_path . "/$file_name";
        $stub_file_path = $this->pakage_stub_path . 'store-request.stub';

        //Step-3 => geting the file content and replacing the certain text if needed
        $stub_content = $this->getContentAndReplaceText($stub_file_path, [
            '$name_space' => $this->makeNameSpace('App\Http\Requests'),
            '$model_name' => $this->model_class_name,
            '$table_name' => str($this->model_class_name)->snake()->plural()->value(),
            '$slot' => $this->store_request_slot,
        ]);

        //Step-4 => making the file
        $this->fileMakingAndPutingContent($file_path, $stub_content);

        // //Old
        // $reqest_name = 'Store' . $this->model_class_name . 'Request';
        // $file_path = app_path("Http/Requests/$reqest_name.php");
        // //Controller content load from stub
        // $stub_content = file_get_contents($this->pakage_stub_path . 'store-request.stub');

        // //replace the model name
        // $content_with_name = str_replace('$model_name', $this->model_class_name, $stub_content);

        // //table_name
        // $table_name = str($this->model_class_name)->kebab()->plural()->value();
        // $content_with_table_name = str_replace('$table_name', $table_name, $content_with_name);

        // $full_content = str_replace('$slot', $this->store_request_slot, $content_with_table_name);
        // file_put_contents($file_path, $full_content);

        // //success message
        // $this->info($this->successMsg($file_path));
    }

    protected function makeUpdateRequest()
    {

        // step- 1 => making directory path (using global prefix such as backend,..) for the service class
        $dir_base_path = app_path('Http/Requests');
        $dir_final_path = $this->makeDirectoryWithValidation($dir_base_path, $this->global_prefix);

        //Step-2 => Making stub file path and file path for the files thats are needed to create
        $file_name = 'Update' . $this->model_class_name . 'Request.php';
        $file_path = $dir_final_path . "/$file_name";
        $stub_file_path = $this->pakage_stub_path . 'update-request.stub';


        //Step-3 => geting the file content and replacing the certain text if needed
        $stub_content = $this->getContentAndReplaceText($stub_file_path, [
            '$name_space' => $this->makeNameSpace('App\Http\Requests'),
            '$model_name' => $this->model_class_name,
            '$table_name' => str($this->model_class_name)->snake()->plural()->value(),
            '$m_name_snack' => str($this->model_class_name)->snake()->value(),
            '$slot' => $this->store_request_slot,
        ]);

        //Step-4 => making the file
        $this->fileMakingAndPutingContent($file_path, $stub_content);

        // //Old
        // $reqest_name = 'Update' . $this->model_class_name . 'Request';
        // $file_path = app_path("Http/Requests/$reqest_name.php");
        // //Controller content load from stub
        // $stub_content = file_get_contents($this->pakage_stub_path . 'update-request.stub');

        // //replace the model name
        // $content_with_name = str_replace('$model_name', $this->model_class_name, $stub_content);

        // //table_name
        // $table_name = str($this->model_class_name)->snake()->plural()->value();
        // $content_with_table_name = str_replace('$table_name', $table_name, $content_with_name);

        // //table_name
        // $model_name_snack = str($this->model_class_name)->snake()->value();
        // $content_with_model_name_snack = str_replace('$model_name_snack', $model_name_snack, $content_with_table_name);
        // $full_content = str_replace('$slot', $this->store_request_slot, $content_with_model_name_snack);
        // file_put_contents($file_path, $full_content);

        // //success message
        // $this->info($this->successMsg($file_path));
    }

    protected function makeView()
    {

        // step- 1 => making directory path (using global prefix such as backend,..) for the service class
        $dir_base_path = resource_path('views');
        $dir_path = $this->makeDirectoryWithValidation($dir_base_path, strtolower($this->global_prefix));
        $dir_final_path = $dir_path . "/pages/$this->view_path";
        $this->makeDirectory($dir_final_path);

        //Old
        $index_view_path = $dir_final_path . "index.blade.php";
        $create_view_path = $dir_final_path . "create.blade.php";
        $edit_view_path = $dir_final_path . "edit.blade.php";

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
        // step- 1 => making directory path (using global prefix such as backend,..) for the service class
        $name_space = $this->makeNameSpace('Database\Seeders');
        $dir_base_path = database_path('seeders');
        $dir_final_path = $this->makeDirectoryWithValidation($dir_base_path, $this->global_prefix);

        //Step-2 => Making stub file path and file path for the files thats are needed to create
        $file_class_name = $this->model_class_name . 'Seeder';
        $file_name = $file_class_name . '.php';
        $file_path = $dir_final_path . "/$file_name";
        $stub_file_path = $this->pakage_stub_path . 'seeder.stub';
        //Step-3 => geting the file content and replacing the certain text if needed
        $stub_content = $this->getContentAndReplaceText($stub_file_path, [
            '$name_space' => $name_space,
            '$model_name' => $this->model_class_name,
            '$table_name' => $this->table_name,
            '$slot' => $this->seeder_slot,
        ]);

        //Step-4 => making the file
        $this->fileMakingAndPutingContent($file_path, $stub_content);

        //seeder inplement in the DatabaseSeeder.php
        $database_seeder_path = database_path('seeders/DatabaseSeeder.php');
        $database_seeder_content = file_get_contents($database_seeder_path);
        $database_seeder_content_with_seeder = str_replace("]); //n", "\t\\$name_space\\$file_class_name::class, \n\t\t]); //n", $database_seeder_content);
        file_put_contents($database_seeder_path, $database_seeder_content_with_seeder);
        $this->info("{$this->warning_icon} The seeder '$file_name' is set to DatabaseSeeder.php file just befor ']); //n'");
    }

    protected function makeFactory()
    {
        // step- 1 => making directory path (using global prefix such as backend,..) for the service class
        $dir_base_path = database_path('factories');
        $dir_final_path = $this->makeDirectoryWithValidation($dir_base_path, $this->global_prefix);

        //Step-2 => Making stub file path and file path for the files thats are needed to create
        $file_name = $this->model_class_name . 'Factory.php';
        $file_path = $dir_final_path . "/$file_name";
        $stub_file_path = $this->pakage_stub_path . 'factory.stub';
        //Step-3 => geting the file content and replacing the certain text if needed
        $stub_content = $this->getContentAndReplaceText($stub_file_path, [
            '$name_space' => $this->makeNameSpace('Database\Factories'),
            '$model_name' => $this->model_class_name,
            '$slot' => $this->seeder_slot,
        ]);

        //Step-4 => making the file
        $this->fileMakingAndPutingContent($file_path, $stub_content);


        //the factory implement
        $raws_num = (int)$this->ask($this->make_icon . ' ' . 'How many rows you want to insert');
        $raws_num = $raws_num ? $raws_num : 1;
        $database_seeder_path = database_path('seeders/DatabaseSeeder.php');
        $database_seeder_content = file_get_contents($database_seeder_path);
        $database_seeder_content_with_factory = str_replace("]); //n", "]); //n\n\n\t\t\App\Models\backend\\{$this->model_class_name}::factory()->count($raws_num)->create();", $database_seeder_content);
        file_put_contents($database_seeder_path, $database_seeder_content_with_factory);
        $this->info("{$this->warning_icon} The  '$file_name' is set to DatabaseSeeder.php file just after ']); //n'");
    }

    protected function makeSidebar()
    {
        $file_path = app_path('Models/NSidebar.php');
        if (!file_exists($file_path)) {
            $content = $this->getContentAndReplaceText($this->pakage_stub_path . 'sidebar-model.stub');
            $this->fileMakingAndPutingContent($file_path, $content);
        }

        if (!Schema::hasTable('n_sidebars')) {
            // sidebar migration
            // step- 1 => making directory path (using global prefix such as backend,..) for the service class
            $dir_base_path = database_path('migrations');
            $dir_final_path = $this->makeDirectoryWithValidation($dir_base_path, 'backend');


            //Step-2 => Making stub file path and file path for the files thats are needed to create
            $table_name = 'n_sidebars';
            $birth_date = '2024_05_31_085644';
            $file_name = $birth_date . '_' . 'create_' . $table_name . '_table.php';
            $file_path = $dir_final_path . "/$file_name";
            $stub_file_path = $this->pakage_stub_path . 'migration.stub';
            $slot = "\$table->string('access');\n\t\t\t \$table->string('route')->nullable();\n\t\t\t \$table->boolean('is_parent')->nullable();\n\t\t\t \$table->foreignIdFor(\App\Models\NSidebar::class)->nullable();";

            if (!file_exists($file_path)) {
                //Step-3 => geting the file content and replacing the certain text if needed
                $stub_content = $this->getContentAndReplaceText($stub_file_path, [
                    '$table_name' => $table_name,
                    '$slot' => $slot,
                ]);

                //Step-4 => making the file
                $this->fileMakingAndPutingContent($file_path, $stub_content);
            }
            Artisan::call("migrate", ['--path' => 'database/migrations/backend/2024_05_31_085644_create_n_sidebars_table.php']);
            $this->info("Migration of $file_path is completed");
        }

        $nav_seeder_slot = '';
        //Sidebar inserting data to database
        $last_row = DB::table('n_sidebars')->latest()->first();
        $serial = 1;
        if ($last_row) {
            $serial = $last_row->id + 1;
        }
        $n_sidebar_id = null;
        $is_parent = true;
        if ($this->parent_navbar) {
            $sidebar = Nsidebar::where('title', $this->parent_navbar)->first();
            if (!$sidebar) {
                $nav_seeder_slot .= "[\n'title' => '$this->parent_navbar',\n'access' => '$this->parent_navbar',\n'route' => NULL,\n'n_sidebar_id' => 'NULL',\n'is_parent' => true,\n'serial' => $serial,\n'status' => 'Active' \n],";
                $sidebar = NSidebar::create([
                    'title' => $this->parent_navbar,
                    'access' => $this->parent_navbar,
                    'is_parent' => true,
                    'serial' => $serial,
                ]);
            }

            $n_sidebar_id = $sidebar->id;
            $is_parent = false;
        }

        $nav_seeder_slot .= "[\n'title' => '$this->model_class_name',\n'access' => '$this->model_class_name',\n'route' => '$this->route_name',\n'n_sidebar_id' => $n_sidebar_id,\n'is_parent' => '$is_parent',\n'serial' => '$serial',\n'status' => 'Active' \n],\n //\$slot";
        NSidebar::create([
            'title' => $this->model_class_name,
            'access' => "$this->model_class_name",
            'route' => $this->route_name,
            'n_sidebar_id' => $n_sidebar_id,
            'is_parent' => $is_parent,
            'serial' => $serial,
            'status' => 'Active',
        ]);
        Cache::forget('nsidebar');
        Cache::rememberForever('nsidebar', function () {
            return NSidebar::with('child_bar')->where('is_parent', 1)->where('status', 'Active')->get();
        });

        //Making navbar Seeder
        // step- 1 => making directory path (using global prefix such as backend,..) for the service class
        $dir_base_path = database_path('seeders');
        $dir_final_path = $this->makeDirectoryWithValidation($dir_base_path, 'backend');

        //Step-2 => Making stub file path and file path for the files thats are needed to create
        $file_name = 'SidebarSeeder.php';
        $file_path = $dir_final_path . "/$file_name";
        $stub_file_path = $this->pakage_stub_path . 'specific/sidebar/seeder.stub';

        if (!file_exists($file_path)) {
            // var_dump('sidebarseeder not exist,noibr');
            //Step-3 => geting the file content and replacing the certain text if needed
            $stub_content = $this->getContentAndReplaceText($stub_file_path, [
                '$slot' => $nav_seeder_slot,
            ]);

            //Step-4 => making the file
            $this->fileMakingAndPutingContent($file_path, $stub_content);
        }else{
            $path = database_path('seeders/backend/SidebarSeeder.php');
            $stub_content = $this->getContentAndReplaceText($path, [
                '//$slot' => $nav_seeder_slot,
            ]);
            // dd($stub_content,$nav_seeder_slot);
            //Step-4 => making the file
            $this->fileMakingAndPutingContent($file_path, $stub_content);
        }
        $this->info("Seeder of $file_path is created");
    }
}
