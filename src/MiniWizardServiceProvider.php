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

        //publishing form for blade view
        $theme_name = self::nameInConfig(self::THEME) ?? 'nobir';
        // dd(self::pakage_root_path );
        $this->publishes([
           self::pakage_root_path. "/bootstrap/theme/$theme_name/form" => resource_path('/views/components/form'),
        ], 'wizard-compnents');

        //publishing stub files
        $this->publishes([
            self::pakage_root_path. '/template/stubs' => self::stubDirPath(),
            ], 'wizard-stubs');
    }
}

