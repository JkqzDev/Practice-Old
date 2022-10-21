<?php

declare(strict_types=1);

namespace practice\form\duel;

use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\duel\Duel;
use practice\duel\queue\QueueFactory;

final class DuelQueueForm extends SimpleForm {

    private array $types = [
        'No Debuff' => Duel::TYPE_NODEBUFF,
        'Battle Rush' => Duel::TYPE_BATTLERUSH,
        'Bridge' => Duel::TYPE_BRIDGE,
        'Combo' => Duel::TYPE_COMBO,
        'Boxing' => Duel::TYPE_BOXING,
        'Sumo' => Duel::TYPE_SUMO,
        'Fist' => Duel::TYPE_FIST,
        'Gapple' => Duel::TYPE_GAPPLE,
        'Build UHC' => Duel::TYPE_BUILDUHC,
        'Final UHC' => Duel::TYPE_FINALUHC,
        'Cave UHC' => Duel::TYPE_CAVEUHC
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