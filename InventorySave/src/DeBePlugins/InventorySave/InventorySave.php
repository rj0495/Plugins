<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\InventorySave;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;

class InventorySave extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onPlayerDeath(PlayerDeathEvent $event){
		$event->setKeepInventory();
	}
}