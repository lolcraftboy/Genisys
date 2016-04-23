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

/*
 * Copied from @beito123's FlowerPot plugin
 */

namespace pocketmine\tile;

use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Short;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\String;
use pocketmine\utils\Color;

class Cauldron extends Spawnable{

	public function __construct(FullChunk $chunk, Compound $nbt){
		if(!isset($nbt->PotionId)){
			$nbt->PotionId = new Short("PotionId", 0xffff);
		}
		if(!isset($nbt->SplashPotion)){
			$nbt->SplashPotion = new Byte("SplashPotion", 0);
		}
		if(!isset($nbt->Items) or !($nbt->Items instanceof Enum)){
			$nbt->Items = new Enum("Items", []);
		}
		parent::__construct($chunk, $nbt);
	}

	public function getPotionId(){
		return $this->namedtag["PotionId"];
	}

	public function setPotionId($potionId){
		$this->namedtag->PotionId = new Short("PotionId", $potionId);

		$this->spawnToAll();
		if($this->chunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
		}
	}

	public function hasPotion(){
		return $this->namedtag["PotionId"] !== 0xffff;
	}

	public function getSplashPotion(){
		return ($this->namedtag["SplashPotion"] == true);
	}

	public function setSplashPotion($bool){
		$this->namedtag->SplashPotion = new Byte("SplashPotion", ($bool == true) ? 1 : 0);

		$this->spawnToAll();
		if($this->chunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
		}
	}

	public function getCustomColor(){//
		if($this->isCustomColor()){
			$color = $this->namedtag["CustomColor"];
			$green = ($color >> 8) & 0xff;
			$red = ($color >> 16) & 0xff;
			$blue = ($color) & 0xff;
			return Color::getRGB($red, $green, $blue);
		}
		return null;
	}

	public function getCustomColorRed(){
		return ($this->namedtag["CustomColor"] >> 16) & 0xff;
	}

	public function getCustomColorGreen(){
		return ($this->namedtag["CustomColor"] >> 8) & 0xff;
	}

	public function getCustomColorBlue(){
		return ($this->namedtag["CustomColor"]) & 0xff;
	}

	public function isCustomColor(){
		return isset($this->namedtag->CustomColor);
	}

	public function setCustomColor($r, $g = 0xff, $b = 0xff){
		if($r instanceof Color){
			$color = ($r->getRed() << 16 | $r->getGreen() << 8 | $r->getBlue()) & 0xffffff;
		}else{
			$color = ($r << 16 | $g << 8 | $b) & 0xffffff;
		}
		$this->namedtag->CustomColor = new Int("CustomColor", $color);

		$this->spawnToAll();
		if($this->chunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
		}
	}

	public function clearCustomColor(){
		if(isset($this->namedtag->CustomColor)){
			unset($this->namedtag->CustomColor);
		}

		$this->spawnToAll();
		if($this->chunk){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
		}
	}

	public function getSpawnCompound(){
		$nbt = new Compound("", [
			new String("id", Tile::CAULDRON),
			new Int("x", (Int) $this->x),
			new Int("y", (Int) $this->y),
			new Int("z", (Int) $this->z),
			new Short("PotionId", $this->namedtag["PotionId"]),
			new Byte("SplashPotion", $this->namedtag["SplashPotion"]),
			new Enum("Items", $this->namedtag["Items"])//unused?
		]);

		if($this->getPotionId() === 0xffff and $this->isCustomColor()){
			$nbt->CustomColor = $this->namedtag->CustomColor;
		}
		return $nbt;
	}
}
