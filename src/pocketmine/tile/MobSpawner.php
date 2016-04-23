<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://mcper.cn
 *
 */

namespace pocketmine\tile;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityGenerateEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\level\format\FullChunk;
use pocketmine\Player;

class MobSpawner extends Spawnable{

	public function __construct(FullChunk $chunk, Compound $nbt){
		if(!isset($nbt->EntityId)){
			$nbt->EntityId = new Int("EntityId", 0);
		}
		parent::__construct($chunk, $nbt);
		$this->lastUpdate = $this->getLevel()->getServer()->getTick();
		if($this->getEntityId() > 0) $this->scheduleUpdate();
	}

	public function getEntityId(){
		return $this->namedtag["EntityId"];
	}

	public function setEntityId($id){
		$this->namedtag->EntityId = new Int("EntityId", $id);
		$this->spawnToAll();
		if($this->chunk instanceof FullChunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
		}
		$this->scheduleUpdate();
	}

	public function getName() {
		return "Monster Spawner";
	}

	public function canUpdate() {
		if($this->getEntityId() === 0) return false;
		$hasPlayer = false;
		$count = 0;
		foreach($this->getLevel()->getEntities() as $e){
			if($e instanceof Player){
				if($e->distance($this->getBlock()) <= 15) $hasPlayer = true;
			}
			if($e::NETWORK_ID == $this->getEntityId()) $count++;
		}
		if($hasPlayer and $count < 15) return true; // Spawn limit = 15
		return false;
	}

	public function onUpdate(){
		if($this->closed === true){
			return false;
		}

		$this->timings->startTiming();

		if(!($this->chunk instanceof FullChunk)) return false;
		if($this->canUpdate()){
			$currentTick = $this->getLevel()->getServer()->getTick();
			$baseTick = $this->getLevel()->getServer()->getTicksPerSecondAverage();
			if(($currentTick - $this->lastUpdate) > $baseTick * 10){//Spawn per 10 seconds
				$this->lastUpdate = $currentTick;
				$up = $this->getLevel()->getBlock($this->getSide(Vector3::SIDE_UP));
				if($up->getId() == Item::AIR){
					$this->getLevel()->getServer()->getPluginManager()->callEvent($ev = new EntityGenerateEvent($this->add(0, 1, 0), $this->getEntityId(), EntityGenerateEvent::CAUSE_MOB_SPAWNER));
					if(!$ev->isCancelled()){
						$pos = $ev->getPosition();
						$nbt = new Compound("", [
							"Pos" => new Enum("Pos", [
								new Double("", $pos->x),
								new Double("", $pos->y),
								new Double("", $pos->z)
							]),
							"Motion" => new Enum("Motion", [
								new Double("", 0),
								new Double("", 0),
								new Double("", 0)
							]),
							"Rotation" => new Enum("Rotation", [
								new Float("", 0),
								new Float("", 0)
							]),
						]);
						$entity = Entity::createEntity($this->getEntityId(), $this->chunk, $nbt);
						$entity->spawnToAll();
					}
				}
			}
		}

		$this->timings->stopTiming();

		return true;
	}

	public function getSpawnCompound(){
		$c = new Compound("", [
			new String("id", Tile::MOB_SPAWNER),
			new Int("x", (int) $this->x),
			new Int("y", (int) $this->y),
			new Int("z", (int) $this->z),
			new Int("EntityId", (int) $this->getEntityId())
		]);

		return $c;
	}
}
