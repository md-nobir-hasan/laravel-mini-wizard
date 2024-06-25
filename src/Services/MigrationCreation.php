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
                    ->searchingText('{{tableName}}')->replace()->insertingText($table_name)
                    ->searchingText('{{migrationFields}}')->replace()->insertingText($this->generateMigrationFields())
                    ->save($migrationFileName);
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
