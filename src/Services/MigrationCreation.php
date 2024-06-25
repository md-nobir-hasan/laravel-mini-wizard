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
        $migrationTemplate = $this->getStub(self::MIGRATION);
        $migrationContent = str_replace(
            ['{{tableName}}', '{{migrationFields}}'],
            [$table_name, $this->generateMigrationFields()],
            $migrationTemplate
        );

        File::put(database_path("/migrations/{$migrationFileName}"), $migrationContent);
    }

    protected function generateMigrationFields()
    {
        $migrationFields = '';
        foreach ($this->fields as $fieldName => $field) {
            $fieldLine = "\$table";

            foreach ($field as $key => $value) {
                if (is_numeric($key)) {
                    if ($value === 'foreignIdFor') {
                        $fieldLine .= "->foreignIdFor(\App\Models\\" . Str::studly(Str::singular($fieldName)) . "::class, '{$fieldName}')";
                    } else {
                        $fieldLine .= "->{$value}('{$fieldName}')";
                    }
                } elseif ($key === 'enum') {
                    $enumValues = implode("', '", $value);
                    $fieldLine .= "->enum('{$fieldName}', ['{$enumValues}'])";
                } elseif ($key === 'default') {
                    $fieldLine .= "->default('{$value}')";
                } else {
                    $fieldLine .= "->{$key}()";
                }
            }

            $fieldLine .= ';';
            $migrationFields .= "\n\t\t\t" . $fieldLine;
        }
        return $migrationFields;
    }


}
