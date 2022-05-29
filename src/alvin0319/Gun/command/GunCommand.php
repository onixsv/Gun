<?php

declare(strict_types=1);

namespace alvin0319\Gun\command;

use alvin0319\Gun\form\GunMainForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

/**
 * 총 관련 명령어 핸들링 클래스
 *
 * @package alvin0319\Gun\command
 * @author  alvin0319
 */
class GunCommand extends Command{

	public function __construct(){
		parent::__construct("총");
		$this->setPermission("gun.cmd");
		$this->setDescription("총 명령어입니다.");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			return false;
		}
		$sender->sendForm(new GunMainForm());
		return true;
	}
}