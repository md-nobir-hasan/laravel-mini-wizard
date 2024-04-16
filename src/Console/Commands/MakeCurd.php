<?php
namespace nobir\CurdByCommand\Console\Commands;

use Illuminate\Console\Command;

class MakeCurd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nobir';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command create the all curd facilities';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Yes you do it successfully');
    }
}
?>

