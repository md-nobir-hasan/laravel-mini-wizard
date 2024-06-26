<?php

namespace Nobir\MiniWizard\Services;

use Nobir\MiniWizard\Traits\ModuleKeys;
use Nobir\MiniWizard\Traits\PathManager;

abstract class BaseCreation
{
    use ModuleKeys, PathManager;

    protected $fields;
    protected $model_name;

    public function __construct(array $fields, string $model_name)
    {
        $this->fields = $fields;
        $this->model_name = $model_name;
    }
    protected function getStub($module)
    {
        switch($module){
            case self::MIGRATION;
                return file_get_contents(self::migration_stub_path);
        }
    }
    abstract public function generate();
}

