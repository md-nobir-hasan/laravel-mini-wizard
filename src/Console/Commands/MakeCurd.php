<?php

namespace nobir\CurdByCommand\Console\Commands;

use Illuminate\Console\Command;

class MakeCurd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nobir:curd';

    // The order of these data type is not change able
    protected $data_type = [
            'bigIncrements','bigInteger', 'binary', 'boolean', 'char', 'dateTimeTz', 'dateTime', 'date', 'decimal', 'double', 'enum', 'float', 'foreignId',
            'foreignIdFor', 'foreignUlid', 'foreignUuid', 'geography', 'geometry', 'id', 'increments', 'integer', 'ipAddress', 'json', 'jsonb', 'longText',
            'macAddress', 'mediumIncrements', 'mediumInteger', 'mediumText', 'morphs', 'nullableMorphs', 'nullableTimestamps', 'nullableUlidMorphs', 'nullableUuidMorphs',
            'rememberToken', 'set', 'smallIncrements', 'smallInteger', 'softDeletesTz', 'softDeletes', 'string', 'text', 'timeTz','time','timestampTz',
            'timestamp','timestampsTz','timestamps','tinyIncrements','tinyInteger','tinyText','unsignedBigInteger','unsignedInteger','unsignedMediumInteger',
            'unsignedSmallInteger','unsignedTinyInteger','ulidMorphs','uuidMorphs','ulid','uuid', 'year'
    ];

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

        if ($this->confirm('Are you want to add a field?', true)) {
            //Database field name
            $n['field_name'] = $this->ask('Field Name:');

            //Data type
            $n['data_type'] = $this->choice(
                'Enter a data type?',
                $this->data_type,
                'string'
            );

            //is nullable
            $n['nullable'] = $this->confirm('Is the field nullable?');

            //default values
            if($this->confirm('Have any default values?')){
                $n['default_values'] = $this->ask('Values (Seperate the values using comma):');
            }
            
        }

        $this->info('Process Terminate');
    }
}
