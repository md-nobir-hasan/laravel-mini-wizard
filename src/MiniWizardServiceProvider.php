<?php
namespace Nobir\MiniWizard;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use Nobir\MiniWizard\Commands\WizardCommand;

Class MiniWizardServiceProvider extends ServiceProvider{

    public function register(){

    }

    public function boot(): void
    {
        AboutCommand::add('The laravel  MINI WIZARD', fn () => ['Version' => '4.0.0']);
        $this->commands([
            WizardCommand::class,
        ]);
    }
}

