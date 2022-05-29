<?php

declare(strict_types=1);

namespace alvin0319\Gun\session;

use alvin0319\Gun\Gun;
use pocketmine\player\Player;
use function time;

/**
 * 총 쿨타임 & 총알 관련 핸들링
 *
 * @package alvin0319\Gun\session
 * @author  alvin0319
 */
class Session{
	/**
	 * @var int[]
	 * name: count
	 */
	protected array $guns = [];
	/**
	 * @var int[]
	 * name: cool
	 */
	protected array $cools = [];
	/** @var Player */
	protected Player $player;
	/** @var Gun|null */
	protected ?Gun $gun = null;

	public function __construct(Player $player){
		$this->player = $player;
	}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function addGun(Gun $gun) : void{
		$this->guns[$gun->getName()] = $gun->getCount();
		$this->cools[$gun->getName()] = -1;
	}

	public function useGun() : void{
		$gun = $this->getNowGun();
		if($gun === null){
			return;
		}
		if(!isset($this->guns[$gun->getName()]) || !isset($this->guns[$gun->getName()])){
			$this->addGun($gun);
		}

		if($this->guns[$gun->getName()] !== 0){
			--$this->guns[$gun->getName()];
			$gun->shoot($this->player);
			return;
		}

		$this->reloadGun();

		if(time() - $this->cools[$gun->getName()] < $gun->getCoolTime()){
			//$this->player->sendTip("§c{$gun->getName()} 총의 장전이 끝나지 않았습니다. (" . (($this->cools[$gun->getName()] + $this->gun->getCoolTime()) - time()) . "초 남음)");
			return;
		}
		$this->unlockGun();
	}

	public function setNowGun(?Gun $gun = null) : void{
		$this->gun = $gun;
	}

	public function getNowGun() : ?Gun{
		return $this->gun;
	}

	public function sendInfo() : void{
		$this->prepare();
		$text = "§d< {$this->gun->getName()} §d>\n§f탄약: §d{$this->guns[$this->gun->getName()]}/{$this->gun->getCount()}\n§f";

		if($this->guns[$this->gun->getName()] === 0){
			$text .= "장전 쿨타임: " . (($this->gun->getCoolTime() + $this->cools[$this->gun->getName()]) - time()) . "초";
		}

		$this->player->sendTip($text);
	}

	public function canReloadGun() : bool{
		$gun = $this->gun;
		if($gun === null){
			return false;
		}
		$this->prepare();

		return $this->guns[$gun->getName()] <= 0;
	}

	public function reloadGun() : void{
		$gun = $this->gun;
		if($gun === null){
			return;
		}
		$this->prepare();

		if($this->cools[$gun->getName()] === -1){
			$this->cools[$gun->getName()] = time();
		}
	}

	public function unlockGun() : void{
		$gun = $this->gun;
		if($gun === null){
			return;
		}
		$this->prepare();

		$this->guns[$gun->getName()] = $gun->getCount();
		$this->cools[$gun->getName()] = -1;
	}

	public function isReloading() : bool{
		$gun = $this->gun;
		if($gun === null){
			return false;
		}
		$this->prepare();

		return $this->cools[$gun->getName()] !== -1;
	}

	public function check() : void{
		$gun = $this->gun;
		if($gun === null){
			return;
		}
		$this->prepare();

		if($this->canReloadGun()){
			$this->reloadGun();
		}

		if($this->isReloading()){
			if(time() - $this->cools[$gun->getName()] < $gun->getCoolTime()){
				return;
			}
			$this->unlockGun();
		}
	}

	public function prepare() : void{
		$gun = $this->gun;
		if($gun === null){
			return;
		}
		if(!isset($this->guns[$gun->getName()])){
			$this->guns[$gun->getName()] = $gun->getCount();
		}
		if(!isset($this->cools[$gun->getName()])){
			$this->cools[$gun->getName()] = -1;
		}
	}
}