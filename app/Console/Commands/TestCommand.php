<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

//use Symfony\Component\Process\Process;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
  //      $process = new Process('cd ~ && ./test');
    //    $process->run();
       //$this->info(exec("cd ~ && ./test"));
        //exec("cd ~ && ./test");
        exec("cd ~ && ./test", $output);
        foreach ($output as $thing) {
          echo $thing;
        }
    }
}
