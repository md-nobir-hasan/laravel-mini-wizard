<?php

namespace Nobir\MiniWizard\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nobir\MiniWizard\Services\BaseCreation;

class SeederCreation extends BaseCreation
{
    public function generate()
    {
        // Derive table name from seeder name
        $FileName = $this->model_name . 'Seeder.php';
        $file_path = self::getModulePath(self::SEEDER, $FileName);
        if (self::fileOverwriteOrNot($file_path)) {
            $name_space = self::getModuleNamespace(self::SEEDER);
            $table_name = self::modelToTableName($this->model_name);
            $slot = $this->generateSlot();
            //file creation
            FileModifier::getContent(self::getStubFilePath(self::SEEDER))
                ->searchingText('{{name_space}}')->replace()->insertingText($name_space)
                ->searchingText('{{model_name}}')->replace()->insertingText($this->model_name)
                ->searchingText('{{table_name}}')->replace()->insertingText($table_name)
                ->searchingText('{{slot}}')->replace()->insertingText($slot)
                ->save($file_path);
            echo 'seeder created successfully';
            return true;
        }
        echo 'Skiped seeder creation';
        return true;
    }

    /**
     *  public function country(){
     *   return $this->belongsTo(country::class);
     *}
     */
    protected function generateSlot()
    {
        $slot = '';
        foreach ($this->fields as $fieldName => $fieldFunctions) {

            //field name set
            $slot .= "\n\t\t\t\t'$fieldName' =>";

            //default value set
            if (isset($fieldFunctions['default']) && $fieldFunctions['default']) {
                $default_value = $fieldFunctions['default'];
                $slot .= (in_array($default_value, ['boolean']) ? "$default_value," : "'$default_value'," ). " //Default value";
                continue;
            }

            //if default value not found then the loop for value setting
            foreach ($fieldFunctions as $functinORNumeric => $functionORValue) {

                //value of (enum, set) is set if default value is not set
                if (in_array($functinORNumeric, ['enum', 'set'])) {
                    $slot .= "'{$functinORNumeric[0]}',";
                    break;
                }

                //value of (foreignIdFor) is set if default value is not set
                if (in_array($functionORValue, ['foreignIdFor'])) {
                    $slot .= 1 . ",";
                    break;
                }

                //value of (boolean) is set if default value is not set
                if (in_array($functionORValue, ['boolean'])) {
                    $slot .= true . ',';
                    break;
                }

                //value of ('bigInteger', 'decimal', 'double', 'float', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger') is set if default value is not set
                if (in_array($functionORValue, ['bigInteger', 'decimal', 'double', 'float', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'])) {
                    $slot .= Rand(200, 10000) . ',';
                    break;
                }

                //value of (that datatype which value usually string) is set if default value is not set
                $sentence = fake()->paragraph(1);
                $slot .=  "'$sentence',";
                break;
            }
        }
        return $slot;
    }
}
