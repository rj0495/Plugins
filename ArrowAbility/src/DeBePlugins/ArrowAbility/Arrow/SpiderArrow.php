<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\ArrowAbility\Arrow;

use pocketmine\block\Block;

class SpiderArrow extends AbilityArrow{
	public function onUpdate($currentTick){
		if($this->onGround and ($this->motionX != 0 or $this->motionY != 0 or $this->motionZ != 0)){
			$this->getLevel->setBlock($this,Block::get(30));
			$this->kill();
		}
		return parent::onUpdate($currentTick);
	}
}