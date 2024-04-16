<?php
namespace nobir\CurdByCommand;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use nobir\Console\Commands\MakeCurd\MakeCurd;

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

