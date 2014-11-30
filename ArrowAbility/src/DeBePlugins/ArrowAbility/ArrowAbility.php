<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\ArrowAbility;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Float;
use pocketmine\entity\Arrow;
use pocketmine\block\Block;
use pocketmine\Player;
use DeBePlugins\ArrowAbility\Arrow\AbilityArrow;
use DeBePlugins\ArrowAbility\Arrow\FireArrow;
use DeBePlugins\ArrowAbility\Arrow\TeleportArrow;
use DeBePlugins\ArrowAbility\Arrow\ExplosionArrow;
use DeBePlugins\ArrowAbility\Arrow\SpiderArrow;
use DeBePlugins\ArrowAbility\Arrow\HealArrow;
use DeBePlugins\ArrowAbility\Arrow\PowerArrow;

class ArrowAbility extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onEntityShootBow(EntityShootBowEvent $event){
		if(!$event->isCancelled()){
 			$arrow = $event->getProjectile();
 			$set = [$arrow->chunk,$arrow->nametag,$p];
			$newArrow = new AbilityArrow(...$set);
 			$p = $event->getEntity();
 			if($p instanceof Player){
 				if(!$p->isCreative()){
					$list = [
 						1 => FireArrow::class,
 						5 => TeleportArrow::class,
 						7 => SpiderArrow::class,
 						9 => HealArrow::class,
 						12 => PowerArrow::class
 					];
  				$inv = $p->getInventory();
 					foreach($inv->getContents() as $k => $i){
 						$d = $i->getDamage();
 						if($i->getID() == 351 && isset($list[$d])){
 							$i->setCount($i->getCount() - 1);
							$inv->setItem($k, $i);
 							$p->sendMessage([1 => "FireArrow",5 => "TeleportArrow",7 => "SpiderArrow",9 => "HealArrow",12 => "PowerArrow"][$d]);
 							$newArrow = new $list[$d](...$set);
 							break;
 						}
					}
 				}
 			}
 			$event->setProjectile($newArrow);
 		}
 	}

	public function onEntityDamage(EntityDamageEvent $event){
		if(!$event->isCancelled() && $event instanceof EntityDamageByEntityEvent){
 			$e = $event->getEntity();
			$arrow = $event->getDamager();
			$damage = round($event->getDamage()/2);
			if($arrow instanceof AbilityArrow){
				$shoter = $arrow->shootingEntity;
				if($shoter instanceof Player){
					if($arrow instanceof FireArrow) $e->setOnFire(rand(3,10));
					elseif($arrow instanceof TeleportArrow) $shoter->teleport($e);
					elseif($arrow instanceof SpiderArrow) $e->getLevel()->setBlock($e,Block::get(30));
					elseif($arrow instanceof HealArrow) $damage = -$damage;
					elseif($arrow instanceof PowerArrow)	$damage = round($damage*rand(2,3)/2);
					else return;
					$arrow->kill();
				}
			}
			$event->setDamage($damage);
		}
	}
}