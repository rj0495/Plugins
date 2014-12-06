<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\CocoaBean;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\scheduler\CallbackTask;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\Info as ProtocolInfo;
use pocketmine\level\Position;
use pocketmine\inventory\BigShapelessRecipe;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\block\Air;

class CocoaBean extends PluginBase implements Listener{

	public function onEnable(){
		$this->grow = [];
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadBean();
	}

	public function onPlayerInteract(PlayerInteractEvent $event){
		$i = $event->getItem();
		$b = $event->getBlock();
		$p = $event->getPlayer();
		if($b->getID() == 17 && $b->getDamage() == 3){
			if($i->getID() == 351 && $i->getDamage() == 3){
				$f = $event->getFace();
				if($b->getSide($f)->getID() !== 0) return;
				switch($f){
					case 2:
						$meta = 0;
					break;
					case 3:
						$meta = 2;
					break;
					case 4:
						$meta = 3;
					break;
					case 5:
						$meta = 1;
					break;
				}
				if(!isset($meta)) return;
				$this->beanPlace($b->getSide($f), $meta);
				if(!$p->isCreative()){
					$i = $p->getInventory()->getItemInHand();
					$i->setCount($i->getCount() - 1);
					$inv = $p->getInventory();
					$inv->setItem($inv->getHeldItemSlot(), $i);
				}
			}
		}elseif($b->getID() == 127){
			if(!isset($this->grow[$this->getPos($b)])) $this->beanPlace($b, $b->getDamage());
			if($i->getID() == 351 && $i->getDamage() == 15){
				$this->beanGrow($b, true);
				if(!$p->isCreative()){
					$i = $p->getInventory()->getItemInHand();
					$i->setCount($i->getCount() - 1);
					$inv = $p->getInventory();
					$inv->setItem($inv->getHeldItemSlot(), $i);
				}
			}
		}
	}

	public function onBlockBreak(BlockBreakEvent $event){
		$b = $event->getBlock();
		if(isset($this->grow[$this->getPos($b)])){
			$c = false;
			if($event->getPlayer()->isCreative()) $c = true;
			$this->beanBreak($b, $c);
		}
	}

	public function onEntityDamage(EntityDamageEvent $event){
		$e = $event->getEntity();
		if($event->getCause() == 3){
			for($x = -1; $x < 2; $x++){
				for($z = -1; $z < 2; $z++){
					if($e->getLevel()->getBlockIdAt(round($e->getX()) + $x, round($e->getY() + 1), round($e->getZ() + $z)) == 127){
						$event->setCancelled();
						break;
					}
				}
			}
		}
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event){
		$pk = $event->getPacket();
		if($pk->pid() == ProtocolInfo::ENTITY_EVENT_PACKET && $pk->event == 9){
			$p = $event->getPlayer();
			$i = $p->getInventory()->getItemInHand();
			if($i->getID() == 357 && $p->getHealth() < 20 && !$p->isCreative()){
				$p->heal(2);
				$i->setCount($i->getCount() - 1);
				$inv = $p->getInventory();
				$inv->setItem($inv->getHeldItemSlot(), $i);
			}
		}
	}

	public function loadBean(){
		$this->loadYml();
		$this->grow = [];
		foreach($this->cb as $k => $v){
			$e = explode(":", $k);
			$l = $this->getServer()->getLevelByName($e[3]);
			$b = $this->beanPlace(Block::get(127, $v, new Position($e[0], $e[1], $e[2], $this->getServer()->getLevelByName($e[3]))), $v);
		}
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"beanUpdate" ]), 20);
		$this->getServer()->getCraftingManager()->registerRecipe((new BigShapelessRecipe(Item::get(357, 0, 8)))->addIngredient(Item::get(296, 0, 2))->addIngredient(Item::get(351, 3, 1)));
	}

	public function getPos($b){
		return $b->getX() . ":" . $b->getY() . ":" . $b->getZ() . ":" . $b->getLevel()->getName();
	}

	public function beanPlace($b, $meta){
		$b->getLevel()->setBlock($b, new CocoaBeanBlock($meta), true, true);
		$gb = $b->getLevel()->getBlock($b);
		$pos = $this->getPos($gb);
		$this->grow[$pos] = $gb;
		$this->saveYml();
		return $gb;
	}

	public function beanBreak($b, $c = false){
		$pos = $this->getPos($b);
		$b = $this->grow[$pos];
		unset($this->grow[$pos]);
		$b->getLevel()->setBlock($b, new Air(), false, false, true);
		if($c) return;
		if($b->getDamage() >= 8){
			$b->getLevel()->dropItem($b, Item::get(351, 3, mt_rand(1, 4)));
		}else{
			$b->getLevel()->dropItem($b, Item::get(351, 3, 1));
		}
		$this->saveYml();
	}

	public function beanGrow($b, $bone = false){
		$pos = $this->getPos($b);
		$b = $this->grow[$pos];
		$max = $b->getDamage() % 4 + 8;
		if($b->getDamage() < $max){
			if($bone){
				$b->setDamage($max);
			}else{
				$b->setDamage($b->getDamage() + 4);
			}
			$b->getLevel()->setBlock($b, $b, true, true, true);
		}
		$this->saveYml();
	}

	public function beanUpdate(){
		foreach($this->grow as $grow){
			if(!$this->checkTree($grow)) $this->beanBreak($grow);
			elseif(rand(0, 50) == 0) $this->beanGrow($grow);
		}
	}

	public function checkTree($b){
		switch($b->getDamage() % 4){
			case 0:
				$f = 3;
			break;
			case 1:
				$f = 4;
			break;
			case 2:
				$f = 2;
			break;
			case 3:
				$f = 5;
			break;
			default:
				return;
		}
		$tree = $b->getSide($f);
		if($tree->getID() !== 17 || $tree->getDamage() !== 3) return false;
		return true;
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$this->cocoa = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "CocoaBean.yml", Config::YAML, []);
		$this->cb = $this->cocoa->getAll();
	}

	public function saveYml(){
		$cb = [];
		foreach($this->grow as $k => $v){
			$cb[$k] = $v->getDamage();
		}
		ksort($cb);
		$this->cocoa->setAll($cb);
		$this->cocoa->save();
		$this->loadYml();
	}
}