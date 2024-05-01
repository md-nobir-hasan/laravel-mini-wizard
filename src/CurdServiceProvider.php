<?php
namespace Nobir\CurdByCommand;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use Nobir\CurdByCommand\Console\Commands\MakeCurd;

Class CurdServiceProvider extends ServiceProvider{

    public function register(){

    }

    public function boot(): void
    {
        AboutCommand::add('Laravel CURD by Command', fn () => ['Version' => '1.0.0']);
        $this->commands([
            MakeCurd::class,
        ]); 
    }
}

