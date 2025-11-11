<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;


class SyncDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:syncdb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy DB from .com to .tk';

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
        $db_from = 'cvm';
        $db_to = 'cvmtk';
        if (!$this->confirm("CONFIRM DROP $db_to? [y|N]")) {
            exit("Drop Table ".$db_to."command aborted");
        }
        $drop_db = "mysql -u root -p".env('DB_PASSWORD')." -e 'drop database if exists $db_to;'";
        $create_db = "mysql -u root -p".env('DB_PASSWORD')." -e 'create database $db_to;'";
        $dump = 'mysqldump -u root -p'.env('DB_PASSWORD').' '.$db_from.' > '.storage_path('dbdump/').'dbdump.sql';
        $restore_dump = 'mysql -u root -p'.env('DB_PASSWORD').' '.$db_to.' < '.storage_path('dbdump/').'dbdump.sql';
        exec($drop_db);
        $this->comment(PHP_EOL."$db_to dropped");
        exec($create_db);
        $this->comment(PHP_EOL."$db_to created again");
        exec($dump);
        $this->comment(PHP_EOL."Database $db_from dump completed");
        exec($restore_dump);
        $this->comment(PHP_EOL."Database dump restored to $db_to");
        $file = new Filesystem;
        $success = $file->cleanDirectory(storage_path('dbdump/'), true);
        $this->comment(PHP_EOL."Command Completed");
    }
}
