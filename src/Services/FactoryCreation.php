<?php

namespace Nobir\MiniWizard\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nobir\MiniWizard\Services\BaseCreation;

class FactoryCreation extends BaseCreation
{
    public function generate()
    {
        // Derive file name f
        $FileName = $this->model_name . 'Factory.php';

        //factory path collection
        $file_path = self::getModulePath(self::FACTORY, $FileName);

        //overwrite or skip logic if exist the file
        if (self::fileOverwriteOrNot($file_path)) {

            /**
             * dynamic properties preparation for the factory
             */

            //namespace derived
            $name_space = self::getModuleNamespace(self::FACTORY);


            //slot preparation
            $slot = $this->generateSlot();

            //Finally the file modification if exist or creation if not exist
            FileModifier::getContent(self::getStubFilePath(self::FACTORY))
                ->searchingText('{{name_space}}')->replace()->insertingText($name_space)
                ->searchingText('{{model_name}}')->replace()->insertingText($this->model_name)
                ->searchingText('{{slot}}')->replace()->insertingText($slot)
                ->save($file_path);
            $this->info('Factory created successfully');

            //Specific file namespace creation
            $name_space = $name_space . '\\' . $this->model_name . 'Factory';

            //// SeederFactory file modification so that when you run any command for seeding such as migrate:fresh --seed, the factory work finely
            $this->seederFactoryModification($name_space);


            // include seeder factory to database seeder
            $this->databaseSeederModification($name_space);


            return true;
        }
        $this->info('Skiped factory creation');
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
                $slot .= (in_array($default_value, ['boolean']) ? "$default_value," : "'$default_value',") . " //Default value";
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

    protected function databaseSeederModification()
    {
        $database_path = database_path('seeders/DatabaseSeeder.php');

        if (!(FileModifier::getContent($database_path)->isExist("include('SeederFactory.php');"))) {

            FileModifier::getContent(database_path('seeders/DatabaseSeeder.php'))
            ->searchingText("{", 2)->insertAfter()->insertingText("\n\n\n\t\tinclude('SeederFactory.php');")
            ->save();

            $this->info("Seeder factory  is added to DatabaseSeeder.php file");
            return true;
        }

        $this->info("Seeder factory  is already added to DatabaseSeeder.php file");
    }

    protected function seederFactoryModification($name_space)
    {
        $get_content_path = database_path('seeders/SeederFactory.php');
        $put_content_path = $get_content_path;
        if(!File::exists($put_content_path)){
            $get_content_path = self::getStubFilePath(self::SEEDER_FACTORY);
        }
        FileModifier::getContent($get_content_path)
        ->searchingText('///', 2)->insertBefore()->insertingText("\n\t\t\$this->call([\\$name_space::class]);")
            ->save($put_content_path);

        $this->info("$name_space is added to SeederFactory");
    }
}
