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

    public function createSeeder()
    {
        (new SeederCreation($this->fields, $this->model_name))->generate();
    }

    public function createFactory()
    {
        (new FactoryCreation($this->fields, $this->model_name))->generate();
    }

    public function createRequests()
    {
        (new RequestsCreation($this->fields, $this->model_name))->generate();
    }
    public function createServiceClass()
    {
        (new ServiceClassCreation($this->fields, $this->model_name))->generate();
    }
    public function createRoute($route_info)
    {
        (new RouteCreation($this->fields, $this->model_name))->parameterPass($route_info);
    }

}
