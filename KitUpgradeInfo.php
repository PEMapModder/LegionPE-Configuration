<?php

namespace legionpe\config;

use legionpe\session\Session;
use legionpe\utils\GrammarUtils;
use pocketmine\item\Item;

class KitUpgradeInfo{
	/** @var \pocketmine\item\Item */
	private $item;
	/** @var int */
	private $price;
	/** @var int */
	private $minRank;
	/** @var string */
	private $minRankName;
	/**
	 * @param Item $item
	 * @param int $price
	 * @param int $minRank
	 */
	public function __construct(Item $item, $price, $minRank = Settings::RANK_IMPORTANCE_DEFAULT){
		$this->item = $item;
		$this->price = $price;
		$this->minRank = $minRank;
		foreach((new \ReflectionClass(Settings::class))->getConstants() as $name => $value){
			if($value === $minRank and substr($name, 0, 16) === "RANK_IMPORTANCE_"){
				$this->minRankName = strtolower(substr($name, 16));
			}
		}
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
	public function canPurchase(Session $session){
		return ($session->getRank() & Settings::RANK_SECTOR_IMPORTANCE) >= $this->minRank or ($session->getRank() & Settings::RANK_SECTOR_PERMISSION) > 0;
	}
	public function sendPurchaseMessage(Session $session){
		if(!$this->canPurchase($session)){
			$session->tell("You need to at least be a $this->minRankName to purchase this upgrade.");
			return true;
		}
		return false;
	}
}
