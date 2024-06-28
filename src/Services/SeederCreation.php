<?php

namespace Nobir\MiniWizard\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nobir\MiniWizard\Services\BaseCreation;

class SeederCreation extends BaseCreation
{
    public function generate()
    {
        // Derive file name from seeder name
        $FileName = $this->model_name . 'Seeder.php';

        //seeder path collection
        $file_path = self::getModulePath(self::SEEDER, $FileName);

        //overwrite or skip logic if exist the file
        if (self::fileOverwriteOrNot($file_path)) {

            /**
             * dynamic properties preparation for the seeder
             */

            //namespace derived
            $name_space = self::getModuleNamespace(self::SEEDER);

            //table name derived
            $table_name = self::modelToTableName($this->model_name);

            //slot preparation
            $slot = $this->generateSlot();

            //Finally the file modification if exist or creation if not exist
            FileModifier::getContent(self::getStubFilePath(self::SEEDER))
                ->searchingText('{{name_space}}')->replace()->insertingText($name_space)
                ->searchingText('{{model_name}}')->replace()->insertingText($this->model_name)
                ->searchingText('{{table_name}}')->replace()->insertingText($table_name)
                ->searchingText('{{slot}}')->replace()->insertingText($slot)
                ->save($file_path);
            echo 'seeder created successfully';

            //Specific file namespace creation
            $name_space = $name_space.'\\'.$this->model_name. 'Seeder';
            //the created file seeding
            $this->seeding($name_space);

            // database modification so that when you run any command for seeding such as migrate:fresh --seed, the seeder work finely
            $this->databaseSeederModification($name_space);
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

    protected function seeding($name_space){
        // dd($name_space);
        echo $name_space;
       try{
            Artisan::call('db:seed', [
                '--class' => $name_space
            ]);
            echo Artisan::output();
       }catch(\Exception $e){
            echo "Database Seedeing Problem \n";
       }
    }

    protected function databaseSeederModification($name_space){

        FileModifier::getContent(database_path('seeders/DatabaseSeeder.php'))->searchingText('{', 2)
            ->insertAfter()->insertingText("\n\t\t\$this->call([\\$name_space::class]);")
            ->save();

        echo "$name_space is added to database seeder";
    }
}
