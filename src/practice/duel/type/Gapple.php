<?php

declare(strict_types=1);

namespace practice\duel\type;

use practice\duel\Duel;

class Gapple extends Duel {

    protected function init(): void {
        $this->canDrop = true;
    }
}