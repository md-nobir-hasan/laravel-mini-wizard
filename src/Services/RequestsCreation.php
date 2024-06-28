<?php

namespace Nobir\MiniWizard\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nobir\MiniWizard\Services\BaseCreation;

class RequestsCreation extends BaseCreation
{
    public function generate()
    {
        $this->generateStoreRequest();
        $this->generateUpdateRequest();
    }

    public function generateStoreRequest()
    {
        // Derive file name from model name
        $FileName = 'Store'.$this->model_name . 'Request.php';

        //reqest path collection
        $file_path = self::getModulePath(self::REQUESTS, $FileName);

        //overwrite or skip logic if exist the file
        if (self::fileOverwriteOrNot($file_path)) {

            /**
             * dynamic properties preparation
             */

            //namespace derived
            $name_space = self::getModuleNamespace(self::REQUESTS);


            //slot preparation
            $slot = $this->generateSlot();

            //Finally the file modification if exist or creation if not exist
            FileModifier::getContent(self::getStubFilePath(self::STORE_REQUEST))
                ->searchingText('{{name_space}}')->replace()->insertingText($name_space)
                ->searchingText('{{model_name}}')->replace()->insertingText($this->model_name)
                ->searchingText('{{slot}}')->replace()->insertingText($slot)
                ->save($file_path);

            $this->info('Store Request created successfully');


            return true;
        }


        $this->info('Skiped Store Request creation');

        return true;
    }

    public function generateUpdateRequest()
    {
        // Derive file name from model name
        $FileName = 'Update' . $this->model_name . 'Request.php';

        //reqest path collection
        $file_path = self::getModulePath(self::REQUESTS, $FileName);

        //overwrite or skip logic if exist the file
        if (self::fileOverwriteOrNot($file_path)) {

            /**
             * dynamic properties preparation
             */

            //namespace derived
            $name_space = self::getModuleNamespace(self::REQUESTS);


            //slot preparation
            $slot = $this->generateSlot('update');

            //Finally the file modification if exist or creation if not exist
            FileModifier::getContent(self::getStubFilePath(self::STORE_REQUEST))
                ->searchingText('{{name_space}}')->replace()->insertingText($name_space)
                ->searchingText('{{model_name}}')->replace()->insertingText($this->model_name)
                ->searchingText('{{slot}}')->replace()->insertingText($slot)
                ->save($file_path);

            $this->info('Store Request created successfully');


            return true;
        }


        $this->info('Skiped Store Request creation');

        return true;
    }
    /**
     *  public function country(){
     *   return $this->belongsTo(country::class);
     *}
     */
    protected function generateSlot($update = null)
    {
        $slot = '';
        foreach ($this->fields as $fieldName => $fieldFunctions) {

            //field name set
            $slot .= "\n\t\t\t\t'$fieldName' => [";

            /**
             * validation  for data types
             */

            /**
             * Search data type  in values
             */

            //values also contain datatype, according to data type some validation are set
            foreach ($fieldFunctions as $value) {
                //value of ('bigInteger', 'decimal', 'double', 'float', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger') is set if default value is not set
                if (in_array($value, ['bigInteger', 'decimal', 'double', 'float', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'])) {
                    $slot .= "'numeric'";
                    break;
                }
                if (in_array($value, ['longText', 'macAddress', 'mediumText', 'string', 'text', 'tinyText', 'smallInteger', 'tinyInteger', 'ulid', 'uuid'])) {
                    $slot .= "'string'";
                    break;
                }

            }
            //value of (boolean) is set if default value is not set
            if (in_array('boolean', $fieldFunctions,)) {
                $slot .= "'boolean',";
            }




            /**
             * Other properties validation
             */

            //set nullable or required
            if (in_array('unsigned', $fieldFunctions)) {
                $slot .= "'min:1',";
            }

            //set unique or required
            $table_name = self::modelToTableName($this->model_name);

            if (in_array('unique', $fieldFunctions)) {
                $slot .= "'unique:$table_name,$fieldName',";
                if($update){
                    $slot .= "'unique:$table_name,$fieldName',";
                }
            }

             //set exist in case of foreing id
            if (in_array('foreignIdFor',$fieldFunctions )) {
                $slot .= "'exists:$table_name,$fieldName',";
            }



            /**
             * validation using keys
             */
            $keys = array_keys($fieldFunctions);

            foreach($keys as $key){

                //set max value using length
                if($key == 'length'){
                    $slot .= "'max:$key',";
                }

                //in value set for enum values
                elseif (in_array($key, ['enum', 'set'])) {
                    $values = $fieldFunctions[$key];
                    $slot .= "'string','in:";
                    foreach ($values as $value) {
                        $slot .= "'$value'";
                    }
                    $slot .= "',";
                }

            }


        }
        return $slot;
    }


}
