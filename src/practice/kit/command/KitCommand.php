<?php

declare(strict_types=1);

namespace practice\kit\command;

use practice\kit\KitFactory;
use pocketmine\player\Player;
use practice\form\kit\KitForm;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use practice\session\SessionFactory;

final class KitCommand extends Command {

    public function __construct() {
        parent::__construct('kit', 'Command for kit');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof Player) {
            return;
        }
        $session = SessionFactory::get($sender);

        if ($session === null) {
            return;
        }

        if (!isset($args[0])) {
            if ($session->inArena()) {
                $arena = $session->getArena();

                if ($arena->inCombat($sender)) {
                    $sender->sendMessage(TextFormat::colorize('&cYou have combat tag'));
                    return;
                }
                $kit = KitFactory::get($arena->getKit());

                $kit?->giveTo($sender);
            }
            return;
        }
        $subCommand = strtolower($args[0]);
        
        if ($sender->hasPermission('kit.command')) {
            if ($subCommand === 'edit') {
                if (!isset($args[1])) {
                    return;
                }
                $kitName = $args[1];
                $kit = KitFactory::get($kitName);

                if ($kit === null) {
                    $sender->sendMessage(TextFormat::colorize('&cKit not exists.'));
                    return;
                }
                $form = new KitForm($kit);
                $sender->sendForm($form);
            }
        }
    }
}