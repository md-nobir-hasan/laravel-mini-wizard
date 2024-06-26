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

        //file creation
        FileModifier::getContent(self::migration_stub_path)
                    ->searchingText('{{table_name}}')->replace()->insertingText($table_name)
                    ->searchingText('{{slot}}')->replace()->insertingText($this->generateMigrationFields())
                    ->save($migrationFileName);
    }

    protected function generateMigrationFields()
    {
        $migrationFields = '';
        foreach ($this->fields as $fieldName => $fieldFunctions) {
            $fieldLine = "\$table";

            foreach ($fieldFunctions as $functinORNumeric => $functionORValue) {
                if(in_array($functinORNumeric, ['enum', 'set'])) {
                    $fieldLine .= "->{$functinORNumeric}('{$fieldName}', '{$functionORValue}')";
                } elseif (in_array($functinORNumeric, ['default'])) {
                    $fieldLine .= "->{$functinORNumeric}('{$functionORValue}')";
                } else {
                    if (in_array($functinORNumeric, ['foreignIdFor'])) {
                        $fieldLine .= "->{$functinORNumeric}(\App\Models\\" . $this->model_name . "::class)";
                    } else {
                        $fieldLine .= "->{$functionORValue}()";
                    }
                }
            }
            $fieldLine .= ';';
            $migrationFields .= "\n\t\t\t" . $fieldLine;
        }
        return $migrationFields;
    }


}
