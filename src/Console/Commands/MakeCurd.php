<?php

namespace Nobir\CurdByCommand\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;

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
    protected $model_class_name;
    protected $model_functions = '';
    protected $model_fillable = 'protected $fillable = ["';
    protected $add_field_msg = 'Are you want to add a field?';
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

        // Database fields collect from the command plate
        $this->collectFields();

        //Migraton creation
        // if ($this->confirm('Are you want to make Migration', true)) {
        //     $this->makeMigration();
        // }

        //Model creation
        // if ($this->confirm('Are you want to make Model', true)) {
        //     $this->makeModel();
        // }

        //Route creation
        // if ($this->confirm('Are you want to make Route', true)) {
        //     $this->makeRoute();
        // }

        //Resource Controller creation
        if ($this->confirm('Are you want to make Resource Controller', true)) {
          $this->makeController();
        }

        $this->info('Process Terminate');
    }

    protected function collectFields()
    {
        $i = 0;
        while (true) {
            if ($this->confirm($this->add_field_msg, true)) {
                $i++;
                //Database field name
                $this->data[$i]['field_name'] = $this->ask('Field Name:');

                //Data type
                $this->data[$i]['data_type'] = $this->choice(
                    'Enter a data type?',
                    $this->data_type,
                    'string'
                );

                //is nullable
                $this->data[$i]['nullable'] = $this->confirm('Is the field nullable?');

                //default values
                if ($this->confirm('Have any default values?')) {
                    $this->data[$i]['default_value'] = $this->ask('Default value is:');
                } else {
                    $this->data[$i]['default_value'] = null;
                }

                $this->add_field_msg = 'Are you want to add another field?';
            } else {
                break;
            }
        }
        $this->info('Processing the field');
        $this->makeReady();
    }

    protected function replaceFillableField($replaceable_field)
    {
        $field = str($replaceable_field)->snake()->value() . '_id';
        $this->model_fillable = str_replace($replaceable_field, $field, $this->model_fillable);
    }


    protected function makeReady()
    {
        foreach ($this->data as $key => $datum) {
            //indentation currection
            if ($key != 1) {
                $this->migration_slot .= "\n\t\t\t";
                $this->model_fillable .= ", ";
            }
            //model fillable properties
            $this->model_fillable .= "{$datum['field_name']}";

            switch ($datum['data_type']) {
                    //Logic for Foreign Id For
                case 'foreignIdFor':
                    // for migration
                    $this->migration_slot .= "\$table->{$datum['data_type']}(App\Models\\{$datum['field_name']}::class)";
                    if ($datum['nullable']) {
                        $this->migration_slot .= "->nullable()";
                    }
                    if ($datum['default_value']) {
                        $this->migration_slot .= "->default('{$datum['default_value']}')";
                    }
                    $this->migration_slot .= "->constrained()->cascadeOnUpdate()->cascadeOnDelete()";
                    $this->replaceFillableField($datum['field_name']);
                    //for model
                    $this->model_functions .= "public function {$datum['field_name']}(){\n\t\treturn \$this->belongsTo({$datum['field_name']}::class);\n\t}";
                    break;

                    //For all Common Data Type
                default:
                    $this->migration_slot .= "\$table->{$datum['data_type']}('{$datum['field_name']}')";
                    if ($datum['nullable']) {
                        $this->migration_slot .= "->nullable()";
                    }
                    if ($datum['default_value']) {
                        $this->migration_slot .= "->default('{$datum['default_value']}')";
                    }
                    break;
            }
            $this->migration_slot .= ';';
        }
        $this->model_fillable .= '"];';
    }

    //Migration creation
    protected function makeMigration()
    {
        //creation table name
        $table_name = str($this->model_class_name)->snake()->plural()->value();

        //Content extract from stub
        $stub_content = file_get_contents($this->pakage_stub_path.'migration.stub');

        //Replace the table name
        $content_with_table_name = str_replace('$table_name', $table_name, $stub_content);

        //setup the migration field
        $content_ready = str_replace('$slot', $this->migration_slot, $content_with_table_name);

        $file_name = date('Y_m_d_His') . '_' . 'create_' . $table_name . '_table.php';
        $file_path = database_path('migrations/' . $file_name);
        file_put_contents($file_path, $content_ready);
        $this->info("The migration file '$file_name' is created Successfully in your migration folder");
    }

    //Model creation
    protected function makeModel()
    {
        $model_name = $this->model_class_name;
        //model content load from stub
        $stub_content = file_get_contents($this->pakage_stub_path.'model.stub');

        //replace the model name
        $content_with_name = str_replace('$model_name', $model_name, $stub_content);

        $full_content = str_replace('$slot', $this->model_fillable . "\n\n\t" . $this->model_functions, $content_with_name);
        $full_file_name = $model_name . '.php';
        $file_path = app_path('Models/' . $full_file_name);
        file_put_contents($file_path, $full_content);
        $this->info("The model file '$full_file_name' is created Successfully in your model folder");
    }

    //Route creation
    protected function makeRoute()
    {
        $model_name = $this->model_class_name;
        $controller_name = $model_name."Controller";
        $route_name = str($model_name)->kebab()->value();

        //Route slot creation
        $route_slot = '';
        $base_route = '';
        $route_group_first_code = '';
        $route_group_last_code = "";

        //Route group preparation
        $route_group = $this->confirm('Has the route group?', true);
        if($route_group){
            $route_group_first_code .= 'Route::';
            if($middleware = $this->ask('Enter Middleware for the route group (Presss enter to skip)')){
                $route_group_first_code .= "middleware('$middleware')->";
            }
            if($prefix = $this->ask('Enter prefix for the route group (Presss enter to skip)')){
                $route_group_first_code .= "prefix('$prefix')->";
            }
            if($name = $this->ask('Enter name for the route group (Presss enter to skip)')){
                $route_group_first_code .= "name('$name')->";
            }
            $route_group_first_code .= "group(function(){\n\t";
            $route_group_last_code = "});\n";
        }
        //base route
        $base_route .= "Route::resource('/$route_name','App\Http\Controllers\\{$controller_name}');\n";

        //Full route
        $route_slot = $route_group_first_code . $base_route.$route_group_last_code;

        $file_path = base_path('routes/mini-wizard.php');
        //route content load from stub if not exist
        if(file_exists($file_path)){
            $stub_content = file_get_contents($file_path);
            if($route_group_first_code){
                $replaceable_route_with_group = $route_group_first_code.$base_route;
                $full_content = str_replace($route_group_first_code, $replaceable_route_with_group, $stub_content);
            }else{
                $full_content = $stub_content."\n".$route_slot;
            }
            file_put_contents($file_path, $full_content);
            $this->info("The route file 'mini-wizard' is updated Successfully");
        }else{
            $stub_content = file_get_contents($this->pakage_stub_path.'route.stub');

            $full_content = str_replace('$slot', $route_slot, $stub_content);

            file_put_contents($file_path, $full_content);
            $this->info("The route file 'mini-wizard' is created Successfully in your routes folder");

            //including the mini-wizard.php file in the web.php
            $web_file_path = base_path('/routes/web.php');
            $web_route_content = file_get_contents($web_file_path);
            $web_route_final_content = str_replace("<?php", "<?php \n require __DIR__ . '/mini-wizard.php';", $web_route_content);
            file_put_contents($web_file_path,$web_route_final_content);
            $this->info("The route file 'mini-wizard' is also included in web.php. So no tension.");

        }
    }

    protected function makeController(){
        $controller_name = $this->model_class_name.'Controller';
        
    }
}

