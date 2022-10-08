<?php

declare(strict_types=1);

namespace practice\form\duel;

use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\duel\Duel;
use practice\queue\QueueFactory;

class DuelQueueForm extends SimpleForm {

    private array $types = [
        'No debuff' => Duel::TYPE_NODEBUFF
    ];

    public function __construct(bool $ranked = false) {
        parent::__construct(TextFormat::colorize($ranked ? 'Ranked duels&m&c' : 'Unranked duels&m&c'));

        foreach ($this->types as $type => $typeId) {
            $this->addButton(new Button(TextFormat::colorize($type . '&m&b')), function (Player $player, int $button_index) use ($typeId, $ranked): void {
                $queue = QueueFactory::get($player);

                if ($queue !== null) {
                    return;
                }
                QueueFactory::create($player, $typeId, $ranked);
            });
        }
    }
}