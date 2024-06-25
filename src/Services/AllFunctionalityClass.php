<?php

namespace Nobir\MiniWizard\Services;

use Nobir\MiniWizard\Services\MigrationCreation as ServicesMigrationCreation;
use Nobir\TheBackendWizard\Services\MigrationCreation;

class AllFunctionalityClass extends BaseCreation
{
    protected $fields;
    protected $model_name;

    public function __construct(array $fields, string $model_name)
    {
        $this->fields = $fields;
        $this->model_name = $model_name;
    }
    public function generate(){

    }

    public function createMigration()
    {
        $migrationCreator = new ServicesMigrationCreation($this->fields, $this->model_name);
        $migrationCreator->generate();
    }

    // Add other methods for creating controllers, models, etc.
}
