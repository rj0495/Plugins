<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\BlockPickUp;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\Item;

class BlockPickUp extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onBlockBreak(BlockBreakEvent $event){
		if($event->isCancelled() || !$event->getPlayer()->hasPermission("debe.blockpickup.use")) return;
 		foreach($event->getBlock()->getDrops($event->getItem()) as $i)
			$event->getPlayer()->getInventory()->addItem(Item::get(...$i));
	}
}