<?php

namespace Nobir\MiniWizard\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Nobir\MiniWizard\Services\AllFunctionalityClass;
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
        'enum' => ['default','nullable'],
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
    protected function dataFilter(){
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

        $allFunctionality = new AllFunctionalityClass($this->fields, $this->model_class_name,$this->models_name);

        //Model creation
        // if ($this->confirm('Do you want to create the model?', true)) {
        //     $allFunctionality->createModel();
        // }

        // //migration creation
        // if ($this->confirm('Do you want to create the migration?', true)) {
        //     $allFunctionality->createMigration();
        // }

        //seeder creation
        if ($this->confirm('Do you want to create the seeder?', true)) {
            $allFunctionality->createSeeder();
        }

        //factory creation
        if ($this->confirm('Do you want to create the factory?', true)) {
            $allFunctionality->createFactory();
        }

        /**
         *  requests creation
         * */
        if ($this->confirm('Do you want to create the requests?', true)) {
            $allFunctionality->createRequests();
        }
    }
}
