<?php

namespace legionpe\config;

use legionpe\LegionPE;
use legionpe\session\Session;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\item\Apple;
use pocketmine\item\Bow;
use pocketmine\item\Carrot;
use pocketmine\item\ChainBoots;
use pocketmine\item\ChainChestplate;
use pocketmine\item\ChainHelmet;
use pocketmine\item\ChainLeggings;
use pocketmine\item\DiamondBoots;
use pocketmine\item\DiamondChestplate;
use pocketmine\item\DiamondHelmet;
use pocketmine\item\DiamondLeggings;
use pocketmine\item\DiamondSword;
use pocketmine\item\GoldBoots;
use pocketmine\item\GoldChestplate;
use pocketmine\item\GoldHelmet;
use pocketmine\item\GoldLeggings;
use pocketmine\item\IronBoots;
use pocketmine\item\IronChestplate;
use pocketmine\item\IronHelmet;
use pocketmine\item\IronLeggings;
use pocketmine\item\IronSword;
use pocketmine\item\Item;
use pocketmine\item\LeatherBoots;
use pocketmine\item\LeatherCap;
use pocketmine\item\LeatherPants;
use pocketmine\item\LeatherTunic;
use pocketmine\item\StoneSword;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Server;

class Settings{
	// ranks of importance (how important the person is, like VERY Important Person) must not exceed 15 according to this, 1 nibble
	// the first two bits are the two actual permission-affecting nibbles
	const RANK_IMPORTANCE_DEFAULT =         0x0000; // 0  , 0000
	const RANK_IMPORTANCE_TESTER =          0x0001; // 1  , 0001
	const RANK_IMPORTANCE_DONATOR =         0x0004; // 4  , 0100
	const RANK_IMPORTANCE_DONATOR_PLUS =    0x0005; // 5  , 0101
	const RANK_IMPORTANCE_VIP =             0x000C; // 12 , 1100
	const RANK_IMPORTANCE_VIP_PLUS =        0x000D; // 13 , 1101
	const RANK_IMPORTANCE_VIP_STAR =        0x000D; // 14 , 1101
	const RANK_SECTOR_IMPORTANCE =          0x000F;

	// permissions the rank has, 2 nibbles
	const RANK_PERM_DEFAULT =               0x0000;
	const RANK_PERM_MOD =                   0x0010; // 16
	const RANK_PERM_ADMIN =                 0x0030; // 48
	const RANK_PERM_OWNER =                 0x0070; // 112
	const RANK_PERM_MOD =                   0x0010;
	const RANK_PERM_ADMIN =                 0x0030;
	const RANK_PERM_OWNER =                 0x0070;
	const RANK_PERM_STAFF =                 0x00F0;
	/** Permission to be undetected by the auto AFK kicker. */
	const RANK_PERM_AFK =                   0x0100; // 256
	/** Permission to bypass spam (spam detector won't detect at all). SpicyCapacitor ignores this permission and logs anyways. */
	const RANK_PERM_SPAM =                  0x0200; // 512
	/** Permission to edit the world. */
	const RANK_PERM_WORLD_EDIT =            0x0400; // 1024
	/** Permission to execute raw PHP code by `/eval` */
	const RANK_PERM_DEV =                   0x0800; // 2048
	const RANK_SECTOR_PERMISSION =          0x0FF0;

	// precise (generally won't affect the program) degree of rank, 2 bits
	const RANK_PREC_STD =                   0x0000;
	const RANK_PREC_TRIAL =                 0x1000;
	const RANK_PREC_HEAD =                  0x2000;
	const RANK_SECTOR_PRECISION =           0x3000;

	// decorative ranks, which don't actually affect anything, 2 bits
	/** Here you are, the youtuber rank */
	const RANK_DECOR_YOUTUBER =             0x4000;
	const RANK_SECTOR_DECOR =              0xC000;
	const KIT_HELMET = "helmet";
	const KIT_CHESTPLATE = "chestplate";
	const KIT_LEGGINGS = "leggings";
	const KIT_BOOTS = "boots";
	const KIT_WEAPON = "weapon";
	const KIT_FOOD = "food";
	const KIT_BOW = "bow";
	const KIT_ARROWS = "arrows";
	/** @var KitUpgradeInfo[][] */
	public static $KITPVP_KITS = [];

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
	public static function parkour_checkpoint_signs(Position $p, &$complete = false){
		if($p->getLevel()->getName() !== "world_parkour"){
			return -1;
		}
		$x = $p->getFloorX();
		$y = $p->getFloorY();
		$z = $p->getFloorZ();
		if($y === 4){
			if($x === 1567 and $z === -935){
				return 1;
			}
			if($x === 1616 and $z === -1023){
				return 2;
			}
			if($x === 1652 and $z === -916){
				return 3;
			}
			if($x === 1669 and $z === -827){
				$complete = true;
				return 4;
			}
		}
		return 0;
	}
	public static function parkour_checkpoint_startPos($id, Server $server){
		$level = $server->getLevelByName("world_parkour");
		switch($id){
			case 0:
				return new Position(1560, 5, -982, $level);
			case 1:
				return new Position(1600, 5, -956, $level);
			case 2:
				return new Position(1658, 5, -996, $level);
			case 3:
				return new Position(1650, 5, -911, $level);
			case 4:
				return new Position(1713, 5, -839, $level);
			default:
				return null;
		}
	}
	public static function parkour_isFallen(Vector3 $vector3){
		if($vector3 instanceof Position){
			if($vector3->getLevel()->getName() !== "world_parkour"){
				return false;
			}
		}
		return $vector3->y < 1;
	}
	public static function parkour_spawnpoint(Server $server){
		return new Position(1560, 8, -982, $server->getLevelByName("world_parkour"));
	}
	public static function portal(Position $p, LegionPE $main /* , Session $session*/ ){
		$x = $p->x;
		$y = $p->y;
		$z = $p->z;
//		LogCapacitor::log($log = new LogToChat($session), __FILE__ . __LINE__, "Detecting portal for $x, $y, $z");
		if((7 <= $y) and ($y <= 13) and (426 <= $z) and ($z <= 430)){
			if(-53 <= $x and $x <= -52){
//				LogCapacitor::log($log, __FILE__ . __LINE__, "Detected KitPvP");
				return $main->getGame(Session::SESSION_GAME_KITPVP);
			}
			if(-131 <= $x and $x <= -130){
//				LogCapacitor::log($log, __FILE__ . __LINE__, "Detected Spleef");
				return $main->getGame(Session::SESSION_GAME_SPLEEF);
			}
			return null;
		}
		if((-93 <= $x) and ($x <= -89) and (7 <= $y) and ($y <= 13) and (467 <= $x) and ($x <= 468)){
//			LogCapacitor::log($log, __FILE__ . __LINE__, "Detected parkour");
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
	public static function kitpvp_maxFriends($rank){
		if($rank instanceof Session){
			$rank = $rank->getRank();
		}
		if(($rank & self::RANK_SECTOR_PERMISSION) === self::RANK_PERM_OWNER){
			return PHP_INT_MAX;
		}
		if(($rank & self::RANK_SECTOR_IMPORTANCE) === self::RANK_IMPORTANCE_VIP_STAR){
			return 25;
		}
		if(($rank & self::RANK_SECTOR_IMPORTANCE) === self::RANK_IMPORTANCE_VIP_PLUS){
			return 20;
		}
		if(($rank & self::RANK_SECTOR_IMPORTANCE) === self::RANK_IMPORTANCE_VIP){
			return 15;
		}
		if(($rank & self::RANK_SECTOR_PERMISSION) === self::RANK_PERM_ADMIN){
			return 15;
		}
		if(($rank & self::RANK_SECTOR_PERMISSION) === self::RANK_PERM_MOD){
			return 10;
		}
		if(($rank & self::RANK_SECTOR_IMPORTANCE) === self::RANK_IMPORTANCE_DONATOR_PLUS){
			return 10;
		}
		if(($rank & self::RANK_SECTOR_IMPORTANCE) === self::RANK_IMPORTANCE_DONATOR){
			return 7;
		}
		return 5;
	}
	public static function kitpvp_isSafeArea(Vector3 $pos){
		// 110, 149
		// 11, -20
		$x = $pos->getFloorX();
		$y = $pos->getFloorY();
		$z = $pos->getFloorZ();
		$x1 = 110 <= $x;
		$x2 = $x < 149;
		$y1 = 1 <= $y;
		$y2 = $y < 97;
		$z1 = -20 <= $z;
		$z2 = $z < 11;
		return $x1 and $x2 and $y1 and $y2 and $z1 and $z2;
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
		if($kills >=3375) $tag="Myth";
		return $tag;
	}
	public static function kitpvp_maxKits($rank){
		if($rank instanceof Session){
			$rank = $rank->getRank();
		}
		$perm = $rank & self::RANK_SECTOR_PERMISSION;
		$imptc = $rank & self::RANK_SECTOR_IMPORTANCE;
		if($perm === self::RANK_PERM_OWNER or $imptc === self::RANK_IMPORTANCE_VIP_STAR){
			return 6;
		}
		if($perm === self::RANK_PERM_ADMIN or $imptc === self::RANK_IMPORTANCE_VIP_PLUS){
			return 5;
		}
		if($perm === self::RANK_PERM_MOD or $imptc === self::RANK_IMPORTANCE_VIP){
			return 4;
		}
		if($imptc === self::RANK_IMPORTANCE_DONATOR_PLUS or $imptc === self::RANK_IMPORTANCE_DONATOR){
			return 3;
		}
		return 2;
	}
	public static function kitpvp_killHeal(Session $session){
		$rank = $session->getRank();
		$i = $rank & self::RANK_SECTOR_IMPORTANCE;
		$p = $rank & self::RANK_SECTOR_PERMISSION;
		if($i === self::RANK_IMPORTANCE_VIP_PLUS){
			$session->getPlayer()->heal(15, EntityRegainHealthEvent::CAUSE_CUSTOM);
			return;
		}
		if($i === self::RANK_IMPORTANCE_VIP){
			$session->getPlayer()->heal(10, EntityRegainHealthEvent::CAUSE_CUSTOM);
			return;
		}
		if($i === self::RANK_IMPORTANCE_DONATOR_PLUS){
			$session->getPlayer()->heal(6, EntityRegainHealthEvent::CAUSE_CUSTOM);
			return;
		}
		if($i === self::RANK_IMPORTANCE_DONATOR or $p > 0){
			$session->getPlayer()->heal(4, EntityRegainHealthEvent::CAUSE_CUSTOM);
			return;
		}
		$session->getPlayer()->heal(10, EntityRegainHealthEvent::CAUSE_CUSTOM);
	}
	public static function kitpvp_getNpcLocation(Server $server, $id){
		$id = (int) $id;
		$l = $server->getLevelByName("world_pvp");
		$loc = new Location(143.5, 61.5, 0, 90, 0, $l);
		if($id === 1){
			$loc->z = -3.5;
		}
		elseif($id === 2){
			$loc->z = -1.5;
		}
		elseif($id === 3){
			$loc->z = -5.5;
		}
		elseif($id === 4){
			$loc->z = 1.5;
		}
		elseif($id === 5){
			$loc->z = -7.5;
		}
		elseif($id === 6){
			$loc->z = 3.5;
		}
		else{
			throw new \UnexpectedValueException("`" . var_export($id, true) . "`");
		}
		return $loc;
	}
	public static function kitpvp_getKitUpgradeInfo($column, $level){
		return self::$KITPVP_KITS[$column][$level];
	}
	public static function kitpvp_maxLevel($column){
		return max(array_keys(self::$KITPVP_KITS[$column]));
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
	public static function spleef_spawn(Server $server){
		return $server->getLevelByName("world_spleef")->getSpawnLocation();
	}
}

Settings::$KITPVP_KITS = [
	Settings::KIT_HELMET => [
		0 => new KitUpgradeInfo(new LeatherCap, 0),
		1 => new KitUpgradeInfo(new GoldHelmet, 500),
		2 => new KitUpgradeInfo(new ChainHelmet, 2500),
		3 => new KitUpgradeInfo(new IronHelmet, 12500, Settings::RANK_IMPORTANCE_DONATOR),
		4 => new KitUpgradeInfo(new DiamondHelmet, 50000, Settings::RANK_IMPORTANCE_VIP),
	],
	Settings::KIT_CHESTPLATE => [
		0 => new KitUpgradeInfo(new LeatherTunic, 0),
		1 => new KitUpgradeInfo(new GoldChestplate, 800),
		2 => new KitUpgradeInfo(new ChainChestplate, 4000),
		3 => new KitUpgradeInfo(new IronChestplate, 20000, Settings::RANK_IMPORTANCE_DONATOR),
		4 => new KitUpgradeInfo(new DiamondChestplate, 80000, Settings::RANK_IMPORTANCE_VIP),
	],
	Settings::KIT_LEGGINGS => [
		0 => new KitUpgradeInfo(new LeatherPants, 0),
		1 => new KitUpgradeInfo(new GoldLeggings, 700),
		2 => new KitUpgradeInfo(new ChainLeggings, 3500),
		3 => new KitUpgradeInfo(new IronLeggings, 17500, Settings::RANK_IMPORTANCE_DONATOR),
		4 => new KitUpgradeInfo(new DiamondLeggings, 70000, Settings::RANK_IMPORTANCE_VIP),
	],
	Settings::KIT_BOOTS => [
		0 => new KitUpgradeInfo(new LeatherBoots, 0),
		1 => new KitUpgradeInfo(new GoldBoots, 400),
		2 => new KitUpgradeInfo(new ChainBoots, 2000),
		3 => new KitUpgradeInfo(new IronBoots, 10000, Settings::RANK_IMPORTANCE_DONATOR),
		4 => new KitUpgradeInfo(new DiamondBoots, 40000, Settings::RANK_IMPORTANCE_VIP),
	],
	Settings::KIT_WEAPON => [
		0 => new KitUpgradeInfo(new StoneSword, 0),
		1 => new KitUpgradeInfo(new IronSword, 5000),
		2 => new KitUpgradeInfo(new DiamondSword, 20000, Settings::RANK_IMPORTANCE_DONATOR),
	],
	Settings::KIT_FOOD => [
		0 => new KitUpgradeInfo(Item::get(Item::MELON_SLICE, 0, 64), 0),
		1 => new KitUpgradeInfo(new Carrot(0, 64), 1000),
		2 => new KitUpgradeInfo(new Apple(0, 64), 5000),
		3 => new KitUpgradeInfo(Item::get(Item::BREAD), 15000),
		4 => new KitUpgradeInfo(Item::get(Item::COOKED_CHICKEN), 30000, Settings::RANK_IMPORTANCE_DONATOR),
		5 => new KitUpgradeInfo(Item::get(Item::COOKED_PORKCHOP), 50000, Settings::RANK_IMPORTANCE_VIP),
		6 => new KitUpgradeInfo(Item::get(Item::GOLDEN_APPLE), 80000, Settings::RANK_IMPORTANCE_VIP_PLUS),
	],
	Settings::KIT_BOW => [
		0 => new KitUpgradeInfo(Item::get(0), 0),
		1 => new KitUpgradeInfo(new Bow, 5000),
	],
	Settings::KIT_ARROWS => [
		0 => new KitUpgradeInfo(Item::get(0), 0),
		1 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 8), 3000),   // +8
		2 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 16), 8000),  // +8
		3 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 32), 10000, Settings::RANK_IMPORTANCE_DONATOR),  // +16
		4 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 64), 20000),  // +16
		5 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 96), 40000, Settings::RANK_IMPORTANCE_DONATOR_PLUS),  // +32
		6 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 128), 60000, Settings::RANK_IMPORTANCE_VIP), // +32
		7 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 192), 100000, Settings::RANK_IMPORTANCE_VIP_PLUS), // +64
		8 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 256), 1), // +64
	]
];
