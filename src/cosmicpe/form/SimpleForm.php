<?php

declare(strict_types=1);

namespace cosmicpe\form;

use Closure;
use cosmicpe\form\entries\simple\Button;
use Exception;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;

abstract class SimpleForm implements Form {

    private string $title;

    private ?string $content;

    /** @var Button[] */
    private array $buttons = [];

    /** @var Closure[] */
    private array $button_listeners = [];

    public function __construct(string $title, ?string $content = null) {
        $this->title = $title;
        $this->content = $content;
    }

    /**
     * @param Button $button
     * @param Closure|null $listener
     *
     * Listener parameters:
     *  * Player $player
     *  * int $data
     */
    final public function addButton(Button $button, ?Closure $listener = null): void {
        $this->buttons[] = $button;
        if ($listener !== null) {
            $this->button_listeners[array_key_last($this->buttons)] = $listener;
        }
    }

    final public function handleResponse(Player $player, $data): void {
        if ($data === null) {
            $this->onClose($player);
        } else {
            try {
                if (is_int($data)) {
                    if (isset($this->button_listeners[$data])) {
                        $this->button_listeners[$data]($player, $data);
                    } else {
                        $this->onClickButton($player, $this->buttons[$data], $data);
                    }
                }
            } catch (Exception $e) {
                throw new FormValidationException($e->getMessage());
            }
        }
    }

    public function onClose(Player $player): void {}

    public function onClickButton(Player $player, Button $button, int $index): void {}

    final public function jsonSerialize(): array {
        return [
            "type" => "form",
            "title" => $this->title,
            "content" => $this->content ?? "",
            "buttons" => $this->buttons
        ];
    }
}