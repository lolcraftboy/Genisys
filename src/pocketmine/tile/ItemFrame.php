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

use pocketmine\item\Item;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\nbt\NBT;

class ItemFrame extends Spawnable{

	public function __construct(FullChunk $chunk, Compound $nbt){
		if(!isset($nbt->Item)){
			$nbt->Item = NBT::putItemHelper(Item::get(Item::AIR));
			$nbt->Item->setName("Item");
		}

		if(!isset($nbt->ItemRotation)){
			$nbt->ItemRotation = new Byte("ItemRotation", 0);
		}

		if(!isset($nbt->ItemDropChance)){
			$nbt->ItemDropChance = new Float("ItemDropChance", 1.0);
		}

		parent::__construct($chunk, $nbt);
	}

	public function getName(){
		return "Item Frame";
	}

	public function getItemRotation(){
		return $this->namedtag["ItemRotation"];
	}

	public function setItemRotation($itemRotation){
		$this->namedtag->ItemRotation = new Byte("ItemRotation", $itemRotation);
		$this->setChanged();
	}

	public function getItem(){
		return NBT::getItemHelper($this->namedtag->Item);
	}

	public function setItem(Item $item,$setChanged = true){
		$nbtItem = NBT::putItemHelper($item);
		$nbtItem->setName("Item");
		$this->namedtag->Item = $nbtItem;
		if($setChanged) $this->setChanged();
	}

	public function getItemDropChance(){
		return $this->namedtag["ItemDropChance"];
	}

	public function setItemDropChance($chance = 1.0){
		$this->namedtag->ItemDropChance = new Float("ItemDropChance", $chance);
	}

	private function setChanged(){
		$this->spawnToAll();
		if($this->chunk instanceof FullChunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
		}
	}

	public function getSpawnCompound(){
		if(!isset($this->namedtag->Item)) $this->setItem(Item::get(Item::AIR), false);
		/** @var Compound $nbtItem */
		$nbtItem = clone $this->namedtag->Item;
		$nbtItem->setName("Item");
		if($nbtItem["id"] == 0){
			return new Compound("", [
				new String("id", Tile::ITEM_FRAME),
				new Int("x", (int) $this->x),
				new Int("y", (int) $this->y),
				new Int("z", (int) $this->z),
				new Byte("ItemRotation", 0),
				new Float("ItemDropChance", (float) $this->getItemDropChance())
			]);
		}else{
			return new Compound("", [
				new String("id", Tile::ITEM_FRAME),
				new Int("x", (int) $this->x),
				new Int("y", (int) $this->y),
				new Int("z", (int) $this->z),
				$nbtItem,
				new Byte("ItemRotation", (int) $this->getItemRotation()),
				new Float("ItemDropChance", (float) $this->getItemDropChance())
			]);
		}
	}
}