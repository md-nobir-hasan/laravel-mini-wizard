<?php

namespace Nobir\MiniWizard\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nobir\MiniWizard\Services\BaseCreation;

class MigrationCreation extends BaseCreation
{
    public function generate()
    {
        //get table name
        $table_name = \Illuminate\Support\Str::snake(\Illuminate\Support\Str::plural($this->model_name)); // Derive table name from model name

        //ge file name
        $migrationFileName = date('Y_m_d_His') . '_create_' . $table_name . '_table.php';

        //prepare file path
        $migration_file_path = self::getModulePath(self::MIGRATION, $migrationFileName);


        if (self::fileOverwriteOrNot($migration_file_path)) {

            //file creation
            FileModifier::getContent(self::getStubFilePath(self::MIGRATION))
                ->searchingText('{{table_name}}')->replace()->insertingText($table_name)
                ->searchingText('{{table_name}}')->replace()->insertingText($table_name)
                ->searchingText('{{slot}}')->replace()->insertingText($this->generateMigrationFields())
                ->save($migration_file_path);

            $this->info('Migration created successfully');

            try {

                Artisan::call('migrate');

                echo Artisan::output();

            } catch (\Exception $e) {

                echo $this->info('migration fail');

            }

            /**
             * if the migrations are under any folder then this folder have to register to appservice provider
             */
            $this->loadMigration();

            return true;
        }


        $this->info('Skiped migration creation') ;


        return true;
    }

    protected function generateMigrationFields()
    {
        $migrationFields = '';

        foreach ($this->fields as $fieldName => $fieldFunctions) {

            $fieldLine = "\$table";

            foreach ($fieldFunctions as $functinORNumeric => $functionORValue) {

                if (in_array($functinORNumeric, ['enum', 'set'])) {

                    $fieldLine .= "->{$functinORNumeric}('{$fieldName}', '{$functionORValue}')";

                }
                elseif (in_array($functinORNumeric, ['default', 'length', 'total', 'places'])) {

                    $fieldLine .= "->{$functinORNumeric}('{$functionORValue}')";

                }
                else {
                    if (in_array($functionORValue, ['foreignIdFor'])) {

                        $model_name = self::foreignKeyToModelName($fieldName);

                        $name_space = self::getModuleNamespace(self::MODEL);

                        if (!file_exists(self::getModulePath(self::MODEL) . '/' . self::foreignKeyToModelName($fieldName) . '.php')) {

                            $name_space = 'App\Models';
                        }

                        $fieldLine .= "->{$functionORValue}(\\$name_space\\" . $model_name . "::class)";

                    }
                    elseif (in_array($functionORValue, ['unsigned', 'constrained', 'cascadeOnDelete', 'cascadeOnUpdate', 'nullable', 'primary', 'unique', 'autoIncrement', 'index', 'restrictOnDelete', 'restrictOnUpdate'])) {

                        $fieldLine .= "->{$functionORValue}()";

                    }
                    else {
                        $fieldLine .= "->{$functionORValue}('{$fieldName}')";
                    }
                }
            }

            $fieldLine .= ';';

            $migrationFields .= "\n\t\t\t" . $fieldLine;

        }

        return $migrationFields;
    }

    public function loadMigration(){
        //derived app service provider path from pathmanager
        $app_service_provider_path = self::appServiceProviderPath();

        //migration path from pathmanager
        $migration_seffix = self::getModuleSuffix(self::MIGRATION);

        if ($migration_seffix) {
            (new FileModifier($app_service_provider_path))->searchingText('{', 3)->insertAfter()->insertingText("\n\t\t\$this->loadMigrationsFrom([\n\t\t\tdatabase_path('migrations'),\n\t\t\tdatabase_path('migrations/$migration_seffix'),\n\t\t]);")
                ->save();
        }
    }
}
