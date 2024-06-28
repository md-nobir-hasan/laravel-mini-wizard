<?php

namespace Nobir\MiniWizard\Services;

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

            //namespace derived
            $table_name = self::modelToTableName($this->model_name);


            //request name derived
            $request_name = "Store{$this->model_name}Request";


            //slot preparation
            $slot = $this->generateSlot();

            //Finally the file modification if exist or creation if not exist
            FileModifier::getContent(self::getStubFilePath(self::REQUESTS))
                ->searchingText('{{name_space}}')->replace()->insertingText($name_space)
                ->searchingText('{{request_name}}')->replace()->insertingText($request_name)
                ->searchingText('{{table_name}}')->replace()->insertingText($table_name)
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

            //namespace derived
            $table_name = self::modelToTableName($this->model_name);

            //request name derived
            $request_name = "Update{$this->model_name}Request";

            //slot preparation
            $slot = $this->generateSlot('update');

            //Finally the file modification if exist or creation if not exist
            FileModifier::getContent(self::getStubFilePath(self::REQUESTS))
                ->searchingText('{{name_space}}')->replace()->insertingText($name_space)
                ->searchingText('{{request_name}}')->replace()->insertingText($request_name)
                ->searchingText('{{table_name}}')->replace()->insertingText($table_name)
                ->searchingText('{{slot}}')->replace()->insertingText($slot)
                ->save($file_path);

            $this->info('Update Request created successfully');

            return true;
        }


        $this->info('Skiped Update Request creation');

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
            $slot .= "\n\t\t\t'$fieldName' => [";


            //set nullable or required validation
            if (in_array('nullable', $fieldFunctions)) {
                $slot .= "'nullable',";
            } else {
                $slot .= "'required',";
            }




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
                    $slot .= "'numeric',";
                    break;
                }
                if (in_array($value, ['longText', 'macAddress', 'mediumText', 'string', 'text', 'tinyText', 'smallInteger', 'tinyInteger', 'ulid', 'uuid'])) {
                    $slot .= "'string',";
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



            //set min validation
            if (in_array('unsigned', $fieldFunctions)) {
                $slot .= "'min:1',";
            }

            //set unique or required
            $table_name = self::modelToTableName($this->model_name);

            if (in_array('unique', $fieldFunctions)) {
                $unique_vali = "'unique:$table_name,$fieldName',";
                if($update){
                    $m_name_snake = self::modelToBelongsToName($this->model_name);
                    $unique_vali = "'unique:$table_name,$fieldName'.\$this->{$m_name_snake}->id,";
                }
                $slot .= $unique_vali;
            }

             //set exist in case of foreing id
            if (in_array('foreignIdFor',$fieldFunctions )) {
                $table_name_for_this = self::modelToTableName(self::foreignKeyToModelName($fieldName));
                $slot .= "'exists:$table_name_for_this,id',";
            }



            /**
             * validation using keys
             */
            $keys = array_keys($fieldFunctions);

            foreach($keys as $key){

                //set max value using length
                if($key == 'length'){
                    $length_value = $fieldFunctions[$key];
                    $slot .= "'max:$length_value',";
                }

                //in value set for enum values
                elseif (in_array($key, ['enum', 'set'])) {
                    $values = $fieldFunctions[$key];
                    $slot .= "'string','in:";
                    foreach ($values as $value) {
                        $slot .= "$value,";
                    }
                    $slot .= "',";
                }

            }
            $slot .= "],";

        }
        return $slot;
    }


}
