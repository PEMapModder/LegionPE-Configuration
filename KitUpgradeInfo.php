<?php

namespace legionpe\config;

use legionpe\utils\GrammarUtils;
use pocketmine\item\Item;

class KitUpgradeInfo{
	/** @var \pocketmine\item\Item */
	private $item;
	/** @var int */
	private $price;
	/**
	 * @param Item $item
	 * @param int $price
	 */
	public function __construct(Item $item, $price){
		$this->item = $item;
		$this->price = $price;
	}
	/**
	 * @return Item
	 */
	public function getItem(){
		return $this->item;
	}
	/**
	 * @return int
	 */
	public function getPrice(){
		return $this->price;
	}
	public function itemsToString(){
		$name = $this->item->getName();
		GrammarUtils::word_quantitize($name, $this->item->getCount());
		return $name . ($this->item->getDamage() === 0 ? "":" with damage value " . $this->item->getDamage());
	}
}
