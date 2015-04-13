<?php

namespace legionpe\config;

use legionpe\games\spleef\SpleefArena;
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
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\tile\Sign;

class Settings{
	// ranks of importance (how important the person is, like VERY Important Person) must not exceed 15 according to this, 1 nibble
	// the first two bits are the two actual permission-affecting nibbles
	const RANK_IMPORTANCE_DEFAULT =         0x0000; // 0  , 0000
	const RANK_IMPORTANCE_TESTER =          0x0001; // 1  , 0001
	const RANK_IMPORTANCE_DONATOR =         0x0004; // 4  , 0100
	const RANK_IMPORTANCE_DONATOR_PLUS =    0x0005; // 5  , 0101
	const RANK_IMPORTANCE_VIP =             0x000C; // 12 , 1100
	const RANK_IMPORTANCE_VIP_PLUS =        0x000D; // 13 , 1101
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
	const RANK_SECTOR_PERMISSION =          0x00F0;

	// precise (generally won't affect the program) degree of rank, 2 bits
	const RANK_PREC_STD =                   0x0000;
	const RANK_PREC_TRIAL =                 0x1000;
	const RANK_PREC_HEAD =                  0x2000;
	const RANK_SECTOR_PRECISION =           0x3000;

	// decorative ranks, which don't actually affect anything, 2 bits
	/** Here you are, the youtuber rank */
	const RANK_DECOR_YOUTUBER =             0x4000;
	const RANK_SECTOR_DECOR =              0xC000;

	// kit sections
	const KIT_HELMET = "helmet";
	const KIT_CHESTPLATE = "chestplate";
	const KIT_LEGGINGS = "leggings";
	const KIT_BOOTS = "boots";
	const KIT_WEAPON = "weapon";
	const KIT_FOOD = "food";
	const KIT_ARROWS = "arrows";
	/** @var KitUpgradeInfo[][] */
	public static $KITPVP_KITS = [];
	public static $INFECTED_WORLDS = ["infected_base_1"];
	public static $RANDOM_BROADCASTS = [
		"Use /coins to view your coins! You can use them in the shops.",
		"Use /hub to go back to hub!",
		"Use `/chat off` to turn off chat from everyone! (chat will be turned back on when you rejoin)",
		"Use `/ignore|unignore <player>` to ignore/unignore players!",
		"Use `/tpr`, `/tpa`, `/tpd` and `/tpc` to handle teleport requests (KitPvP only)!",
		"Use /stats to view your stats!",
		"Tired of your friends accusing you killing them? Use /friend. (KitPvP only)",
		"Use /restart to reset your parkour progress and teleport back to parkour spawn!",
		"More minigames are coming soon!",
		"Can't join because you're stuck at building terrain screen? Go to http://lgpe.co/omg",
		"Please report bugs by tweeting @PEMapModder_Flw on Twitter or creating an issue at http://lgpe.co/bug",
		"Changed your mind and wanna enable/disable IP auth? No problem, run `/auth ip yes|no`.",
		"Use /rules to check our server rules!",
		"Players blocking your way? /hide them!",
		"Do `/team create` to create your own team!",
		"Want to get more friends? Visit http://legionpvp.eu to donate.",
		"Want to have access to buying better items? Visit http://lgpe.co/l to donate.",
		"Want to create your own team? Visit http://lgpe.co/l to donate.",
		"Want to earn coins faster? Visit http://lgpe.co/l to donate.",
		"Use `/ch t` to switch to your team channel, `/ch g` to go back!",
		"Use `/chat off <channel>` to ignore chat messages from #<channel>!",
		"NEVER give ANYone including staff members your password; we don't know your password and we won't need it.",
		"NEVER give ANYone including staff members your password; we don't know your password and we won't need it.",
		"NEVER give ANYone including staff members your password; we don't know your password and we won't need it.",
	];

	public static function init(Server $server){
		foreach(["world", "world_parkour", "world_pvp", "world_spleef"] as $world){
			if($server->isLevelGenerated($world)){
				$server->loadLevel($world);
			}
		}
	}
	public static function getRandomBroadcast(){
		return self::$RANDOM_BROADCASTS[mt_rand(0, count(self::$RANDOM_BROADCASTS) - 1)];
	}
	public static function loginSpawn(Server $server){
		return $server->getLevelByName("world")->getSpawnLocation();
	}
	public static function portal(Player $p, LegionPE $main){
		if($p->getLevel()->getName() !== "world"){
			return null;
		}
		$x = $p->x;
		$y = $p->y;
		$z = $p->z;
		if((7 <= $y) and ($y <= 13) and (426 <= $z) and ($z <= 430)){
			if(-53 <= $x and $x <= -52){
//				$main->getLogger()->alert("Detected KitPvP for $p");
				return $main->getGame(Session::SESSION_GAME_KITPVP);
			}
			if(-131 <= $x and $x <= -130){
				//				LogCapacitor::log($log, __FILE__ . __LINE__, "Detected Spleef");
				return $main->getGame(Session::SESSION_GAME_SPLEEF);
			}
		}
		if((-95 <= $x) and ($x <= -89) and (7 <= $y) and ($y <= 13) and (466 <= $z) and ($z <= 468)){
			return $main->getGame(Session::SESSION_GAME_PARKOUR);
		}
		return null;
	}
	public static function portalBoost(Player $p, Block $block){
		list($x, $y, $z) = [$block->x, $block->y, $block->z];
		if($y === 7){
			if(($x === -79) and ($z === 428)){
				$tp = new Vector3(-80.5, 8, 428.5);
				$correct = new Vector3(-61.5, 8, 428.5);
			}
			if(($x === -92) and ($z === 441)){
				$tp = new Vector3(-91.5, 8, 439.5);
				$correct = new Vector3(-91.5, 8, 463.5);
			}
			if(($x === -105) and ($z === 428)){
				$tp = new Vector3(-102.5, 8, 428.5);
				$correct = new Vector3(-126.5, 8, 428.5);
			}
			if(($x === -92) and ($z === 415)){
				$tp = new Vector3(-91.5, 8, 417.5);
				$correct = new Vector3(-91.5, 8, 463.5);
			}
			if(isset($tp, $correct)){
//				$p->teleport($tp);
//				$p->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask(array(self::class, "boostTo"), [$p, $correct]), 12);
				self::boostTo($p, $correct);
			}
		}
	}
	public static function boostTo(Player $player, Vector3 $target){
		$vectors = $target->subtract($player);
		$vectors->y = 0;
		$player->setMotion($vectors->divide(5));
	}
	public static function portalBoost2(Player $player){
		if(isset($player->_portalOffTill)){
			if(microtime(true) <= $player->_portalOffTill){
				return;
			}
		}
		if($player->getLevel() !== "world"){
			return;
		}
		$x = $player->x;
		$z = $player->z;
		if(426 <= $z){
			if($z <= 430){
				if($x <= -102){
					if(-105 <= $x){
						$player->_portalOffTill = microtime(true) + 2;
						$player->setMotion(new Vector3(10, 0, 0));
					}
					//TODO
				}
			}
		}
	}
	public static function coinsFactor(Session $session, $force = false){
		if(!$session->isGrindingCoins() and !$force){
			return 1;
		}
		$rank = $session->getRank();
		switch($rank & self::RANK_SECTOR_IMPORTANCE){
			case self::RANK_IMPORTANCE_VIP_PLUS:
				return 3;
			case self::RANK_IMPORTANCE_VIP:
				return 2.5;
			case self::RANK_IMPORTANCE_DONATOR_PLUS:
				return 2;
			case self::RANK_IMPORTANCE_DONATOR:
				return 1.5;
			default:
				return 1;
		}
	}
	public static function getGrindDuration(Session $session){
		if($session->getRank() & self::RANK_IMPORTANCE_VIP){
			return 3600;
		}
		if($session->getRank() & self::RANK_IMPORTANCE_DONATOR){
			return 1800;
		}
		return 0;
	}
	public static function getGrindActivationWaiting(Session $session){
		if($session->getRank() & self::RANK_IMPORTANCE_VIP){
			return 129600;
		}
		if($session->getRank() & self::RANK_IMPORTANCE_DONATOR){
			return 216000;
		}
		return PHP_INT_MAX;
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
	public static function team_maxCapacity($rank){
		if($rank & self::RANK_PERM_ADMIN){
			return PHP_INT_MAX;
		}
		switch($rank & self::RANK_SECTOR_IMPORTANCE){
			case self::RANK_IMPORTANCE_VIP_PLUS:
				return 20;
			case self::RANK_IMPORTANCE_VIP:
				return 15;
			case self::RANK_IMPORTANCE_DONATOR_PLUS:
				return 10;
			case self::RANK_IMPORTANCE_DONATOR:
				return 5;
		}
		return 0;
	}
	public static function parkour_checkpoint_signs(Position $p){
		if($p->getLevel()->getName() !== "world_parkour"){
			return -1;
		}
		$x = $p->getFloorX();
		$y = $p->getFloorY();
		$z = $p->getFloorZ();
		if($y === 5){
			if($x === 1567 and $z === -936){
				return 1;
			}
			if($x === 1616 and $z === -1024){
				return 2;
			}
			if($x === 1652 and $z === -917){
				return 3;
			}
			if($x === 1669 and $z === -828){
				return 4;
			}
		}
		if($x === 1741 and $y === 7 and $z === -915){
			return 0;
		}
		return -1;
	}
	public static function parkour_checkpoint_startPos($id, Server $server){
		$level = $server->getLevelByName("world_parkour");
		switch($id){
			case 0:
				return new Position(1560.5, 5, -982.5, $level);
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
		return $vector3->y < 1.5; // accept error up to 0.5
	}
	public static function parkour_spawnpoint(Server $server){
		return new Position(1560, 8, -982, $server->getLevelByName("world_parkour"));
	}
	public static function parkour_teleportSign(Vector3 $v3){
		if($v3->x === 1569 and $v3->y === 5 and $v3->z === -932){
			return [
				new Vector3(1573, 5, -928),
				new Vector3(1582, 5, -923),
				new Vector3(1502, 5, -929),
				new Vector3(1598, 5, -939),
				new Vector3(1600, 5, -956)
			];
		}
		elseif($v3->x === 1619 and $v3->y === 5 and $v3->z === -1027){
			return [
				new Vector3(1626, 5, -1029),
				new Vector3(1636, 5, -1026),
				new Vector3(1646, 5, -1017),
				new Vector3(1655, 5, -1004),
				new Vector3(1658, 5, -996),
			];
		}
		elseif($v3->x === 1673 and $v3->y === 5 and $v3->z === -826){
			return [
				new Vector3(1697, 5, -825),
				new Vector3(1713, 5, -839)
			];
		}
		return null;
	}
	public static function kitpvp_spawn(Server $server){
//		foreach($server->getLevels() as $level){
//			$server->getLogger()->debug("Level " . $level->getName() . " (" . $level->getFolderName() . ")");
//		}
		return new Position(123, 65, -3, $server->getLevelByName("world_pvp"));
	}
	public static function kitpvp_maxFriends($rank){
		if($rank instanceof Session){
			$rank = $rank->getRank();
		}
		if(($rank & self::RANK_SECTOR_PERMISSION) === self::RANK_PERM_OWNER){
			return PHP_INT_MAX;
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
		if($perm === self::RANK_PERM_OWNER){
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
			$session->getPlayer()->heal(15, new EntityRegainhealthEvent($session->getPlayer(), 15, EntityRegainHealthEvent::CAUSE_CUSTOM));
			return;
		}
		if($i === self::RANK_IMPORTANCE_VIP){
			$session->getPlayer()->heal(10, new EntityRegainhealthEvent($session->getPlayer(), 10, EntityRegainHealthEvent::CAUSE_CUSTOM));
			return;
		}
		if($i === self::RANK_IMPORTANCE_DONATOR_PLUS){
			$session->getPlayer()->heal(6, new EntityRegainhealthEvent($session->getPlayer(), 6, EntityRegainHealthEvent::CAUSE_CUSTOM));
			return;
		}
		if($i === self::RANK_IMPORTANCE_DONATOR or $p > 0){
			$session->getPlayer()->heal(4, new EntityRegainHealthEvent($session->getPlayer(), 4, EntityRegainHealthEvent::CAUSE_CUSTOM));
			return;
		}
		$session->getPlayer()->heal(2, new EntityRegainHealthEvent($session->getPlayer(), 2, EntityRegainHealthEvent::CAUSE_CUSTOM));
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
	 * @param int $newLevel
	 * @return number[] {@code [int price, string name, string description, short damage, int fire aspect duration ticks, double knockback magnitude]}
	 */
	public static function kitpvp_getBowInfo($newLevel){
		switch($newLevel){
			case 0:
				return [
					0,
					"Nihil (nothing)",
					"The art of Taoism: doing nothing. Don't leave spawn, that's how you keep " .
					"your deaths from increasing.",
					0, 0, 0,
					0
				];
			case 1:
				return [
					1500,
					"Wooden Bow",
					"The basic wood holds the key to everything, from the basic crafting table " .
					"to the tip of the majestic diamond sword.",
					6, 0, 0,
					0
				];
			case 2:
				return [
					5000,
					"Ghast Bone Bow",
					"The elastic ghast bone magnifies the power of ranged fighting, making " .
					"ghasts one of the horrors in the Nether.",
					8, 0, 0,
					self::RANK_IMPORTANCE_DONATOR
				];
			case 3:
				return [
					12500,
					"Blaze Rod Bow",
					"Blaze rods build up the essence of fire, so its effect is still " .
					"significant in the overworld where fire can be extinguished.",
					9, 50, 0,
					self::RANK_IMPORTANCE_DONATOR_PLUS
				];
			case 4:
				return [
					30000,
					"Ender Rib Bow",
					"The throned enderdragon rib is the tool that knocked numerous players " .
					"into the void. Shall it be your weapon?",
					10, 80, 20,
					self::RANK_IMPORTANCE_VIP
				];
		}
		return [PHP_INT_MAX, "You Guess?", "Uncertainty is a powerful weapon. Sadly, there are no potions of uncertainty in Minecraft.", 0, 0, 0, PHP_INT_MAX];
	}
	public static function kitpvp_maxLevel($column){
		return max(array_keys(self::$KITPVP_KITS[$column]));
	}
	public static function spleef_spawn(Server $server){
		return new Location(922, 10, -2, 270.0, 0.0, $server->getLevelByName("world_spleef"));
	}
	public static function spleef_arenaCnt(){
		return 4;
	}
	public static function spleef_getArenaConfig($id, Server $server){
		$level = $server->getLevelByName("world_spleef");
		$config = new SpleefArenaConfig;
		$config->minPlayers = 2;
		$config->maxGameTicks = 2400;
		$config->maxWaitTicks = 1200;
		$config->minWaitTicks = 200;
		$config->floors = 1;
		$config->floorHeight = 4; // includes the block layer, so there are 3 layers of air actually
		$config->lowestY = 3;
		$config->playerItems = [new GoldShovel, new GoldShovel, new GoldShovel, new GoldShovel];
		$config->floorMaterials = [
			Block::get(Block::SNOW_BLOCK)
		];
		switch($id){
			case 1:
				$config->name = "Arena 1";
				$config->spectatorSpawnLoc = new Location(925, 11, -40, 0.0, 90, $level);
				$config->playerPrepLoc = new Location(925, 4.5, -40, 0.0, 90, $level);
				$config->playerStartLocs = [
					new Location(925, 4.5, -56, 0.0, 0.0, $level),
					new Location(925, 4.5, -24, 180.0, 0.0, $level),
					new Location(909, 4.5, -40, 270.0, 0.0, $level),
					new Location(940, 4.5, -40, 90.0, 0.0, $level),
					new Location(935, 4.5, -51, 45.0, 0.0, $level),
					new Location(916, 4.5, -31, 225.0, 0.0, $level),
					new Location(936, 4.5, -30, 135.0, 0.0, $level),
					new Location(917, 4.5, -50, 315.0, 0.0, $level),
				];
				$config->fromx = 906;
				$config->tox = 943;
				$config->fromz = -59;
				$config->toz = -21;
				break;
			case 2:
				$config->name = "Arena 2";
				$config->spectatorSpawnLoc = new Location(966, 11, 0, 0.0, 90, $level);
				$config->playerPrepLoc = new Location(966, 4.5, 0, 0.0, 90, $level);
				$config->playerStartLocs = [
					new Location(950, 4.5, 0, 270.0, 0.0, $level),
					new Location(982, 4.5, 0, 90.0, 0.0, $level),
					new Location(966, 4.5, -14, 0.0, 0.0, $level),
					new Location(966, 4.5, 16, 180.0, 0.0, $level),
					new Location(958, 4.5, -8, 315.0, 0.0, $level),
					new Location(977, 4.5, 11, 135.0, 0.0, $level),
					new Location(976, 4.5, -10, 45.0, 0.0, $level),
					new Location(956, 4.5, 10, 225.0, 0.0, $level),
				];
				$config->fromx = 947;
				$config->tox = 985;
				$config->fromz = -18;
				$config->toz = 19;
				break;
			case 3:
				$config->name = "Arena 3";
				$config->spectatorSpawnLoc = new Location(925, 11, 42, 0.0, 90, $level);
				$config->playerPrepLoc = new Location(925, 4.5, 42, 0.0, 90, $level);
				$config->playerStartLocs = [
					new Location(910, 4.5, 42, 270.0, 0.0, $level),
					new Location(941, 4.5, 42, 90.0, 0.0, $level),
					new Location(925, 4.5, 26, 0.0, 0.0, $level),
					new Location(925, 4.5, 58, 180.0, 0.0, $level),
					new Location(917, 4.5, 32, 315.0, 0.0, $level),
					new Location(935, 4.5, 52, 135.0, 0.0, $level),
					new Location(915, 4.5, 53, 225.0, 0.0, $level),
					new Location(936, 4.5, 32, 45.0, 0.0, $level),
				];
				$config->fromx = 907;
				$config->tox = 944;
				$config->fromz = 23;
				$config->toz = 61;
				break;
			case 4:
				$config->name = "Arena 4";
				$config->spectatorSpawnLoc = new Location(884, 11, 1, 0.0, 90, $level);
				$config->playerPrepLoc = new Location(884, 4.5, 1, 0.0, 90, $level);
				$config->playerStartLocs = [
					new Location(868, 4.5, 1, 270.0, 0.0, $level),
					new Location(900, 4.5, 1, 90.0, 0.0, $level),
					new Location(884, 4.5, 17, 180.0, 0.0, $level),
					new Location(884, 4.5, -14, 0.0, 0.0, $level),
					new Location(876, 4.5, 9, 225.0, 0.0, $level),
					new Location(892, 4.5, -6, 45.0, 0.0, $level),
					new Location(894, 4.5, 11, 135.0, 0.0, $level),
					new Location(874, 4.5, -8, 315.0, 0.0, $level),
				];
				$config->fromx = 865;
				$config->tox = 903;
				$config->fromz = -17;
				$config->toz = 20;
				break;
		}
		return $config;
	}
	public static function spleef_getType(Position $pos, &$arena, &$spectator){
		$arena = -1;
		if($pos->getLevel()->getName() !== "world_spleef"){
			return;
		}
		if($pos->y === 11){
			if($pos->z === -16){
				if((923 <= $pos->x) and ($pos->x <= 926)){
					$arena = 1;
					$spectator = 0;
					return;
				}
				if((928 <= $pos->x) and ($pos->x <= 931)){
					$arena = 1;
					$spectator = 1;
					return;
				}
			}
			if($pos->x === 942){
				if((-1 <= $pos->z) and ($pos->z <= 2)){
					$arena = 2;
					$spectator = 0;
					return;
				}
				if((3 <= $pos->z) and ($pos->z <= 6)){
					$arena = 2;
					$spectator = 1;
					return;
				}
			}
			if($pos->z === 18){
				if((925 <= $pos->x) and ($pos->x <= 928)){
					$arena = 3;
					$spectator = 0;
					return;
				}
				if((919 <= $pos->x) and ($pos->x <= 922)){
					$arena = 3;
					$spectator = 1;
					return;
				}
			}
			if($pos->x === 908){
				if((3 <= $pos->z) and ($pos->z <= 6)){
					$arena = 4;
					$spectator = 0;
					return;
				}
				if((-3 <= $pos->z) and ($pos->z <= 0)){
					$arena = 4;
					$spectator = 1;
					return;
				}
			}
			if($pos->z === -20){
				if((923 <= $pos->x) and ($pos->x <= 931)){
					$arena = 1;
					$spectator = 2;
					return;
				}
			}
			if($pos->x === 946){
				if((-1 <= $pos->z) and ($pos->z <= 6)){
					$arena = 2;
					$spectator = 2;
					return;
				}
			}
			if($pos->z === 22){
				if((919 <= $pos->x) and ($pos->x <= 928)){
					$arena = 3;
					$spectator = 2;
					return;
				}
			}
			if($pos->x === 904){
				if((-3 <= $pos->z) and ($pos->z <= 6)){
					$arena = 4;
					$spectator = 2;
					return;
				}
			}
		}
	}
	public static function spleef_isArenaFloor(Position $pos){
		if($pos->getLevel()->getName() !== "world_spleef"){
			return false;
		}
		return ($pos->y === 3) and ($pos->getLevel()->getBlock($pos)->getId() === Block::SNOW_BLOCK);
	}
	public static function spleef_updateArenaSigns(SpleefArena $arena){
		$level = $arena->getGame()->getMain()->getServer()->getLevelByName("world_spleef");
		$cnt = $arena->countPlayers();
		$max = $arena->getConfig()->getMaxPlayers();
		if(!$arena->isPlaying()){
			$full = $cnt === $max;
			$texts = [
				$arena->getConfig()->name,
				$full ? "[FULL]":"[JOIN]",
				"$cnt / $max"
			];
		}
		else{
			$texts = [
				$arena->getConfig()->name,
				"[PLAYING]",
				"$cnt / $max"
			];
		}
		switch($arena->getId()){
			case 1:
				for($x = 923; $x <= 926; $x++){
					$tile = $level->getTile(new Vector3($x, 11, -16));
					if($tile instanceof Sign){
						$tile->setText(...$texts);
					}
				}
				for($x = 928; $x <= 931; $x++){
					$tile = $level->getTile(new Vector3($x, 11, -16));
					if($tile instanceof Sign){
						$tile->setText($texts[0], "[SPECTATE]", $texts[2]);
					}
				}
				break;
			case 2:
				for($z = -1; $z <= 3; $z++){
					$tile = $level->getTile(new Vector3(942, 11, $z));
					if($tile instanceof Sign){
						$tile->setText(...$texts);
					}
				}
				for($z = 4; $z <= 8; $z++){
					$tile = $level->getTile(new Vector3(942, 11, $z));
					if($tile instanceof Sign){
						$tile->setText($texts[0], "[SPECTATE]", $texts[2]);
					}
				}
				break;
			case 3:
				for($x = 925; $x <= 928; $x++){
					$tile = $level->getTile(new Vector3($x, 11, 18));
					if($tile instanceof Sign){
						$tile->setText(...$texts);
					}
				}
				for($x = 919; $x <= 922; $x++){
					$tile = $level->getTile(new Vector3($x, 11, 18));
					if($tile instanceof Sign){
						$tile->setText($texts[0], "[SPECTATE]", $texts[2]);
					}
				}
				break;
			case 4:
				for($z = 3; $z <= 6; $z++){
					$tile = $level->getTile(new Vector3(908, 11, $z));
					if($tile instanceof Sign){
						$tile->setText(...$texts);
					}
				}
				for($x = -3; $x <= 0; $x++){
					$tile = $level->getTile(new Vector3(908, 11, $z));
					if($tile instanceof Sign){
						$tile->setText($texts[0], "[SPECTATE]", $texts[2]);
					}
				}
				break;
		}
	}
	/**
	 * @param Position $pos
	 * @return Vector3[]|null
	 */
	public static function spleef_incineratorInfo(Position $pos){
		if($pos->getLevel()->getName() !== "world_spleef"){
			return null;
		}
		if($pos->getLevel()->getName() === "world_spleef" and ($pos->x === 936 and $pos->z === -12 or $pos->x === 934 and $pos->z === -14) and ($pos->y === 11 or $pos->y === 12)){
			return [new Vector3(937, 12, -14), new Vector3(937, 22, -14)];
		}
		return null;
	}
	public static function infected_getRandomBaseWorld(){
		return self::$INFECTED_WORLDS[mt_rand(0, count(self::$INFECTED_WORLDS) - 1)];
	}
	public static function easter_getSnowballCount($rank){
		if($rank instanceof Session){
			$rank = $rank->getRank();
		}
		$imporatnce = $rank & self::RANK_SECTOR_IMPORTANCE;
		if($imporatnce === self::RANK_IMPORTANCE_VIP_PLUS){
			return 8;
		}
		if($imporatnce === self::RANK_IMPORTANCE_VIP){
			return 4;
		}
		return 0;
	}
	public static function checkInvisibility(Position $pos){
		if(true){
			return false;
		}
		if($pos->getLevel()->getName() === "world_pvp"){
			if((121 <= $pos->x) and ($pos->x < 126)){
				if((-5 <= $pos->x) and ($pos->x < 0)){
					return $pos->y > 63;
				}
			}
			return false;
		}
		if($pos->getLevel()->getName() === "world"){
			if((-93 <= $pos->x) and ($pos->x < -91)){
				if((427 <= $pos->z) and ($pos->z < 430)){
					return true;
				}
			}
			return false;
		}
		if($pos->getLevel()->getName() === "world_parkour"){
			if((920 <= $pos->x) and ($pos->x < 924)){
				if((-4 <= $pos->z) and ($pos->z < 0)){
					return $pos->y > 8;
				}
			}
		}
		return false;
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
		2 => new KitUpgradeInfo(new DiamondSword, 7500, Settings::RANK_IMPORTANCE_DONATOR_PLUS),
	],
	Settings::KIT_FOOD => [
		0 => new KitUpgradeInfo(Item::get(Item::MELON_SLICE, 0, 64), 0),
		1 => new KitUpgradeInfo(new Carrot(0, 64), 750),
		2 => new KitUpgradeInfo(new Apple(0, 64), 2500),
		3 => new KitUpgradeInfo(Item::get(Item::BREAD, 0, 64), 8500, Settings::RANK_IMPORTANCE_DONATOR),
		4 => new KitUpgradeInfo(Item::get(Item::COOKED_CHICKEN, 0, 64), 15000, Settings::RANK_IMPORTANCE_DONATOR_PLUS),
		5 => new KitUpgradeInfo(Item::get(Item::COOKED_PORKCHOP, 0, 64), 25000, Settings::RANK_IMPORTANCE_VIP),
//		6 => new KitUpgradeInfo(Item::get(Item::GOLDEN_APPLE, 0, 64), 40000, Settings::RANK_IMPORTANCE_VIP_PLUS),
	],
	Settings::KIT_ARROWS => [
		0 => new KitUpgradeInfo(Item::get(0), 0),
		1 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 8), 500),   // +8
		2 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 16), 750),  // +8
		3 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 32), 1500, Settings::RANK_IMPORTANCE_DONATOR),  // +16
		4 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 64), 2500, Settings::RANK_IMPORTANCE_DONATOR_PLUS),  // +16
		5 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 96), 20000),  // +32
		6 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 128), 30000, Settings::RANK_IMPORTANCE_VIP), // +32
		7 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 192), 50000, Settings::RANK_IMPORTANCE_VIP_PLUS), // +64
		8 => new KitUpgradeInfo(Item::get(Item::ARROW, 0, 256), 100000), // +64
	]
];
