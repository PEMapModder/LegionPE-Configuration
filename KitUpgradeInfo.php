<?php

namespace legionpe\config;

use legionpe\session\Session;
use legionpe\utils\GrammarUtils;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

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
		if($this->item->getId() === Item::AIR){
			return false;
		}
		$name = $this->item->getName();
		if($name === "Unknown"){
			$name = $this->searchItemNameFromConsts($this->item->getId());
		}
		GrammarUtils::word_quantitize($name, $this->item->getCount());
		return strtolower($name) . ($this->item->getDamage() === 0 ? "":" with damage value " . $this->item->getDamage());
	}
	public function canPurchase(Session $session){
		return ($session->getRank() & Settings::RANK_SECTOR_IMPORTANCE) >= $this->minRank or ($session->getRank() & Settings::RANK_SECTOR_PERMISSION) > 0;
	}
	public function sendCantPurchaseMessage(Session $session){
		if(!$this->canPurchase($session)){
			$session->tell("You need to at least be a $this->minRankName to purchase this upgrade.");
			return true;
		}
		return false;
	}
	private function searchItemNameFromConsts($id){
		foreach((new \ReflectionClass(Item::class))->getConstants() as $name => $val){
			if($id === $val){
				$ret = strtolower(str_replace("_", " ", $name));
				return $ret;
			}
		}
		return TextFormat::RED . "Unknown" . TextFormat::RESET;
	}
}
