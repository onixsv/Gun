<?php

declare(strict_types=1);

namespace alvin0319\Gun\task;

use alvin0319\Gun\session\SessionManager;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class GunCheckTask extends Task{

	public function onRun() : void{
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			$session = SessionManager::getSession($player);

			if($session === null){
				continue;
			}

			if($session->getNowGun() === null){
				continue;
			}

			if($session->canReloadGun()){
				$session->reloadGun();
			}
			$session->sendInfo();
		}
	}
}