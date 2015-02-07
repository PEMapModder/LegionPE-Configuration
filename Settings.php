<?php

namespace legionpe\config;

use legionpe\games\spleef\SpleefArenaConfig;
use legionpe\LegionPE;
use legionpe\session\Session;
use pocketmine\block\Block;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\item\Apple;
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
use pocketmine\item\GoldShovel;
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
	const RANK_PERM_STAFF =                 0x00F0; // 240
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
		if(($rank & self::RANK_SECTOR_PERMISSION) === self::RANK_PERM_ADMIN){
			return 25;
		}
		if(($rank & self::RANK_SECTOR_IMPORTANCE) === self::RANK_IMPORTANCE_VIP_PLUS){
			return 25;
		}
		if(($rank & self::RANK_SECTOR_IMPORTANCE) === self::RANK_IMPORTANCE_VIP){
			return 20;
		}
		if(($rank & self::RANK_SECTOR_PERMISSION) === self::RANK_PERM_MOD){
			return 20;
		}
		if(($rank & self::RANK_SECTOR_IMPORTANCE) === self::RANK_IMPORTANCE_DONATOR_PLUS){
			return 15;
		}
		if(($rank & self::RANK_SECTOR_IMPORTANCE) === self::RANK_IMPORTANCE_DONATOR){
			return 10;
		}
		return 5;
	}
	public static function kitpvp_isSafeArea(Vector3 $pos){
		$x = $pos->x;
		$y = $pos->y;
		$z = $pos->z;
		if((127 <= $x) and ($x <= 152) and (-15 <= $z) and ($z <= 9) and ($y < 57)){
			return true;
		}
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
			return 5;
		}
		if($perm === self::RANK_PERM_ADMIN or $imptc === self::RANK_IMPORTANCE_VIP_PLUS){
			return 4;
		}
		if($perm === self::RANK_PERM_MOD or $imptc === self::RANK_IMPORTANCE_VIP){
			return 3;
		}
		if($imptc === self::RANK_IMPORTANCE_DONATOR_PLUS or $imptc === self::RANK_IMPORTANCE_DONATOR){
			return 2;
		}
		return 1;
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
		$loc = new Location(151.5, 54, 0, 90, 0, $l);
		if($id === 4){
			$loc->z = 1.5;
		}
		elseif($id === 2){
			$loc->z = -0.5;
		}
		elseif($id === 1){
			$loc->z = -2.5;
		}
		elseif($id === 3){
			$loc->z = -4.5;
		}
		elseif($id === 5){
			$loc->z = -6.5;
		}
		else{
			throw new \UnexpectedValueException("`" . var_export($id, true) . "`");
		}
		return $loc;
	}
	public static function kitpvp_getShopLocations(Server $server){
		$level = $server->getLevelByName("world_pvp");
		return [
			Settings::KIT_HELMET => new Location(135.5, 54, -14.5, 0, 0, $level),
			Settings::KIT_CHESTPLATE => new Location(137.5, 54, -14.5, 0, 0, $level),
			Settings::KIT_LEGGINGS => new Location(141.5, 54, -14.5, 0, 0, $level),
			Settings::KIT_BOOTS => new Location(143.5, 54, -14.5, 0, 0, $level),
			Settings::KIT_WEAPON => new Location(143.5, 54, 9.5, 180, 0, $level),
			Settings::KIT_FOOD => new Location(139.5, 54, 9.5, 180, 0, $level),
			Settings::KIT_ARROWS => new Location(135.5, 54, 9.5, 180, 0, $level),
		];
	}
	public static function kitpvp_getBowLocation(Server $server){
		return new Location(127.5, 54, -2.5, 270.0, 22, $server->getLevelByName("world_pvp"));
	}
	public static function kitpvp_getKitUpgradeInfo($column, $level){
		return self::$KITPVP_KITS[$column][$level];
	}
	/**
	 * @param $newLevel
	 * @return array <code>[int price, string name, string description, short damage, int fire aspect duration ticks, double knockback magnitude]</code>
	 */
	public static function kitpvp_getBowInfo($newLevel){
		switch($newLevel){
			case 0:
				return [0, "nihil (nothing)", "The art of Taoism: doing nothing. Don't leave spawn, that's how you keep your deaths from increasing.", 0, 0, 0];
			case 1:
				return [1500, "Wooden Bow", "The basic wood holds the key to everything, from the basic crafting table to the tip of the majestic diamond sword.", 6, 0, 0];
//			case 2:
//				return [5000, "Ghast Bone Bow", "The elastic ghast bone magnifies the power of ranged fighting, making ghasts one of the horrors in the Nether.", 8, 0, 0];
//			case 3:
//				return [12500, "Blaze Rod Bow", "Blaze rods build up the essence of fire, so its effect is still significant in the overworld where fire can be extinguished.", 9, 70, 0];
//			case 4:
//				return [30000, "Enderdragon Rib Bow", "The throned enderdragon rib is the tool that knocked numerous players into the void. Shall it be your tool?", 10, 80, 20];
		}
		return [PHP_INT_MAX, "You Guess?", "Uncertainty is a powerful weapon. Sadly, there are no potions of uncertainty in Minecraft.", 0, 0, 0];
	}
	public static function kitpvp_maxLevel($column){
		return max(array_keys(self::$KITPVP_KITS[$column]));
	}
	public static function defaultTeamLimit(){
		return 3;
	}
	public static function shiftTeamLimit($number){
		$number &= 0x0F;
		return $number << 20;
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
	public static function spleef_arenaCnt(){
		return 6;
	}
	public static function spleef_getArenaConfig($id){
		$config = new SpleefArenaConfig;
		$config->minPlayers = 2;
		$config->maxGameTicks = 2400;
		$config->maxWaitTicks = 1200;
		$config->minWaitTicks = 200;
		$config->floorHeight = 4; // includes the block layer, so there are 3 layers of air actually
		$config->playerItems = [new GoldShovel, new GoldShovel, new GoldShovel, new GoldShovel];
		switch($id){
			case 1:
				$config->name = "Arena 1";
				$config->spectatorSpawnLoc = new Location();
				$config->playerPrepLoc = new Location();
				$config->playerStartLocs = [
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
				];
				$config->radius = 16;
				$config->floors = 3;
				$config->floorHeight = 4;
				$config->lowestCenter = new Position();
				$config->floorMaterials = [
					Block::get(Block::STAINED_CLAY, 1),
					Block::get(Block::STAINED_CLAY, 7)
				];
				break;
			case 2:
				$config->name = "Arena 2";
				$config->spectatorSpawnLoc = new Location();
				$config->playerPrepLoc = new Location();
				$config->playerStartLocs = [
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
				];
				$config->radius = 16;
				$config->floors = 3;
				$config->floorHeight = 4;
				$config->lowestCenter = new Position();
				$config->floorMaterials = [
					Block::get(Block::STAINED_CLAY, 1),
					Block::get(Block::STAINED_CLAY, 7)
				];
				break;
			case 3:
				$config->name = "Arena 3";
				$config->spectatorSpawnLoc = new Location();
				$config->playerPrepLoc = new Location();
				$config->playerStartLocs = [
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
				];
				$config->radius = 16;
				$config->floors = 3;
				$config->floorHeight = 4;
				$config->lowestCenter = new Position();
				$config->floorMaterials = [
					Block::get(Block::STAINED_CLAY, 1),
					Block::get(Block::STAINED_CLAY, 7)
				];
				break;
			case 4:
				$config->name = "Arena 4";
				$config->spectatorSpawnLoc = new Location();
				$config->playerPrepLoc = new Location();
				$config->playerStartLocs = [
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
				];
				$config->radius = 16;
				$config->floors = 3;
				$config->floorHeight = 4;
				$config->lowestCenter = new Position();
				$config->floorMaterials = [
					Block::get(Block::STAINED_CLAY, 1),
					Block::get(Block::STAINED_CLAY, 7)
				];
				break;
			case 5:
				$config->name = "Arena 5";
				$config->spectatorSpawnLoc = new Location();
				$config->playerPrepLoc = new Location();
				$config->playerStartLocs = [
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
				];
				$config->radius = 16;
				$config->floors = 3;
				$config->floorHeight = 4;
				$config->lowestCenter = new Position();
				$config->floorMaterials = [
					Block::get(Block::STAINED_CLAY, 1),
					Block::get(Block::STAINED_CLAY, 7)
				];
				break;
			case 6:
				$config->name = "Arena 6";
				$config->spectatorSpawnLoc = new Location();
				$config->playerPrepLoc = new Location();
				$config->playerStartLocs = [
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
					new Location(),
				];
				$config->radius = 16;
				$config->floors = 3;
				$config->floorHeight = 4;
				$config->lowestCenter = new Position();
				$config->floorMaterials = [
					Block::get(Block::STAINED_CLAY, 1),
					Block::get(Block::STAINED_CLAY, 7)
				];
				break;
		}
		return $config;
	}
}

Settings::$KITPVP_KITS = [
	Settings::KIT_HELMET => [
		0 => new KitUpgradeInfo(new LeatherCap, 0),
		1 => new KitUpgradeInfo(new GoldHelmet, 250),
		2 => new KitUpgradeInfo(new ChainHelmet, 1250),
		3 => new KitUpgradeInfo(new IronHelmet, 6000, Settings::RANK_IMPORTANCE_DONATOR),
		4 => new KitUpgradeInfo(new DiamondHelmet, 7500, Settings::RANK_IMPORTANCE_VIP),
	],
	Settings::KIT_CHESTPLATE => [
		0 => new KitUpgradeInfo(new LeatherTunic, 0),
		1 => new KitUpgradeInfo(new GoldChestplate, 400),
		2 => new KitUpgradeInfo(new ChainChestplate, 2000),
		3 => new KitUpgradeInfo(new IronChestplate, 5000, Settings::RANK_IMPORTANCE_DONATOR),
		4 => new KitUpgradeInfo(new DiamondChestplate, 7500, Settings::RANK_IMPORTANCE_VIP),
	],
	Settings::KIT_LEGGINGS => [
		0 => new KitUpgradeInfo(new LeatherPants, 0),
		1 => new KitUpgradeInfo(new GoldLeggings, 350),
		2 => new KitUpgradeInfo(new ChainLeggings, 1750),
		3 => new KitUpgradeInfo(new IronLeggings, 8750, Settings::RANK_IMPORTANCE_DONATOR),
		4 => new KitUpgradeInfo(new DiamondLeggings, 35000, Settings::RANK_IMPORTANCE_VIP),
	],
	Settings::KIT_BOOTS => [
		0 => new KitUpgradeInfo(new LeatherBoots, 0),
		1 => new KitUpgradeInfo(new GoldBoots, 200),
		2 => new KitUpgradeInfo(new ChainBoots, 1000),
		3 => new KitUpgradeInfo(new IronBoots, 5000, Settings::RANK_IMPORTANCE_DONATOR),
		4 => new KitUpgradeInfo(new DiamondBoots, 7500, Settings::RANK_IMPORTANCE_VIP),
	],
	Settings::KIT_WEAPON => [
		0 => new KitUpgradeInfo(new StoneSword, 0),
		1 => new KitUpgradeInfo(new IronSword, 2500),
		2 => new KitUpgradeInfo(new DiamondSword, 7500, Settings::RANK_IMPORTANCE_DONATOR),
	],
	Settings::KIT_FOOD => [
		0 => new KitUpgradeInfo(Item::get(Item::MELON_SLICE, 0, 64), 0),
		1 => new KitUpgradeInfo(new Carrot(0, 64), 750),
		2 => new KitUpgradeInfo(new Apple(0, 64), 2500),
		3 => new KitUpgradeInfo(Item::get(Item::BREAD), 8500, Settings::RANK_IMPORTANCE_DONATOR),
		4 => new KitUpgradeInfo(Item::get(Item::COOKED_CHICKEN), 15000, Settings::RANK_IMPORTANCE_DONATOR_PLUS),
		5 => new KitUpgradeInfo(Item::get(Item::COOKED_PORKCHOP), 25000, Settings::RANK_IMPORTANCE_VIP),
		6 => new KitUpgradeInfo(Item::get(Item::GOLDEN_APPLE), 40000, Settings::RANK_IMPORTANCE_VIP_PLUS),
	],
	Settings::KIT_ARROWS => [
		0 => new KitUpgradeInfo(Item::get(0), 0),
		1 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 8), 500),   // +8
		2 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 16), 750),  // +8
		3 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 32), 1500, Settings::RANK_IMPORTANCE_DONATOR),  // +16
		4 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 64), 2500),  // +16
//		5 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 96), 20000, Settings::RANK_IMPORTANCE_DONATOR_PLUS),  // +32
//		6 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 128), 30000, Settings::RANK_IMPORTANCE_VIP), // +32
//		7 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 192), 50000, Settings::RANK_IMPORTANCE_VIP_PLUS), // +64
//		8 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 256), 100000), // +64
	]
];
