<?php

namespace Nobir\MiniWizard\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Nobir\MiniWizard\Services\BaseCreation;

class AdminMenuCreation extends BaseCreation
{

    protected $route_info;
    protected $theme_path;
    protected $table_name = 'admin_menus';
    protected $menu_model_name = 'AdminMenu';

    //admin menue seeder array demo, title must be unique of the tables
    /**
     *
     *  $admin_mene = [
     *                 title' => 'District',
     *                 'access' => 'District',
     *                 'route' => 'setup.district.',
     *                 'n_sidebar_id' => 1,
     *                 'is_parent' => false,
     *                 'serial' => 2,
     *                 'status' => '1'
     *           ];

     */

    /**
     * Route in fo array will be
     */
    // protected $routes_info = [
    //     'group_name' => 'setup',
    //     'group_middleware' => 'admin', //(this middleware for the main route)
    //     'middleware' => '', //(this middleware for the main route)
    //     'is_resource' => false,
    //     'general_routes' => [
    //         ['url' => 'index', 'name' => 'index', 'route_method' => 'get', 'controller_method' => 'index', 'middleware' => ''],
    //         ['url' => 'create', 'name' => 'create', 'route_method' => 'get', 'controller_method' => 'create', 'middleware' => ''],
    //         ['url' => 'store', 'name' => 'store', 'route_method' => 'post', 'controller_method' => 'store', 'middleware' => 'auth'],
    //     ]
    // ];

    public function generate()
    {
        //  file name from seeder name
        $FileName = 'AdminMenuSeeder.php';

        //seeder path collection
        $put_content_path = self::getModulePath(self::SEEDER, $FileName);

        //get content path is put content path cause data have to save
        if (file_exists($put_content_path)) {
            $get_content_path = $put_content_path;
        } else {
            /**
             * get content path search in  theme
             */
            $theme_name = self::nameInConfig(self::THEME) ?? 'nobir';
            $theme_path = self::stub_path("view/$theme_name");
            if ($theme_path) {
                $this->theme_path = $theme_path;
            } else {
                $this->info('Your providing theme is not found');
                return false;
            }

            $get_content_path =  $theme_path . "/admin_menu_seeder.stub";
        }


        /**
         * put content path preparation
         */


        //overwrite or skip logic if exist the file
        // if (self::fileOverwriteOrNot($put_content_path)) {

        /**
         * dynamic properties preparation for the seeder
         */

        //namespace derived
        $name_space = self::getModuleNamespace(self::SEEDER);

        // //table name derived
        // $table_name = self::modelToTableName($this->model_name);

        //slot preparation
        $slot = $this->generateSlot();

        //Finally the file modification if exist or creation if not exist
        FileModifier::getContent($get_content_path)
            ->searchingText('{{name_space}}')->replace()->insertingText($name_space)
            ->searchingText(']);')->insertBefore()->insertingText($slot)
            ->save($put_content_path);

        $this->info("Admin menu created successfully");

        //Specific file namespace creation
        $name_space = $name_space . '\\' . $this->menu_model_name . 'Seeder';

        //the created file seeding
        $this->seeding($name_space);




        //// SeederFactory file modification so that when you run any command for seeding such as migrate:fresh --seed, the factory work finely
        $this->seederFactoryModification($name_space);


        return true;
        // }
        $this->info("Skiped seeder creation");
        return true;
    }

    /**
     *  public function country(){
     *   return $this->belongsTo(country::class);
     *}
     */
    protected function generateSlot()
    {
        $slot = "\n\t\t\t[";
        //admin menue seeder array demo
        /**
         *
         *  $admin_mene = [
         *                 title' => 'District',
         *                 'access' => 'District',
         *                 'route' => 'setup.district.',
         *                 'parent_id' => 1,
         *                 'is_parent' => false,
         *                 'serial' => 2,
         *                 'status' => '1'
         *           ];

         */


        $model_name = $this->model_name;
        $title = str()->headline($model_name);
        $route = $this->BaseRouteNamePrepare();
        $is_parent = $this->isParent();
        $parent_id = null;
        if ($is_parent !== true) {
            $parent_id = $is_parent;
            $is_parent = false;
        }


        $slot .= "\n\t\t\t\t'title' => '$title',";
        $slot .= "\n\t\t\t\t'access' =>'$model_name',";
        $slot .= "\n\t\t\t\t'route' =>'$route',";
        $slot .= "\n\t\t\t\t'route' =>'$route',";
        $slot .= "\n\t\t\t\t'parent_id' =>'$parent_id',";
        $slot .= "\n\t\t\t\t'is_parent' =>'$is_parent',";
        $slot .= "\n\t\t\t\t'status' =>'1',";

        $slot .= "\n\t\t\t],";
        return $slot;
    }

    protected function isParent()
    {
        $group_name = $this->route_info['group_name'];
        if ($group_name) {
            $table_name = $this->table_name;
            $group_title = str()->title($group_name);
            if (Schema::hasTable($table_name)) {
                $paren_menue = DB::table($table_name)->where('title', $group_title)->first();
                $parent_menu_id = $paren_menue ? $paren_menue->id : ($this->parentMenuCreate($group_name));
                return $parent_menu_id;
            }
            $this->info("There is no table found named {$this->table_name}. parent_id and is_parent's value may mistake in AdminMenuSeeder.php, Please Check");
            return 1;
        }
        return true;
    }
    protected function parentMenuCreate($group_name)
    {
        $group_title = str()->headline($group_name);

        $model_with_namespace = self::getModuleNamespace(self::SEEDER) . "\\{$this->model_name}";
        $route_name = str()->snake($group_name) . '.';
        // try {
        $latest_entry = DB::table($this->table_name)->latest()->first();
        $serial = $latest_entry ?  ($latest_entry->serial + 1) : 1;
        $insert = $model_with_namespace::create([
            'title' => $group_title,
            'access' => $group_name,
            'route' => $route_name,
            'parent_id' => null,
            'is_parent' => true,
            'serial' => $serial,
            'status' => 'Active'
        ]);
        return ($insert->serial + 1);
        // } catch (\Exception $e) {
        //     $this->info("Parent menu don't create. please see parentMenuCreate() ");
        //     return 1;
        // }
    }

    public function parameterPass($route_info)
    {
        $this->route_info = $route_info;
        $this->generate();
    }

    protected function seeding($name_space)
    {
        if (Schema::hasTable($this->table_name)) {
            DB::table($this->table_name)->delete();
            try {
                Artisan::call('db:seed', [
                    '--class' => $name_space
                ]);
                echo Artisan::output();
            } catch (\Exception $e) {
                $this->info("Admin menue don't seed, Please check");
            }
        } else {
            $this->info("admin_menus table is not found");
        }
    }


    protected function seederFactoryModification($name_space)
    {
        $get_content_path = database_path('seeders/SeederFactory.php');
        $put_content_path = $get_content_path;
        if (!File::exists($put_content_path)) {
            $get_content_path = self::getStubFilePath(self::SEEDER_FACTORY);
        }
        if (!(FileModifier::getContent($get_content_path)->isExist("$name_space"))) {

            FileModifier::getContent($get_content_path)
                ->searchingText("///", 1)->insertBefore()->insertingText("\n\t\t\t\$this->call([\\$name_space::class]);")
                ->save();

            $this->info("AdminMenuSeeder  is added to SeederFactory.php file");
            return true;
        }
        $this->info("$name_space is already added to SeederFactory.php file");
    }


    protected function BaseRouteNamePrepare()
    {
        $base_route_name = '';
        if ($suffix = self::getModuleSuffix(self::ROUTE)) {
            $base_route_name .= $suffix . '.';
        }
        if ($group_name = $this->route_info['group_name']) {
            $base_route_name .= $group_name . '.';
        }

        $model_name_as_dir = self::PascalToSnacke($this->model_name);
        $base_route_name .= $model_name_as_dir . '.';

        return $base_route_name;
    }
}
