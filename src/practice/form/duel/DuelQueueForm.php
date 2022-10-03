<?php

declare(strict_types=1);

namespace practice\form\duel;

use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\duel\Duel;
use practice\Practice;

class DuelQueueForm extends SimpleForm {

    private array $types = [
        'No debuff' => Duel::TYPE_NODEBUFF
    ];

    public function __construct(bool $ranked = false) {
        $plugin = Practice::getInstance();
        $manager = $plugin->getDuelManager();

        parent::__construct(TextFormat::colorize($ranked ? '&bRanked duels' : '&9Unranked duels'));

        foreach ($this->types as $type => $typeId) {
            $this->addButton(new Button(TextFormat::colorize('&7' . $type . PHP_EOL . '&fIn queue: ' . count($manager->getQueuesById($typeId, $ranked)))), function (Player $player, int $button_index) use ($manager, $typeId, $ranked): void {
                $queue = $manager->getQueue($player);

                if ($queue !== null) {
                    return;
                }
                $manager->createQueue($player, $typeId, $ranked);
            });
        }
    }
}