<?php

declare(strict_types=1);

namespace alvin0319\Gun;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\color\Color;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\network\mcpe\convert\GlobalItemTypeDictionary;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializerContext;
use pocketmine\network\mcpe\protocol\types\ParticleIds;
use pocketmine\player\Player;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\sound\ClickSound;

/**
 * 총 관련 핸들링 파트
 *
 * @package alvin0319\Gun
 * @author  alvin0319
 */
class Gun{
	/** @var string */
	protected string $name;
	/** @var Item */
	protected Item $item;
	/** @var int */
	protected int $coolTime;
	/** @var float */
	protected float $damage;
	/** @var int */
	protected int $distance;
	/** @var bool */
	protected bool $canPassBlock;
	/** @var int */
	protected int $count;

	public function __construct(string $name, Item $item, int $coolTime, int $damage, int $distance, bool $canPassBlock, int $count){
		$this->name = $name;
		$this->item = $item;
		$this->coolTime = $coolTime;
		$this->damage = $damage;
		$this->distance = $distance;
		$this->canPassBlock = $canPassBlock;
		$this->count = $count;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getItem() : Item{
		return clone $this->item;
	}

	public function getCoolTime() : int{
		return $this->coolTime;
	}

	public function getDamage() : float{
		return $this->damage;
	}

	public function getCount() : int{
		return $this->count;
	}

	public function getDistance() : int{
		return $this->distance;
	}

	public function canPassWall() : bool{
		return $this->canPassBlock;
	}

	public function shoot(Player $player) : void{
		$dv = $player->getDirectionVector();

		$packets = [];

		for($i = 0; $i < $this->distance; $i++){
			$vector = $dv->multiply($i)->addVector($player->getPosition());
			$vector->y += 1.7;
			$block = $player->getWorld()->getBlock($vector);

			if(!$this->canPassBlock && $block->isSolid()){
				break;
			}


			$packets[] = LevelEventPacket::standardParticle(ParticleIds::SPARKLER, (new Color(0, 255, 0))->toRGBA(), $vector);


			foreach($player->getWorld()->getEntities() as $entity){
				if(!$entity instanceof Living){
					continue;
				}
				if($entity === $player){
					continue;
				}
				if($entity->getPosition()->distance($vector) > 3){
					continue;
				}
				$entity->attack(new EntityDamageByEntityEvent($player, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->damage));
				$player->getWorld()->addSound($player->getPosition(), new ClickSound(1), [$player]);
				$player->getWorld()->addParticle($vector, new BlockBreakParticle(BlockFactory::getInstance()->get(BlockLegacyIds::REDSTONE_BLOCK, 0)), [$player]);
				break;
			}
		}
		$batch = PacketBatch::fromPackets(new PacketSerializerContext(GlobalItemTypeDictionary::getInstance()->getDictionary()), ...$packets);
		$promise = $player->getServer()->prepareBatch($batch, $player->getNetworkSession()->getCompressor());
		foreach($player->getWorld()->getPlayers() as $p){
			$p->getNetworkSession()->queueCompressed($promise);
		}
	}

	public function jsonSerialize() : array{
		return [
			"name" => $this->name,
			"item" => $this->item->jsonSerialize(),
			"coolTime" => $this->coolTime,
			"damage" => $this->damage,
			"distance" => $this->distance,
			"canPassBlock" => $this->canPassBlock,
			"count" => $this->count
		];
	}

	public static function jsonDeserialize(array $data) : Gun{
		return new Gun($data["name"], Item::jsonDeserialize($data["item"]), $data["coolTime"], $data["damage"], $data["distance"], $data["canPassBlock"], $data["count"]);
	}
}