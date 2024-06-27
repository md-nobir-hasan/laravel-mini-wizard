<?php

namespace Nobir\MiniWizard\Services;

use Nobir\MiniWizard\Services\MigrationCreation;

class AllFunctionalityClass extends BaseCreation
{
    public function generate(){

    }

    public function createModel()
    {
        (new ModelCreation($this->fields, $this->model_name, $this->models_name))->generate();
    }
    public function createMigration()
    {
        (new MigrationCreation($this->fields, $this->model_name))->generate();
    }

}
