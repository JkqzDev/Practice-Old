<?php

declare(strict_types=1);

namespace cosmicpe\form\entries\custom;

use cosmicpe\form\entries\ModifyableEntry;
use InvalidArgumentException;

final class DropdownEntry implements CustomFormEntry, ModifyableEntry{

	/** @var string */
	private $title;

	/** @var array */
	private $options;

	/** @var int */
	private $default = 0;

	public function __construct(string $title, array $options){
		$this->title = $title;
		$this->options = array_values($options);
	}

	public function getValue() : string{
		return $this->options[$this->default];
	}

	public function setValue($value) : void{
		$this->setDefault($value);
	}

	public function validateUserInput($input) : void{
		if(!is_int($input) || !isset($this->options[$input])){
			throw new InvalidArgumentException("Failed to process invalid user input: " . $input);
		}
	}

	public function setDefault(string $default_option) : void{
		foreach($this->options as $index => $option){
			if($option === $default_option){
				$this->default = $index;
				return;
			}
		}

		throw new InvalidArgumentException("Option \"" . $default_option . "\" does not exist!");
	}

	public function jsonSerialize() : array {
		return [
			"type" => "dropdown",
			"text" => $this->title,
			"options" => $this->options,
			"default" => $this->default
		];
	}
}
