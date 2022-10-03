<?php

declare(strict_types=1);

namespace practice\provider;

use practice\Practice;

class ProviderManager {

    public function __construct() {
        $plugin = Practice::getInstance();
        $plugin->saveDefaultConfig();

        
    }
}