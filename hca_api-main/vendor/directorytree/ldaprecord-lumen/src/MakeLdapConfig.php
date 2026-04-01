<?php

namespace LdapRecord\Lumen;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeLdapConfig extends Command
{
    /**
     * The filesystem instance.
     */
    protected Filesystem $files;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'ldap:make:config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new LDAP configuration file.';

    /**
     * Create a new controller creator command instance.
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Create the LDAP configuration file.
     */
    public function handle(): int
    {
        if (! $this->files->exists($stubPath = $this->getStubPath())) {
            $this->error("Unable to retrieve LDAP configuration stub file from path [$stubPath]");

            return static::FAILURE;
        }

        $publishPath = $this->getLdapConfigPath(
            $configPath = $this->getConfigPath()
        );

        if ($this->alreadyExists($publishPath)) {
            $this->error("LDAP configuration file already exists at path [$publishPath]");

            return static::INVALID;
        }

        if (! $this->alreadyExists($configPath)) {
            $this->makeDirectory($configPath);
        }

        $this->files->put($publishPath, $this->getContents($stubPath));

        $this->info("Successfully created LDAP configuration file at path [$publishPath]");

        return static::SUCCESS;
    }

    /**
     * Determine if the given file / folder already exists.
     */
    protected function alreadyExists(string $path): bool
    {
        return $this->files->exists($path);
    }

    /**
     * Make the given directory path.
     */
    protected function makeDirectory(string $path): void
    {
        $this->files->makeDirectory($path);
    }

    /**
     * Get the contents of the given file.
     */
    protected function getContents(string $path): string
    {
        return $this->files->get($path);
    }

    /**
     * Get the LDAP configuration file stub.
     */
    protected function getStubPath(): string
    {
        return base_path(implode(DIRECTORY_SEPARATOR, ['vendor', 'directorytree', 'ldaprecord-laravel', 'config', 'ldap.php']));
    }

    /**
     * Get the configuration folder path.
     */
    public function getConfigPath(): string
    {
        return base_path('config');
    }

    /**
     * Get the full LDAP configuration file path.
     */
    protected function getLdapConfigPath(string $configPath): string
    {
        return $configPath.DIRECTORY_SEPARATOR.'ldap.php';
    }
}
