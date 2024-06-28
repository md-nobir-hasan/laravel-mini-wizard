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
    const SEEDER_FACTORY = 'seeder_factory';
    const CONTROLLER = 'controller';

    //reqest paths
    const REQUESTS = 'requests';
    const STORE_REQUEST = 'store_request';
    const UPDATE_REQUEST = 'update_request';


    const SERVICE_CLASS = 'service_class';
    const VIEW = 'view';
}
