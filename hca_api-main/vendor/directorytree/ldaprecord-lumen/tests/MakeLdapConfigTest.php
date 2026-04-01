<?php

namespace LdapRecord\Lumen\Tests;

use Illuminate\Filesystem\Filesystem;
use LdapRecord\Lumen\LdapServiceProvider;

class MakeLdapConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app()->register(LdapServiceProvider::class);
    }

    protected function tearDown(): void
    {
        $this->deleteConfigStub();

        parent::tearDown();
    }

    protected function deleteConfigStub()
    {
        $files = app(Filesystem::class);

        $files->delete($this->configFilePath());
        $files->deleteDirectory($this->configFileFolder());
    }

    protected function configFilePath()
    {
        return $this->configFileFolder().'/ldap.php';
    }

    protected function configFileFolder()
    {
        return __DIR__.'/../config';
    }

    public function test_configuration_file_can_be_published()
    {
        $this->artisan('ldap:make:config');

        $this->assertTrue(file_exists($this->configFilePath()));
        $this->assertTrue(file_exists($this->configFileFolder()));
    }
}
