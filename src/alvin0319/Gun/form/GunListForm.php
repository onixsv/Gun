<?php

declare(strict_types=1);

namespace alvin0319\Gun\form;

use alvin0319\Gun\Gun;
use Closure;
use pocketmine\form\Form;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use function array_map;
use function is_int;

/**
 * 총 목록/제거 핸들링 폼 클래스
 *
 * @package alvin0319\Gun\form
 * @author  alvin0319
 */
class GunListForm implements Form{
	/** @var Gun[] */
	protected array $guns = [];

	protected Closure $handleClosure;

	public function __construct(array $guns, Closure $handleClosure){
		$this->guns = $guns;
		Utils::validateCallableSignature(function(Player $player, int $data) : void{
		}, $handleClosure);
		$this->handleClosure = $handleClosure;
	}

	public function jsonSerialize() : array{
		return [
			"type" => "form",
			"title" => "총",
			"content" => "",
			"buttons" => array_map(function(Gun $gun) : array{
				return ["text" => $gun->getName()];
			}, $this->guns)
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_int($data)){
			return;
		}
		($this->handleClosure)($player, $data);
	}
}