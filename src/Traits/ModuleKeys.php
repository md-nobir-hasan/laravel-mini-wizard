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
    const REQUESTS = 'requests';
    const SERVICE_CLASS = 'service_class';
    const PARENT_SERVICE_CLASS = 'parent_service_class';
    const VIEW = 'view';
    const THEME = 'theme';

    //keys in route module
    const ROUTE = 'route';

    //keys in controller
    const CONTROLLER = 'controller';
    const RESOURCE_CONTROLLER = 'resource_controller';


    /**
     * stub paths
     */
    //for controller
    const CODE_FOR_GET_METHOD= 'code_for_get_method';
    const CODE_FOR_POST_METHOD= 'code_for_post_method';
    const CODE_FOR_PUT_METHOD= 'code_for_put_method';
    const CODE_FOR_DELETE_METHOD= 'code_for_delete_method';

    //for view (blade file)
}
