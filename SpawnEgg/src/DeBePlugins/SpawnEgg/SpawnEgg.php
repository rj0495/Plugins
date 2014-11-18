<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\SpawnEgg;

use pocketmine\plugin\PluginBase;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

class SpawnEgg extends PluginBase implements Listener{
	public function onEnable(){
		$spawnEgg = [15,10,11,12,13,14,16,33,38,39,34,35,37,39,36];
		foreach($spawnEgg as $se)
			Block::$creative[] =	[383, $se];
	}
}