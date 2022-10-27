<?php

declare(strict_types=1);

namespace cosmicpe\form;

use pocketmine\player\Player;

abstract class ModalForm implements Form {

    private string $title;

    private ?string $content;

    private string $first_button = "";

    private ?string $second_button;

    public function __construct(string $title, ?string $content = null) {
        $this->title = $title;
        $this->content = $content;
    }

    final public function setFirstButton(string $button): void {
        $this->first_button = $button;
    }

    final public function setSecondButton(string $button): void {
        $this->second_button = $button;
    }

    public function handleResponse(Player $player, $data): void {
        if (!$data) {
            $this->onClose($player);
            return;
        }

        $this->onAccept($player);
    }

    protected function onClose(Player $player): void {}

    protected function onAccept(Player $player): void {}

    final public function jsonSerialize(): array {
        return [
            "type" => "modal",
            "title" => $this->title,
            "content" => $this->content ?? "",
            "button1" => $this->first_button,
            "button2" => $this->second_button ?? ""
        ];
    }
}