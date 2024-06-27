<?php
namespace Nobir\MiniWizard;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use Nobir\MiniWizard\Commands\WizardCommand;
use Nobir\MiniWizard\Traits\ModuleKeys;
use Nobir\MiniWizard\Traits\PathManager;

Class MiniWizardServiceProvider extends ServiceProvider{
    use PathManager, ModuleKeys;

    public function register(){

    }

    public function boot(): void
    {
        AboutCommand::add('The laravel  MINI WIZARD', fn () => ['Version' => '4.0.0']);
        $this->commands([
            WizardCommand::class,
        ]);

        //publishing configure file
        $this->publishes([
           self::pakage_root_path.'/bootstrap/config.php' => config_path('mini-wizard.php'), //configure files
        ], 'wizard-config');

        //publishing bootstraping files
        $this->publishes([
           self::pakage_root_path.'/template/sidebar/NSidebarModel.php' => $this->getModulePath(self::MODEL),
           self::pakage_root_path.'/template/sidebar/2024_05_31_085644_create_n_sidebars_table.php' => $this->getModulePath(self::MIGRATION),
           self::pakage_root_path.'/template/sidebar/nSidebarSeeder.php' => $this->getModulePath(self::SEEDER),
           self::pakage_root_path.'/template/sidebar/2024_05_31_085644_create_n_sidebars_table.php' => $this->getModulePath(self::MIGRATION),
        ], 'wizard-sidebar');

        //publishing stub files
        $this->publishes([
            self::pakage_root_path. '/template/stubs' => self::stubDirPath(),
            ], 'wizard-stubs');
    }
}

