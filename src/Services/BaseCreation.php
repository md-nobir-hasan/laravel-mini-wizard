<?php

namespace Nobir\MiniWizard\Services;

use Nobir\MiniWizard\Traits\ModuleKeys;
use Nobir\MiniWizard\Traits\PathManager;
use Nobir\MiniWizard\Traits\StringManipulation;

abstract class BaseCreation
{
    use ModuleKeys, PathManager,StringManipulation;

    protected $fields;
    protected $model_name;
    protected $models_name;

    public function __construct(array $fields, string $model_name,array $models_name = null)
    {
        $this->fields = $fields;
        $this->model_name = $model_name;
        $this->models_name = $models_name;
    }


    abstract public function generate();
}

