<?php
namespace App\Utils;
use Illuminate\Database\Capsule\Manager as DB;

class MigrationRunner
{
    public static function run(): void
    {
        $files = glob(__DIR__ . '/../Migrations/*.sql');
        sort($files); 
        foreach ($files as $file) {
            echo "Running: " . basename($file) . "\n";
            DB::unprepared(file_get_contents($file));
        }
        echo "All migrations applied.\n";
    }
}