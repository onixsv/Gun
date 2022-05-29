<?php

declare(strict_types=1);

namespace alvin0319\Gun\session;

use pocketmine\player\Player;

/**
 * 세션 관리 클래스
 *
 * @package alvin0319\Gun\session
 * @author  alvin0319
 */
class SessionManager{
	/** @var Session[] */
	protected static array $sessions = [];

	public static function addSession(Player $player) : Session{
		return self::$sessions[$player->getName()] = new Session($player);
	}

	public static function getSession(Player $player) : ?Session{
		return self::$sessions[$player->getName()] ?? null;
	}

	public static function getSessionNonNull(Player $player) : Session{
		if(!isset(self::$sessions[$player->getName()])){
			throw new \RuntimeException("Session for {$player->getName()} not found");
		}
		return self::$sessions[$player->getName()];
	}

	public static function removeSession(Player $player) : void{
		if(self::getSession($player) !== null){
			unset(self::$sessions[$player->getName()]);
		}
	}
}