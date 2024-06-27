<?php

namespace Nobir\MiniWizard\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nobir\MiniWizard\Services\BaseCreation;

class ModelCreation extends BaseCreation
{
    public function generate()
    {
        // Derive table name from model name
        $modelFileName = $this->model_name . '.php';
        $model_file_path = self::getModulePath(self::MODEL, $modelFileName);
        if (self::fileCheck($model_file_path)) {
            $name_space = self::getModuleNamespace(self::MODEL);
            $fillable_properties = $this->fillable();
            $relationships = $this->generateRelationships();
            //file creation
            FileModifier::getContent(self::getStubFilePath(self::MODEL))
                ->searchingText('{{name_space}}')->replace()->insertingText($name_space)
                ->searchingText('{{model_name}}')->replace()->insertingText($this->model_name)
                ->searchingText('{{fillable_properties}}')->replace()->insertingText($fillable_properties)
                ->searchingText('{{relationships}}')->replace()->insertingText($relationships)
                ->save($model_file_path);
            echo 'Model created successfully';
            return true;
        }
        echo 'Skiped model creation';
        return true;
    }

    /**
     *  public function country(){
     *   return $this->belongsTo(country::class);
     *}
     */
    protected function generateRelationships()
    {
        $relationships = '';
        if ($this->models_name) {
            foreach ($this->models_name as $model_name) {
                //searching the model class
                $name_space = self::getModuleNamespace(self::MODEL);
                $parent_model_path = self::getModulePath(self::MODEL) . '/' . $model_name . '.php';
                if (!file_exists($parent_model_path)) {
                    $name_space = 'App\Models';
                    $parent_model_path = app_path('Models') . '/' . $model_name . '.php';
                }
                $func_name = self::modelToBelongsToName($model_name);
                $relationships .= "\n\tpublic function $func_name(){\n\t\treturn \$this->belongsTo(\\$name_space\\$model_name::class);\n\t}";
                $this->createRelationshipInParentModel($parent_model_path);
            }
        }
        return $relationships;
    }

    protected function fillable()
    {
        $fillable_arry = array_keys($this->fields);
        $fillable = '';
        foreach ($fillable_arry as $fill) {
            $fill;
            $fillable .= ", '$fill'";
        }
        return $fillable;
    }

    protected function createRelationshipInParentModel($model_path)
    {
        $func_name = self::modelToTableName($this->model_name);
        $name_space = self::getModuleNamespace(self::MODEL);
        $relationship = "\n\tpublic function $func_name(){\n\t\treturn \$this->hasMany(\\$name_space\\$this->model_name::class);\n\t}";

        FileModifier::getContent($model_path)
            ->searchingText('];')->insertAfter()->insertingText($relationship)
            ->save();
    }
}
