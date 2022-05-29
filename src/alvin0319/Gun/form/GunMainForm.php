<?php

declare(strict_types=1);

namespace alvin0319\Gun\form;

use alvin0319\Gun\GunLoader;
use OnixUtils\OnixUtils;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function is_int;

/**
 * 총 관련 메인 폼 핸들러
 *
 * @package alvin0319\Gun\form
 * @author  alvin0319
 */
class GunMainForm implements Form{

	public function jsonSerialize() : array{
		return [
			"type" => "form",
			"title" => "총 메인 메뉴",
			"content" => "",
			"buttons" => [
				["text" => "나가기"],
				["text" => "총 생성하기"],
				["text" => "총 목록 보기"],
				["text" => "총 제거하기"]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_int($data)){
			return;
		}

		switch($data){
			case 1:
				$player->sendForm(new GunCreateForm());
				break;
			case 2:
				$player->sendForm(new GunListForm($guns = GunLoader::getInstance()->getGuns(), function(Player $player, int $data) use ($guns) : void{
					if(isset($guns[$data])){
						$player->getInventory()->addItem(GunLoader::getInstance()->designItem($guns[$data], $guns[$data]->getItem()));
						OnixUtils::message($player, "{$guns[$data]->getName()} 총을 꺼냈습니다.");
					}
				}));
				break;
			case 3:
				$player->sendForm(new GunListForm($guns = GunLoader::getInstance()->getGuns(), function(Player $player, int $data) use ($guns) : void{
					if(isset($guns[$data])){
						GunLoader::getInstance()->removeGun($guns[$data]);
						OnixUtils::message($player, "총을 제거했습니다.");
					}
				}));
				break;
		}
	}
}