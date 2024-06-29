<?php

namespace Nobir\MiniWizard\Services;

use Illuminate\Support\Facades\File;

class RouteCreation extends BaseCreation
{

    protected $route_info;

    public function generate()
    {
        /**
         * logic based on mini-wizard published or not yet
         */

        $this->modifyMiniWizardRouteFile();

        /**
         * Include the mini-wizard.php in web.php in not
         */
         $web_path = base_path('routes/web.php');

         FileModifier::getContent($web_path)->searchingText("mini-wizard.php")
                ->ifNotExist()->searchingText('<?php')->insertAfter()->insertingText("\nrequire __DIR__ . '/mini-wizard.php';")
                ->save();

        return true;
    }

    protected function modifyMiniWizardRouteFile()
    {

        $put_content_path = base_path('routes/mini-wizard.php');
        $get_content_path = $put_content_path;

        if (!File::exists($get_content_path)) {
            $get_content_path = self::getStubFilePath(self::ROUTE);
        }
        $route_info = $this->route_info;

        /**
         * code writing for the target route
         */

        $target_route = '';

        //Controller namspace get
        $controller_namespace = self::getModuleNamespace(self::CONTROLLER) . "\\{$this->model_name}Controller";

        //Resource route preparation
        if ($route_info['is_resource']) {
            $route_name = self::PascalToCabab($this->model_name);

            $resource_route = "Route::resource('/$route_name','$controller_namespace')";

            if ($middleware = $route_info['middleware']) {
                $resource_route .= "->middleware('$middleware')";
            }

            $resource_route .= ';';

            $target_route = $resource_route;
        } else {
            $general_routes = $route_info['general_routes'];
            $general_routes_make = '';
            foreach ($general_routes as $general_route) {
                //url should be validate later task
                $general_routes_make .= "\nRoute::{$general_route['route_method']}('/{$general_route['url']}',['$controller_namespace','{$general_route['controller_method']}'])->name('{$general_route['name']}')";
                if ($middleware = $general_route['middleware']) {
                    $general_routes_make .= "->middleware('$middleware')";
                }
                $general_routes_make .= ';';
            }
            $target_route = $general_routes_make;
        }

        /**
         * code writing for the group if exist
         */
        $route_group_start = '';
        $route_group_end = "";
        if ($group = $route_info['group_name']) {
            $route_group_start = "\nRoute::";
            $route_group_end = "\n});";

            //group middleware added to route
            $group_middleware = $route_info['group_middleware'];
            $group_middleware ? $route_group_start .= "middleware('$group_middleware')->" : '';

            //group prefix and name added to route
            $route_group_start .= "prefix('/$group')->";
            $route_group_start .= "name('$group.')->";
            $route_group_start .= "group(function () {";

            //Final route preparation
            $final_route = $route_group_start . $general_routes_make . $route_group_end;

            //inserting the route
            FileModifier::getContent($get_content_path)
                ->searchingText("$route_group_start")
                ->ifExist()->insertAfter()->insertingText($target_route)
                ->ifNotExist()
                ->searchingText('///')->insertBefore()->insertingText($final_route)
                ->save($put_content_path);
            $this->info('route added to mini-wizard.php with route group');
        } else {
            //inserting the route
            FileModifier::getContent($get_content_path)
                ->searchingText('///')->insertBefore()->insertingText($target_route)
                ->save($put_content_path);

            $this->info('route added to mini-wizard.php without route group');
        }
    }

    public function parameterPass($route_info)
    {
        $this->route_info = $route_info;
        $this->generate();
    }
}
