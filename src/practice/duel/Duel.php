<?php

declare(strict_types=1);

namespace practice\duel;

use pocketmine\world\World;
use practice\session\Session;

abstract class Duel {

    public const TYPE_NODEBUFF = 0;

    public function __construct(
        private int $id,
        private int $typeId,
        private Session $firstPlayer,
        private Session $secondPlayer,
        private World $world
    ) {

    }

    protected function init(): void {}

    public function getId(): int {
        return $this->id;
    }

    public function getTypeId(): int {
        return $this->typeId;
    }
}