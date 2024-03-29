<?php

declare(strict_types=1);

namespace practice\kit\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\form\kit\KitForm;
use practice\kit\KitFactory;
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
                $inventory = $session->getInventory(strtolower($arena->getKit()));
                $inventory?->giveKit();
            }
            return;
        }
        $subCommand = strtolower($args[0]);

        if ($sender->hasPermission('kit.command')) {
            if ($subCommand === 'edit') {
                if (!isset($args[1])) {
                    return;
                }
                $text = $args;
                unset($text[0]);
                $kitName = implode(' ', $text);
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