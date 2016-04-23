<?php

/**
 * OpenGenisys Project
 *
 * @author PeratX
 */

namespace pocketmine\entity;

use pocketmine\nbt\tag\Byte;
use pocketmine\level\format\FullChunk;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\nbt\tag\Compound;
use pocketmine\Player;

class Bat extends FlyingAnimal{

	const NETWORK_ID = 19;

	const DATA_IS_RESTING = 16;

	public $width = 0.6;
	public $length = 0.6;
	public $height = 0.6;

	public $flySpeed = 0.8;
	public $switchDirectionTicks = 100;

	public function getName(){
		return "Bat";
	}

	public function initEntity(){
		$this->setMaxHealth(6);
		parent::initEntity();
	}

	public function __construct(FullChunk $chunk, Compound $nbt){
		if(!isset($nbt->isResting)){
			$nbt->isResting = new Byte("isResting", 0);
		}
		parent::__construct($chunk, $nbt);

		$this->setDataProperty(self::DATA_IS_RESTING, self::DATA_TYPE_BYTE, $this->isResting());
	}

	public function isResting(){
		return (int) $this->namedtag["isResting"];
	}

	public function setResting($resting){
		$this->namedtag->isResting = new Byte("isResting", $resting ? 1 : 0);
	}

	public function onUpdate($currentTick){
		if ($this->age > 20 * 60 * 10) {
			$this->kill();
		}
		return parent::onUpdate($currentTick);
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Bat::NETWORK_ID;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}