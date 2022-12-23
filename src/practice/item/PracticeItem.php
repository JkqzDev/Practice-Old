<?php

declare(strict_types=1);

namespace practice\item;

use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class PracticeItem extends Item {

    public function __construct(
        string $name,
        int    $id,
        int    $meta = 0
    ) {
        parent::__construct(new ItemIdentifier($id, $meta), TextFormat::clean($name));
        $this->setCustomName(TextFormat::colorize('&r' . $name));

        $namedtag = $this->getNamedTag();
        $namedtag->setString('practice_item', $name);
        $this->setNamedTag($namedtag);
    }
}