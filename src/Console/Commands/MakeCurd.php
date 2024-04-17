<?php

namespace Nobir\CurdByCommand\Console\Commands;

use Illuminate\Console\Command;

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
        $model_name = str($this->argument('model'))->title();
        $table_name = $model_name->lower()->plural();
        // dd($model_name,$table_name);
        $data_fields_names = $this->collectFields();
        // dd($data_fields_names);
        // Migration file creating
        if($this->confirm('Are you want to make migration',true)){
            $this->makeMigration($data_fields_names,$table_name->value());
        }

        $this->info('Process Terminate');
    }

    protected function collectFields()
    {
        $i = 0;
        $n=[];
        while (true) {
            if ($this->confirm($this->add_field_msg, true)) {
                $i++;
                //Database field name
                $n[$i]['field_name'] = $this->ask('Field Name:');

                //Data type
                $n[$i]['data_type'] = $this->choice(
                    'Enter a data type?',
                    $this->data_type,
                    'string'
                );

                //is nullable
                $n[$i]['nullable'] = $this->confirm('Is the field nullable?');

                //default values
                if ($this->confirm('Have any default values?')) {
                    $n[$i]['default_value'] = $this->ask('Default value is:');
                }else{
                    $n[$i]['default_value'] = null;

                }
                $this->add_field_msg = 'Are you want to add another field?';
            } else {
                break;
            }
        }
        return $n;
    }

    protected function makeMigration($data_fields,$table_name){
        $stub_content = file_get_contents(__DIR__.'/../../stubs/migration.stub');
        $content_with_table_name = str_replace('$table_name',$table_name,$stub_content);
        $fields = '';
        // if(count($data_fields)>0){
            foreach($data_fields as $key => $field){

                //3 tab space added for currect indentation
                if($key != 1){
                    $fields .= "\t\t\t";
                }

                //migration according to datatype
                switch($field['data_type']){

                    //Logic for Foreign Id For
                    case 'foreignIdFor':
                        $fields .= "\$table->{$field['data_type']}(App\Models\\{$field['field_name']}::class)";
                        if ($field['nullable']) {
                            $fields .= "->nullable()";
                        }
                        if ($field['default_value']) {
                            $fields .= "->default('{$field['default_value']}')";
                        }
                        $fields .= "->constrained()->cascadeOnUpdate()->cascadeOnDelete()";
                        break;

                    //For all Common Data Type
                    default:
                        $fields .= "\$table->{$field['data_type']}('{$field['field_name']}')";
                        if ($field['nullable']) {
                            $fields .= "->nullable()";
                        }
                        if ($field['default_value']) {
                            $fields .= "->default('{$field['default_value']}')";
                        }
                        break;
                }






                $fields .= ";\n";
            }
            $fields = substr($fields,0,-1);

        $content_ready = str_replace('$fields', $fields, $content_with_table_name);
        $file_name = 'create_'.$table_name.'_table';
        $file_path = database_path('migrations/'.date('Y_m_d_His').'_'.$file_name.'.php');
        file_put_contents($file_path, $content_ready);
        $this->info("The migration file '$file_name' is created Successfully in your migration folder");
        // }

    }
}
