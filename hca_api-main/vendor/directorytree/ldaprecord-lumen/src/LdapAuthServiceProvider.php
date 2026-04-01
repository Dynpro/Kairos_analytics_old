<?php

namespace LdapRecord\Lumen;

use Illuminate\Support\Facades\Auth;
use LdapRecord\Laravel\LdapAuthServiceProvider as BaseServiceProvider;

class LdapAuthServiceProvider extends BaseServiceProvider
{
    /**
     * {@inheritDoc}
     */
    protected function registerAuthProvider(): void
    {
        if (! is_null(Auth::getFacadeRoot())) {
            parent::registerAuthProvider();
        }
    }
}
