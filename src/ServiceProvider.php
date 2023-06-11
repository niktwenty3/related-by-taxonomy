<?php

namespace Niktwenty3\RelatedByTaxonomy;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        \Niktwenty3\RelatedByTaxonomy\Relbytaxonomy::class,
    ];
}
