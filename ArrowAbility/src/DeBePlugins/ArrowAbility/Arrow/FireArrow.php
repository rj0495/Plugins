<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\ArrowAbility\Arrow;

use pocketmine\block\Block;

class FireArrow extends AbilityArrow{
	public function onUpdate($currentTick){
		$this->setHealth(99);
		$this->setOnFire(99);
		if($this->onGround and ($this->motionX != 0 or $this->motionY != 0 or $this->motionZ != 0)){
			$this->getLevel->setBlock($this,Block::get(51));
			$this->kill();
		}
		return parent::onUpdate($currentTick);
	}
}