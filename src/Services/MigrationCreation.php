<?php

namespace Nobir\MiniWizard\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nobir\MiniWizard\Services\BaseCreation;

class MigrationCreation extends BaseCreation
{
    public function generate()
    {
        $table_name = \Illuminate\Support\Str::snake(\Illuminate\Support\Str::plural($this->model_name)); // Derive table name from model name
        $migrationFileName = date('Y_m_d_His') . '_create_' . $table_name . '_table.php';
        $migration_file_path = self::getModulePath(self::MIGRATION,$migrationFileName);
        if(self::fileCheck($migration_file_path)){
            //file creation
            FileModifier::getContent(self::getStubFilePath(self::MIGRATION))
                ->searchingText('{{table_name}}')->replace()->insertingText($table_name)
                ->searchingText('{{slot}}')->replace()->insertingText($this->generateMigrationFields())
                ->save($migration_file_path);
            echo 'Migration created successfully';
            return true;
        }
        echo 'Skiped migration creation';
        return true;

    }

    protected function generateMigrationFields()
    {
        $migrationFields = '';
        foreach ($this->fields as $fieldName => $fieldFunctions) {
            $fieldLine = "\$table";
            foreach ($fieldFunctions as $functinORNumeric => $functionORValue) {
                if(in_array($functinORNumeric, ['enum', 'set'])) {
                    $fieldLine .= "->{$functinORNumeric}('{$fieldName}', '{$functionORValue}')";
                } elseif (in_array($functinORNumeric, ['default', 'length', 'total', 'places'])) {
                    $fieldLine .= "->{$functinORNumeric}('{$functionORValue}')";
                } else {
                    if (in_array($functionORValue, ['foreignIdFor'])) {
                        $fieldLine .= "->{$functionORValue}(\App\Models\\" . $this->model_name . "::class)";
                    } elseif (in_array($functionORValue, ['unsigned', 'constrained', 'cascadeOnDelete', 'cascadeOnUpdate', 'nullable', 'primary', 'unique', 'autoIncrement', 'index', 'restrictOnDelete', 'restrictOnUpdate'])) {
                        $fieldLine .= "->{$functionORValue}()";
                    } else {
                        $fieldLine .= "->{$functionORValue}('{$fieldName}')";
                    }
                }
            }
            $fieldLine .= ';';
            $migrationFields .= "\n\t\t\t" . $fieldLine;
        }
        return $migrationFields;
    }

}

