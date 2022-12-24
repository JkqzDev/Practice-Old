<?php

declare(strict_types=1);

namespace practice\duel\type;

use practice\duel\Duel;

class BuildUHC extends Duel {

    protected function init(): void {
        $this->canDrop = true;
    }
}