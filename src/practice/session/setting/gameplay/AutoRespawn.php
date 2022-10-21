<?php

declare(strict_types=1);

namespace practice\session\setting\gameplay;

class AutoRespawn extends GameplaySetting {
    
    public function __construct() {
        parent::__construct('Auto Respawn');
    }
}