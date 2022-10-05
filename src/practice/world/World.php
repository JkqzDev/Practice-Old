<?php

declare(strict_types=1);

namespace practice\world;

class World {

    public function __construct(
        private $copy
    ) {
        if ($copy) {
            // copy
        }
    }
}