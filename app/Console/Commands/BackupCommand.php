<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use ZipArchive;

/**
 * PRD §32/§41 "Automated database/file backup".
 *
 * One zip per run holding a database dump plus everything uploaded (media
 * library, documents, CVs, form attachments). Archives are kept locally,
 * optionally copied to an off-site disk (MOAUM_BACKUP_DISK, e.g. an S3 bucket
 * configured in config/filesystems.php), and pruned to the newest N.
 */
class BackupCommand extends Command
{
    protected $signature = 'moaum:backup
        {--keep= : Number of local archives to keep (default from config)}
        {--connection= : Database connection to dump (default: the app default)}';

    protected $description = 'Back up the database and uploaded files into a zip archive';

    public function handle(): int
    {
        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);

        $stamp = now()->format('Ymd-His');
        $archivePath = "{$directory}/moaum-{$stamp}.zip";
        $dumpPath = "{$directory}/database-{$stamp}.sql";

        $connection = $this->option('connection') ?: config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        try {
            $this->dumpDatabase($connection, $dumpPath);
            $files = $this->archive($archivePath, $dumpPath, $driver === 'sqlite' ? 'database.sqlite' : 'database.sql');
        } catch (RuntimeException $e) {
            $this->error('Backup failed: '.$e->getMessage());
            File::delete([$dumpPath, $archivePath]);

            return self::FAILURE;
        } finally {
            File::delete($dumpPath);
        }

        $this->info(sprintf('Backup written: %s (%d files, %s)', basename($archivePath), $files, $this->humanSize(filesize($archivePath))));

        if ($disk = config('moaum.backups.disk')) {
            Storage::disk($disk)->putFileAs('moaum-backups', $archivePath, basename($archivePath));
            $this->info("Copied to the \"{$disk}\" disk.");
        }

        $this->prune($directory, (int) ($this->option('keep') ?? config('moaum.backups.keep', 14)));

        return self::SUCCESS;
    }

    private function dumpDatabase(string $connection, string $dumpPath): void
    {
        $config = config("database.connections.{$connection}");

        if (! is_array($config)) {
            throw new RuntimeException("Unknown database connection [{$connection}].");
        }

        match ($config['driver']) {
            'sqlite' => $this->dumpSqlite($config['database'], $dumpPath),
            'mysql', 'mariadb' => $this->dumpMysql($config, $dumpPath),
            default => throw new RuntimeException("The {$config['driver']} driver is not supported by moaum:backup."),
        };
    }

    private function dumpSqlite(string $database, string $dumpPath): void
    {
        if ($database === ':memory:' || ! File::exists($database)) {
            throw new RuntimeException("SQLite database not found at {$database}.");
        }

        // A file copy of SQLite is a consistent snapshot as long as no write
        // is mid-flight; backups run at a quiet hour from the scheduler.
        File::copy($database, $dumpPath);
    }

    /** @param  array<string, mixed>  $config */
    private function dumpMysql(array $config, string $dumpPath): void
    {
        $result = Process::timeout(600)
            // Password via environment, never on the command line (visible in `ps`).
            ->env(['MYSQL_PWD' => (string) ($config['password'] ?? '')])
            ->run([
                env('MYSQLDUMP_PATH', 'mysqldump'),
                '--single-transaction', '--quick', '--routines', '--no-tablespaces',
                '--host='.$config['host'], '--port='.$config['port'],
                '--user='.$config['username'],
                '--result-file='.$dumpPath,
                $config['database'],
            ]);

        if ($result->failed()) {
            throw new RuntimeException('mysqldump failed: '.trim($result->errorOutput()));
        }
    }

    private function archive(string $archivePath, string $dumpPath, string $dumpName): int
    {
        $zip = new ZipArchive;

        if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Cannot create {$archivePath}.");
        }

        $zip->addFile($dumpPath, 'database/'.$dumpName);
        $count = 1;

        // Uploaded content: media library (public) and everything private.
        foreach (['app/public/media' => 'files/media', 'app/private' => 'files/private'] as $source => $target) {
            $path = storage_path($source);
            if (! is_dir($path)) {
                continue;
            }

            foreach (Finder::create()->files()->in($path)->ignoreDotFiles(true) as $file) {
                $zip->addFile($file->getRealPath(), $target.'/'.str_replace('\\', '/', $file->getRelativePathname()));
                $count++;
            }
        }

        $zip->close();

        return $count;
    }

    private function prune(string $directory, int $keep): void
    {
        $archives = collect(File::glob("{$directory}/moaum-*.zip"))->sortDesc()->values();

        $archives->slice(max(1, $keep))->each(function ($old) {
            File::delete($old);
            $this->line('Pruned '.basename($old));
        });
    }

    private function humanSize(int $bytes): string
    {
        return $bytes >= 1048576 ? round($bytes / 1048576, 1).' MB' : max(1, round($bytes / 1024)).' KB';
    }
}
