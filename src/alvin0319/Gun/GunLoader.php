<?php

declare(strict_types=1);

namespace alvin0319\Gun;

use alvin0319\Gun\command\GunCommand;
use alvin0319\Gun\session\SessionManager;
use alvin0319\Gun\task\GunCheckTask;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use function array_values;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function json_encode;

/**
 * 오닉스서버 총 플러그인
 *
 * @package alvin0319\Gun
 * @author  alvin0319
 */
class GunLoader extends PluginBase implements Listener{
	use SingletonTrait;

	/** @var Gun[] */
	protected array $guns = [];

	public function onLoad() : void{
		self::$instance = $this;
	}

	protected function onEnable() : void{
		if(file_exists($file = $this->getDataFolder() . "Gun.json")){
			$data = json_decode(file_get_contents($file), true);
			foreach($data as $name => $gunData){
				$gun = Gun::jsonDeserialize($gunData);
				$this->guns[$gun->getName()] = $gun;
			}
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->getServer()->getCommandMap()->register("gun", new GunCommand());

		$this->getScheduler()->scheduleRepeatingTask(new GunCheckTask(), 20);
	}

	protected function onDisable() : void{
		$arr = [];

		foreach($this->guns as $name => $gun){
			$arr[$gun->getName()] = $gun->jsonSerialize();
		}
		file_put_contents($this->getDataFolder() . "Gun.json", json_encode($arr));
	}

	public function addGun(Gun $gun) : void{
		$this->guns[$gun->getName()] = $gun;
	}

	public function removeGun(Gun $gun) : void{
		unset($this->guns[$gun->getName()]);
	}

	public function designItem(Gun $gun, Item $item) : Item{
		$item->setCustomName("{$gun->getName()} §f총");
		$item->setLore([
			"총 재장전 시간: {$gun->getCoolTime()}",
			"총 데미지: {$gun->getDamage()}",
			"총 사거리: {$gun->getDistance()}m",
			"벽뚫 가능 여부: " . ($gun->canPassWall() ? "O" : "X"),
			"탄약: {$gun->getCount()}"
		]);
		return $item;
	}

	/**
	 * @return Gun[]
	 */
	public function getGuns() : array{
		return array_values($this->guns);
	}

	public function getGun(string $name) : ?Gun{
		return $this->guns[$name] ?? null;
	}

	public function getGunByItem(Item $item) : ?Gun{
		foreach($this->getGuns() as $gun){
			if($gun->getItem()->equals($item, true, false)){
				return $gun;
			}
		}
		return null;
	}

	/**
	 * @param PlayerInteractEvent $event
	 *
	 * @handleCancelled true
	 */
	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		$player = $event->getPlayer();
		$session = SessionManager::getSessionNonNull($player);

		if($session->getNowGun() !== null){
			$session->useGun();
		}
	}

	public function onPlayerItemHeld(PlayerItemHeldEvent $event) : void{
		$player = $event->getPlayer();
		$item = $event->getItem();

		$session = SessionManager::getSessionNonNull($player);

		if(($gun = $this->getGunByItem($item)) !== null){
			$session->setNowGun($gun);
		}else{
			$session->setNowGun();
		}
	}

	public function onPlayerJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();

		SessionManager::addSession($player);
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void{
		$player = $event->getPlayer();

		SessionManager::removeSession($player);
	}
}