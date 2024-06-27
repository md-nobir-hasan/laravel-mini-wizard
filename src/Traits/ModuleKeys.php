<?php
namespace Nobir\MiniWizard\Traits;

trait ModuleKeys{
    /**
     * Where these manual key are present.
     * only in config files, please match these modules key value with config files.
     * another these key are match stub files - please make sure stub files name are the same with these values.
     */
    const MIGRATION = 'migration';
    const MODEL = 'model';
    const SEEDER = 'seeder';
    const FACTORY = 'factory';
    const CONTROLLER = 'controller';
    const REQUESTS = 'requests';
    const SERVICE_CLASS = 'service_class';
    const VIEW = 'view';
}
