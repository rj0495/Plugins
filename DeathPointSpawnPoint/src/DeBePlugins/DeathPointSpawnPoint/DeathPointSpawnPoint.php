<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\DeathPointSpawnPoint;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;

class DeathPointSpawnPoint extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onPlayerDeath(PlayerDeathEvent $event){
		$p = $event->getEntity();
		$pos = $p->getPosition();
		if($pos->getY() <= 0) $pos->add(0,9-$pos->getY(),0);
		$pos->add(0,1,0);
		$p->setSpawn($pos);
	}
}