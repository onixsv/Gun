<?php

declare(strict_types=1);

namespace alvin0319\Gun\form;

use alvin0319\Gun\Gun;
use alvin0319\Gun\GunLoader;
use OnixUtils\OnixUtils;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function count;
use function is_array;
use function is_numeric;

/**
 * 총 생성 폼 핸들링 클래스
 *
 * @package alvin0319\Gun\form
 * @author  alvin0319
 */
class GunCreateForm implements Form{

	public function jsonSerialize() : array{
		return [
			"type" => "custom_form",
			"title" => "총 생성",
			"content" => [
				[
					"type" => "input",
					"text" => "총 이름"
				],
				[
					"type" => "input",
					"text" => "총 재장전 쿨타임"
				],
				[
					"type" => "input",
					"text" => "총이 줄 데미지"
				],
				[
					"type" => "input",
					"text" => "총의 사거리"
				],
				[
					"type" => "toggle",
					"text" => "벽 뚫기 가능 여부",
					"default" => false
				],
				[
					"type" => "input",
					"text" => "총 탄약 개수"
				]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_array($data) || count($data) !== 6){
			return;
		}
		[$name, $cool, $damage, $distance, $canPassWall, $ammo] = $data;

		if(!isset($name) || GunLoader::getInstance()->getGun($name) !== null){
			OnixUtils::message($player, "총의 이름을 입력해주세요.");
			return;
		}
		if(!isset($cool) || !is_numeric($cool) || ($cool = (int) $cool) < 0){
			OnixUtils::message($player, "총의 재장전 시간을 입력해주세요.");
			return;
		}
		if(!isset($damage) || !is_numeric($damage) || ($damage = (int) $damage) < 1){
			OnixUtils::message($player, "총의 데미지를 입력해주세요.");
			return;
		}
		if(!isset($distance) || !is_numeric($distance) || ($distance = (int) $distance) < 1){
			OnixUtils::message($player, "총의 사거리를 입력해주세요.");
			return;
		}
		if(!isset($ammo) || !is_numeric($ammo) || ($ammo = (int) $ammo) < 1){
			OnixUtils::message($player, "총의 탄약 개수를 입력해주세요.");
			return;
		}
		$item = $player->getInventory()->getItemInHand();
		if($item->isNull()){
			OnixUtils::message($player, "아이템은 공기가 될 수 없습니다.");
			return;
		}
		$gun = new Gun($name, $item, $cool, $damage, $distance, $canPassWall, $ammo);
		GunLoader::getInstance()->addGun($gun);
		$player->getInventory()->setItemInHand(GunLoader::getInstance()->designItem($gun, $item));
		OnixUtils::message($player, "성공적으로 총을 추가했습니다.");
	}
}