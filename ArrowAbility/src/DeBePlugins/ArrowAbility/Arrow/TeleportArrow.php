<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\ArrowAbility\Arrow;

use pocketmine\block\Block;

class TeleportArrow extends AbilityArrow{
	public function onUpdate($currentTick){
		$this->shootingEntity->teleport($this);
		if($this->onGround and ($this->motionX != 0 or $this->motionY != 0 or $this->motionZ != 0)){
			$this->shootingEntity->teleport($this);
			$this->kill();
		}
		return parent::onUpdate($currentTick);
	}
}