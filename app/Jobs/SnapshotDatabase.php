<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\DbSnapshots\Snapshot;
use Spatie\DbSnapshots\SnapshotRepository;
use Artisan;

class SnapshotDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const LIMIT_SNAPSHOT = 48;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // try {
        //     $output = null;
        //     logger()->info('=====Begin snapshot');
        
        //     $backup_directory = storage_path('snapshots') . '/inventory_drk_backend_bk.sql.zip';
        //     $dbname = env('DB_DATABASE', 'forge');
        //     $db_username = env('DB_USERNAME', 'forge');
        //     $db_pwd = env('DB_PASSWORD', '');
        //     if(!empty($db_pwd)) {
        //         $db_pwd = '-p' . $db_pwd;
        //     }
            
        //     $cmd = 'mysqldump -u ' . $db_username . ' ' . $db_pwd . ' --databases ' . $dbname . ' | gzip > ' . $backup_directory;
            
        //     logger()->info('=====CMD:' . $cmd);
        //     exec($cmd, $output);
            
        //     logger()->info('=====Mysqldump:' . json_encode($output));
            
        //     logger()->info('=====End snapshot');
            
        // } catch (\Exception $ex) {
        //     logger()->error($ex);
        // }

       logger()->info('=====Begin snapshot');

       Artisan::call('snapshot:create --compress');

       $snapshots = app(SnapshotRepository::class)->getAll()
           ->map(function (Snapshot $snapshot) {
               return [
                   'name' => $snapshot->name,
                   'created_at' => $snapshot->createdAt()->format('Y-m-d H:i:s')
               ];
           })
           ->sortByDesc('created_at')
           ->slice(self::LIMIT_SNAPSHOT)
           ->each(function ($item) {
               Artisan::call("snapshot:delete {$item['name']}");
           });

       logger()->info('=====End snapshot');
    }
}

