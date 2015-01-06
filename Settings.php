<?php

namespace legionpe\config;

use legionpe\LegionPE;
use legionpe\session\Session;
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
	const RANK_IMPORTANCE_VIP_STAR =        0x000E; // 14 , 1110
	const RANK_SECTOR_IMPORTANCE =          0x000F; // 15 , 1111

	// permissions the rank has, 2 nibbles
	const RANK_PERM_DEFAULT =               0x0000;
	const RANK_PERM_MOD =                   0x0010;
	const RANK_PERM_ADMIN =                 0x0030;
	const RANK_PERM_OWNER =                 0x0070;
	/** Permission to be undetected by the auto AFK kicker. */
	const RANK_PERM_AFK =                   0x0100;
	/** Permission to bypass spam (spam detector won't detect at all). SpicyCapacitor ignores this permission and logs anyways. */
	const RANK_PERM_SPAM =                  0x0200;
	/** Permission to edit the world. */
	const RANK_PERM_WORLD_EDIT =            0x0400;
	/** Permission to execute raw PHP code by `/eval` */
	const RANK_PERM_DEV =                   0x0800;
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
		return $tag;
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
}
