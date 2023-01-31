<?php

namespace Wemx\Installer\Commands;

use Illuminate\Console\Command;

class BackupCommand extends Command
{

    protected $signature = 'backup {--action= : create/list/restore} {--type= : panel/db/all}';
    protected $description = 'Backup manager for Pterodactyl';

    public const ACTIONS = [
        'create' => 'Create a new backup',
        'restore' => 'Restore the panel from the backup',
        'list' => 'View the list of backups',
    ];

    public const TYPE = [
        'panel' => 'Only panel files',
        'db' => 'Only the database',
        'all' => 'All panel and database'
    ];

    private string $panel_directory, $backup_directory, $db_directory, $db_user, $db_pass, $db_host, $db_name;

    public $args = [];

    public function handle()
    {
        if (!file_exists(config_path('wemx-backup.php'))) {
            exec('php artisan vendor:publish --provider="Wemx\Installer\CommandsServiceProvider" --force');
        }

        $this->panel_directory = base_path();
        if (!empty(\Config::get('wemx-backup.backup_directory'))) {
            $this->backup_directory = \Config::get('wemx-backup.backup_directory');
        } else {
            $path = explode('/', $this->panel_directory);
            $this->backup_directory = implode('/', array_splice($path, 0, -1)) . '/pterodactyl-backups';
        }

        $this->db_directory = $this->backup_directory . '/db';
        $this->db_user = env('DB_USERNAME', null);
        $this->db_pass = env('DB_PASSWORD', null);
        $this->db_host = env('DB_HOST', null);
        $this->db_name = env('DB_DATABASE', null);

        $this->createDir($this->backup_directory);
        $this->createDir($this->db_directory);

        $this->args['ACTIONS'] = $this->option('action') ?? $this->choice(
            'Choose an action',
            self::ACTIONS
        );

        switch ($this->args['ACTIONS']) {
            case 'create':
                $this->createBackup();
                break;
            case 'restore':
                $this->restoreBackup();
                break;
            case 'list':
                $this->listBackups();
                break;
            default:
                $this->listBackups();
                break;
        }
    }

    public function createBackup()
    {
        $this->args['TYPE'] = $this->option('type') ?? $this->choice(
            'Choose an action',
            self::TYPE
        );
        $name = \Carbon::now()->format('Y-m-d-H-i-s');
        switch ($this->args['TYPE']) {
            case 'panel':
                $this->createPanelBackup($name);
                break;
            case 'db':
                $this->createDbBackup($name);
                break;
            case 'all':
                $this->createPanelBackup($name);
                $this->createDbBackup($name);
                break;
            default:
                exit;
        }
    }

    public function restoreBackup()
    {
        $this->args['TYPE'] = $this->option('type') ?? $this->choice(
            'Choose an action',
            self::TYPE
        );

        $backups = new \DirectoryIterator($this->backup_directory);

        $file = $this->choice(
            'Select backup',
            $this->backupPrepare($backups)
        );

        switch ($this->args['TYPE']) {
            case 'panel':
                $this->restorePanelBackup($file . '.zip');
                break;
            case 'db':
                $this->restoreDbBackup($file . '.sql');
                break;
            case 'all':
                $this->restorePanelBackup($file . '.zip');
                $this->restoreDbBackup($file . '.sql');
                break;
            default:
                exit;
        }
    }

    public function listBackups($type = 'panel')
    {
        $type = $this->choice(
            'Choose an type',
            [
                'panel' => 'Panel backups',
                'db' => 'Database backups',
            ]
        );

        switch ($type) {
            case 'panel':
                $backups = new \DirectoryIterator($this->backup_directory);
                break;
            case 'db':
                $backups = new \DirectoryIterator($this->db_directory);
                break;
            default:
                exit;
        }

        foreach ($this->backupPrepare($backups) as $key => $fileinfo) {
            $key++;
            $this->info("{$key}) {$fileinfo}");
        }
    }

    private function createPanelBackup($name)
    {
        if (!file_exists($this->backup_directory)) {
            mkdir($this->backup_directory, 0777, true);
        }


        $backup_file = $this->backup_directory . '/backup-' . $name . '.zip';
        $panel_directory = $this->panel_directory;

        $zip = new \ZipArchive();
        $zip->open($backup_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($panel_directory),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        $this->info('Creating a panel backup...');
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($panel_directory) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
    }
    private function createDbBackup($name)
    {
        if (!file_exists($this->db_directory)) {
            mkdir($this->db_directory, 0777, true);
        }
        $this->info('Creating a database backup...');
        $command = "mysqldump --user={$this->db_user} --password={$this->db_pass} --host={$this->db_host} {$this->db_name} > {$this->db_directory}/db-{$name}.sql";
        system($command);
    }

    private function restorePanelBackup($file)
    {
        $file = $this->backup_directory . '/' . $file;
        if (file_exists($file)) {
            $this->info('Restore a panel backup...');
            $zip = new \ZipArchive;
            if ($zip->open($file) === TRUE) {
                $zip->extractTo($this->panel_directory);
                $zip->close();
                $this->info('The panel backup has been successfully restored!');
            } else {
                $this->warn('Failed to restore panel backup.');
            }
        } else {
            $this->warn("The backup file does not exist.");
        }
    }

    private function restoreDbBackup($file)
    {
        $file = \Str::replaceFirst('backup', 'db', $file);
        $file = $this->db_directory . '/' . $file;
        if (!file_exists($file)) {
            $this->warn("Database backup not found: {$file}");
            return;
        }
        $this->info('Restore a database backup...');
        $command = "mysql --user={$this->db_user} --password={$this->db_pass} --host={$this->db_host} {$this->db_name} < {$file}";
        system($command);
        $this->info('The database backup has been successfully restored!');
    }

    private function backupPrepare(\DirectoryIterator $backups)
    {
        $data = [];
        foreach ($backups as $fileinfo) {
            if (!$fileinfo->isDot()) {
                if ($fileinfo->isFile()) {
                    $data[] = substr($fileinfo->getFilename(), 0, (strrpos($fileinfo->getFilename(), ".")));
                }
            }
        }
        return $data;
    }

    private function createDir($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }
}
