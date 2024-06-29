<?php

namespace Nobir\MiniWizard\Services;

use Illuminate\Support\Facades\File;

class RouteCreation extends BaseCreation
{

    protected $route_info;
    /**
     * Array will be
     *
     * $routes_info = [
     *    group_name => '',
     *     group_middleware => '', //(this middleware for the main route)
     *     middleware => '', //(this middleware for the main route)
     *     is_resource => '',
     *     general_routes => [
     *        [url=>'',name=>'',route_method=>'',controller_method=>'','middleware'=>'']
     *        ..........................
     *        ..........................
     *    ]
     * ]
     */


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

            $resource_route = "\nRoute::resource('/$route_name','$controller_namespace')";

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
         * code writing for the route if group exist
         */
        $group_route_start = '';
        $group_route_end = "";
        if ($group = $route_info['group_name']) {
            $group_route_start = "Route::";
            $group_route_end = "\n});";

            //group middleware added to route
            $group_middleware = $route_info['group_middleware'];
            $group_middleware ? $group_route_start .= "middleware('$group_middleware')->" : '';

            //group prefix and name added to route
            $group_route_start .= "prefix('/$group')->";
            $group_route_start .= "name('$group.')->";
            $group_route_start .= "group(function () {";

            //Final route preparation
            // $final_route = $group_route_start . $general_routes_make . $group_route_end;

            //inserting the route
            // FileModifier::getContent($get_content_path)
            //     ->searchingText("$group_route_start")
            //     ->ifExist()->insertAfter()->insertingText($target_route)
            //     ->ifNotExist()
            //     ->searchingText('///')->insertBefore()->insertingText($final_route)
            //     ->save($put_content_path);
            // $this->info('route added to mini-wizard.php with route group');
        } else {
            // //inserting the route
            // FileModifier::getContent($get_content_path)
            //     ->searchingText('///')->insertBefore()->insertingText($target_route)
            //     ->save($put_content_path);

            // $this->info('route added to mini-wizard.php without route group');
        }



        /**
         * code writing for the route if global route is exist
         */
        $route_suffix = self::getModuleSuffix(self::ROUTE);
        $parent_route_start = '';
        $parent_route_end = '';
        if ($route_suffix) {
            $parent_route_start = "\nRoute::prefix('/$route_suffix')->name('$route_suffix.')->group(function () {";
            $parent_route_end = "\n});";
        }


        $full_route = $parent_route_start . $group_route_start . $target_route . $group_route_end . $parent_route_end;
        $route_from_group = $group_route_start . $target_route . $group_route_end;

        // create file instance
        $file_modifier = FileModifier::getContent($get_content_path);

        //set is_Route_ceated value 1 to understand thant is_Route_created variable's value exist or not
        $is_route_created = 1;
        if ($parent_route_start) {

            //is save it return true otherwise false return if return false that's mean parent route exist in the content
            $is_route_created = $file_modifier->searchingText($parent_route_start)
                ->ifNotExist()->searchingText('///')->insertBefore()->insertingText($full_route)->isSave();

        }

        if ($is_route_created !== true) {
            //1 is set to understant the variable contain value or not
            $is_route_created2 = 1;
            if ($group_route_start) {
                //the instance return false or true false means route value exist but not in content
                $is_route_created2 = $file_modifier->searchingText($group_route_start)
                    // ->ifNotExist()->searchingText('///')->insertBefore()->insertingText($group_route_start)
                    ->ifExist()->insertAfter()->insertingText($target_route)
                    ->isSave();
            }

            if ($is_route_created2 !== true) {
                dd($is_route_created2,  $file_modifier);
                if ($is_route_created2 === false) {
                    if ($is_route_created === false) {
                        $final_save = $file_modifier->searchingText($parent_route_start)->insertAfter()->insertingText($route_from_group)->save();

                    } else {
                        $final_save = $file_modifier->searchingText('///')->insertBefore()->insertingText($route_from_group)->save();
                    }
                } else {
                    if ($is_route_created === false) {
                        $final_save = $file_modifier->searchingText($parent_route_start)->insertAfter()->insertingText($target_route)->save();
                    } else {
                        $final_save = $file_modifier->searchingText('///')->insertBefore()->insertingText($target_route)->save();
                    }
                }
            }
        }

        // FileModifier::getContent($get_content_path)->searchingText("$group_route_start")
        //     ->ifExist()->insertAfter()->insertingText($target_route)
        //     ->ifNotExist()->searchingText("$parent_route_start")
        //     ->ifExist()->insertingText("$full_route")
        //     ->save();
    }

    public function parameterPass($route_info)
    {
        $this->route_info = $route_info;
        $this->generate();
    }
}
