<?php

namespace legionpe\config;

use legionpe\LegionPE;
use legionpe\session\Session;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Server;

class Settings{
	// ranks of importance (how important the person is, like VERY Important Person) must not exceed 15 according to this, 1 nibble
	const RANK_IMPORTANCE_DEFAULT =         0x0000; // 0  , 0000
	const RANK_IMPORTANCE_TESTER =          0x0001; // 1  , 0001
	const RANK_IMPORTANCE_DONATOR =         0x0004; // 4  , 0100
	const RANK_IMPORTANCE_DONATOR_PLUS =    0x0005; // 5  , 0101
	const RANK_IMPORTANCE_VIP =             0x000C; // 12 , 1100
	const RANK_IMPORTANCE_VIP_PLUS =        0x000D; // 13 , 1101
	const RANK_IMPORTANCE_VIP_STAR =        0x000E; // 14 , 1110
	const RANK_SECTOR_IMPORTANCE =          0x000F; // 15 , 1111

	// permissions the rank has, 2 nibbles
	const RANK_PERM_DEFAULT =               0x0000;
	const RANK_PERM_MOD =                   0x0010;
	const RANK_PERM_ADMIN =                 0x0030;
	const RANK_PERM_OWNER =                 0x0070;
	const RANK_PERM_      =                 0x0200;
	const RANK_PERM_WORLD_EDIT =            0x0400;
	const RANK_PERM_DEV =                   0x0800;
	const RANK_SECTOR_PERMISSION =          0x0FF0;

	// precise (generally won't affect the program) degree of rank, 2 bits
	const RANK_PREC_STD =                   0x0000;
	const RANK_PREC_TRIAL =                 0x1000;
	const RANK_PREC_HEAD =                  0x2000;
	const RANK_SECTOR_PRECISION =           0x3000;

	const KITPVP_KIT_FIGHTER   = 1;
	const KITPVP_KIT_ARCHER    = 2;
	const KITPVP_KIT_JUGGERNAUT = 10;
	// blah; you can add up to 255 kits (255 kits from 1 to 255)
	private static $KITPVP_KIT_NAMES = [
		self::KITPVP_KIT_FIGHTER => "Fighter",
		self::KITPVP_KIT_ARCHER => "Archer",
		self::KITPVP_KIT_JUGGERNAUT => "Juggernaut",
	];

	const PURCHASE_KIT_ARCHER = 0;
	const PURCHASE_KIT_JUGGERNAUT = 1;
	const PURCHASE_CLASS_KIT = 0x00000100;
	const PURCHASE_BITMASK_KIT = self::PURCHASE_CLASS_KIT | self::PURCHASE_BITMASK_ITEM;
	const PURCHASE_BITMASK_CLASSES = 0xFFFFFF00;
	const PURCHASE_BITMASK_ITEM = 0x000000FF;

	public static function init(Server $server){
		foreach(["world", "world_parkour", "world_pvp", "world_spleef"] as $world){
			if($server->isLevelGenerated($world)){
				$server->loadLevel($world);
			}
		}
	}
	public static function loginSpawn(Server $server){
		return $server->getLevelByName("world")->getSpawnLocation();
	}
	public static function defaultTeamLimit(){
		return 3;
	}
	public static function shiftTeamLimit($number){
		$number &= 0x0F;
		return $number << 20;
	}
	public static function parkour_checkpoint(Position $p){
		if($p->getLevel()->getName() !== "world_parkour"){
			return -1;
		}
		$x = $p->getFloorX();
		$y = $p->getFloorY();
		$z = $p->getFloorZ();
		if($y === 8){
			if($x === 1560 and $z === -982){
				return 0;
			}
			if($x === 1600 and $z === -957){
				return 1;
			}
			if($x === 1658 and $z === -997){
				return 2;
			}
			if($x === 1650 and $z === -913){
				return 3;
			}
			if($x === 1712 and $z === -837){
				return 4;
			}
		}
		if($y === 9 and $x === 1740 and $z === -912){
			return 5;
		}
		return -1;
	}
	public static function parkour_spawnpoint(Server $server){
		return new Position(1560, 8, -982, $server->getLevelByName("world_parkour"));
	}
	public static function portal(Position $p, LegionPE $main){
		$x = $p->x;
		$y = $p->y;
		$z = $p->z;
		if(7 <= $y and $y <= 13 and 426 <= $z and $z <= 430){
			if($x === -52){
				return $main->getGame(Session::SESSION_GAME_KITPVP);
			}
			if($x === -130){
				return $main->getGame(Session::SESSION_GAME_SPLEEF);
			}
			return null;
		}
		if(-93 <= $x and $x <= -89 and 7 <= $y and $y <= 13 and $z === 467){
			return $main->getGame(Session::SESSION_GAME_PARKOUR);
		}
		return null;
	}
	public static function kitpvp_spawn(Server $server){
//		foreach($server->getLevels() as $level){
//			$server->getLogger()->debug("Level " . $level->getName() . " (" . $level->getFolderName() . ")");
//		}
		return $server->getLevelByName("world_pvp")->getSpawnLocation();
	}
	public static function kitpvp_equip(Inventory $inv, $kitId){
		switch($kitId){
			case self::KITPVP_KIT_FIGHTER:
				$inv->addItem(
					// Item::get( Item ID, damage (default 0), count (default 1) )
					Item::get(Item::IRON_SWORD),
					Item::get(Item::MELON_SLICE, 0, 128)
				);
				break;
			case self::KITPVP_KIT_ARCHER:
				$inv->addItem(
					Item::get(Item::BOW),
					Item::get(Item::ARROW, 0, 128),
					Item::get(Item::MELON_SLICE, 0, 128)
				);
				break;
		}
	}
	public static function kitpvp_availableKits(Session $session){
		$purchases = $session->getPurchases();
		$available = [self::KITPVP_KIT_FIGHTER];
		foreach($purchases as $purchase){
			$id = $purchase->getProductId();
			if($kitId = $id & self::PURCHASE_BITMASK_KIT){
				if(isset(self::$KITPVP_KIT_NAMES[$kitId])){
					$available[$kitId] = true;
				}
				// else?
			}
		}
		return array_keys($available);
	}
	public static function kitpvp_canAccessKit($kitId, Session $session){
		if($kitId === self::KITPVP_KIT_FIGHTER){
			return true;
		}
		$masked = $kitId | self::PURCHASE_CLASS_KIT;
		foreach($session->getPurchases() as $p){
			if($p->getProductId() === $masked){
				return true;
			}
		}
		return false;
	}
	public static function kitpvp_getKitIdByString($name){
		$id = array_search(strtolower($name), array_change_key_case(self::$KITPVP_KIT_NAMES, CASE_LOWER));
		if(is_int($id)){
			return $id;
		}
		return false;
	}
	public static function kitpvp_getKitStringById($kitId){
		return isset(self::$KITPVP_KIT_NAMES[$kitId]) ? self::$KITPVP_KIT_NAMES[$kitId]:"unknown";
	}
	public static function kitpvp_maxFriends($rank){
		if($rank instanceof Session){
			$rank = $rank->getRank();
		}
		if($rank & self::RANK_PERM_OWNER){
			return PHP_INT_MAX;
		}
		if($rank & self::RANK_IMPORTANCE_VIP_STAR){
			return 25;
		}
		if($rank & self::RANK_IMPORTANCE_VIP_PLUS){
			return 20;
		}
		if($rank & self::RANK_IMPORTANCE_VIP){
			return 15;
		}
		if($rank & self::RANK_PERM_ADMIN){
			return 15;
		}
		if($rank & self::RANK_PERM_MOD){
			return 10;
		}
		if($rank & self::RANK_IMPORTANCE_DONATOR_PLUS){
			return 10;
		}
		if($rank & self::RANK_IMPORTANCE_DONATOR){
			return 7;
		}
		return 5;
	}
	public static function kitpvp_isSafeArea(Vector3 $pos){
		$x = $pos->x;
		$y = $pos->y;
		$z = $pos->z;
		return (-8 <= $x and $x <= 8 and 0 <= $y and $y <= 127 and -8 <= $z and $z <= 8);
	}
	public static function kitpvp_getTag($kills){
		$tag = "";
		if($kills >=25) $tag="Fighter";
		if($kills >=75) $tag="Killer";
		if($kills >=150) $tag="Dangerous";
		if($kills >=250) $tag="Hard";
		if($kills >=375) $tag="Beast";
		if($kills >=525) $tag="Elite";
		if($kills >=675) $tag="Warrior";
		if($kills >=870) $tag="Knight";
		if($kills >=1100) $tag="Addict";
		if($kills >=1350) $tag="Unstoppable";
		if($kills >=1625) $tag="Pro";
		if($kills >=1925) $tag="Hardcore";
		if($kills >=2250) $tag="Master";
		if($kills >=2600) $tag="Legend";
		if($kills >=2975) $tag="God";
		return $tag;
	}
	public static function getGameByLevel(Level $level, LegionPE $main){
		switch($level->getName()){
			case "world_pvp":
				return $main->getGame(Session::SESSION_GAME_KITPVP);
			case "world_parkour":
				return $main->getGame(Session::SESSION_GAME_PARKOUR);
			case "world_spleef":
				return $main->getGame(Session::SESSION_GAME_SPLEEF);
			case "world_infected":
				return $main->getGame(Session::SESSION_GAME_INFECTED);
		}
		return null;
	}
	public static function getPurchaseByCoords(Position $pos){
		// TODO
		if($pos === "dummy result"){
			return [
				"duration" => 86400, // seconds
				"product" => 0x00000000, // product bitmask
				"name" => "dummy product", // product display name
				"price" => 100, // amount of coins to take
			];
		}
		return false;
	}
	public static function equals(Position $init, Position... $poss){
		$x = $init->x;
		$y = $init->y;
		$z = $init->z;
		$lev = $init->getLevel()->getName();
		/** @var Position[] $poss */
		foreach($poss as $pos){
			if($pos->x !== $x or $pos->y !== $y or $pos->z = $z or $pos->getLevel()->getName() !== $lev){
				return false;
			}
		}
		return true;
	}
}
