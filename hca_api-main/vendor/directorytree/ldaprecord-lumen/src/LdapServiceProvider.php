<?php

namespace LdapRecord\Lumen;

use Illuminate\Support\Facades\Event;
use LdapRecord\Laravel\LdapServiceProvider as BaseServiceProvider;

class LdapServiceProvider extends BaseServiceProvider
{
    /**
     * {@inheritDoc}
     */
    protected function registerConfiguration(): void
    {
        $this->app->configure('ldap');
    }

    /**
     * {@inheritDoc}
     */
    protected function registerCommands(): void
    {
        parent::registerCommands();

        $this->commands([MakeLdapConfig::class]);
    }

    /**
     * {@inheritDoc}
     */
    protected function registerEventListeners(): void
    {
        if (! is_null(Event::getFacadeRoot())) {
            parent::registerEventListeners();
        }
    }
}
