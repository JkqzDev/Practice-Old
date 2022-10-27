<?php

declare(strict_types=1);

namespace practice\session\setting\display;

use practice\session\Session;

class Scoreboard extends DisplaySetting {

    public function __construct() {
        parent::__construct('Scoreboard');
    }

    public function execute(Session $session): void {
        if (!$this->isEnabled()) {
            $session->getScoreboard()->despawn();
        } else {
            $session->getScoreboard()->spawn();
        }
    }
}