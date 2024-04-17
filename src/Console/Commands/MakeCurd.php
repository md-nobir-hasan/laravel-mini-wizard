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
    protected $data=[];
    protected $migration_slot='';
    protected $model_functions='';
    protected $model_fillable='protected $fillable = ["';
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
        //make ready the model and table names
        $model_name = $this->argument('model');
        $table_name = str($model_name)->snake()->plural();

        // Database fields collect from the command plate
        $data_fields_names = $this->collectFields();

        //Migraton creation
        // if($this->confirm('Are you want to make Migration',true)){
        //     $this->makeMigration($data_fields_names,$table_name->value());
        // }

        //Model create
        if($this->confirm('Are you want to make Model',true)){
            $this->makeModel($data_fields_names,$model_name);
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
                }else{
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

    protected function makeReady(){
        foreach($this->data as $key => $datum){
            //indentation currection
            if($key != 1){
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
        }
        $this->model_fillable .= '"];';
    }

    protected function makeMigration($data_fields,$table_name){
        $stub_content = file_get_contents(__DIR__.'/../../stubs/migration.stub');
        $content_with_table_name = str_replace('$table_name',$table_name,$stub_content);
        $fields = '';
        // if(count($data_fields)>0){
            // foreach($data_fields as $key => $field){
                //3 tab space added for currect indentation
                // if($key != 1){
                //     $fields .= "\t\t\t";
                // }

                //migration according to datatype
                // switch($field['data_type']){

                //     //Logic for Foreign Id For
                //     case 'foreignIdFor':
                //         $fields .= "\$table->{$field['data_type']}(App\Models\\{$field['field_name']}::class)";
                //         if ($field['nullable']) {
                //             $fields .= "->nullable()";
                //         }
                //         if ($field['default_value']) {
                //             $fields .= "->default('{$field['default_value']}')";
                //         }
                //         $fields .= "->constrained()->cascadeOnUpdate()->cascadeOnDelete()";
                //         break;

                //     //For all Common Data Type
                //     default:
                //         $fields .= "\$table->{$field['data_type']}('{$field['field_name']}')";
                //         if ($field['nullable']) {
                //             $fields .= "->nullable()";
                //         }
                //         if ($field['default_value']) {
                //             $fields .= "->default('{$field['default_value']}')";
                //         }
                //         break;
                // }






                // $fields .= ";\n";
            // }
            // $fields = substr($fields,0,-1);

        $content_ready = str_replace('$fields', $fields, $content_with_table_name);
        $file_name = 'create_'.$table_name.'_table';
        $file_path = database_path('migrations/'.date('Y_m_d_His').'_'.$file_name.'.php');
        file_put_contents($file_path, $content_ready);
        $this->info("The migration file '$file_name' is created Successfully in your migration folder");
        // }

    }

    protected function makeModel($data_fields,$file_name){
        //model content load from stub
        $stub_content = file_get_contents(__DIR__.'/../../stubs/model.stub');

        //replace the model name
        $content_with_name = str_replace('$model_name', $file_name, $stub_content);

        $full_content = str_replace('$slot', $this->model_fillable."\n\n\t".$this->model_functions, $content_with_name);
        $file_name = $file_name.'.php';
        $file_path = app_path('Models/'.$file_name);
        file_put_contents($file_path, $full_content);
        $this->info("The migration file '$file_name' is created Successfully in your migration folder");
        // }
    }


}
