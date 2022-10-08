<?php

declare(strict_types=1);

namespace practice\provider;

use practice\Practice;

class ProviderManager {
    
    static public function loadAll(): void {
        $plugin = Practice::getInstance();
    }
}