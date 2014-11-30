<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\Inventory;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\item\Item;

class Inventory extends PluginBase{

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!isset($sub[0])) return false;
		$rm = "Usage: /Inventory ";
		$mm = "[Inventory] ";
		$ik = $this->isKorean();
		if(!isset($sub[1])){
			$sender->sendMessage($rm . ($ik ? "<명령어> <플레이어명>": "<Command> <PlayerName>"));
			return true;
		}else{
			$player = $this->getServer()->getPlayer(strtolower($sub[1]));
			if($player == null){
				$sender->sendMessage($sub[1] . " " . ($ik ? "는 잘못된 플레이어명입니다.": "is invalid player"));
				return true;
			}elseif($player->isCreative()){
				$sender->sendMessage($mm . $player->getName() . ($ik ? " 님은 크리에이티브입니다.": " is Creative mode"));
			}else{
				$inv = $player->getInventory();
				$n = $player->getName();
				$mn = $mm . $n;
			}
		}
		switch(strtolower($sub[0])){
			case "view":
			case "v":
			case "보기":
				$iv = [];
				foreach($inv->getContents() as $I){
					if($I->getID() !== 0 || $I->getCount() > 0){
						$iv[] = $I->getName() . " (" . $I->getID() . ":" . $I->getDamage() . ") [" . $I->getCount() . "]";
					}
				}
				$page = 1;
				if(isset($sub[2]) && is_numeric($sub[2])) $page = round($sub[0]);
				$list = ceil(count($iv) / 5);
				if($page >= $list) $page = $list;
				$m1 = count($inv);
				$m2 = $page / $list;
				$r = $mn . ($ik ? "의 인벤토리 (페이지 ": "\'s Inventory (Page ") . "$page / $list) [" . count($inv) . "]\n";
				$num = 0;
				foreach($iv as $v){
					$num++;
					if($num + 5 > $page * 5 && $num <= $page * 5) $r .= "  [$num] $v \n";
				}
			break;
			case "armor":
			case "a":
			case "갑옷":
			case "아머":
				$r = $mn . ($ik ? "의 갑옷 \n": "\'s Armors") . " \n";
				$r .= "  [Helmet] : " . $inv->getHelmet() . "\n";
				$r .= "  [ChestPlat] : " . $inv->getChestplate() . "\n";
				$r .= "  [Leggings] : " . $inv->getLeggings() . "\n";
				$r .= "  [Boots] : " . $inv->getBoots() . "\n";
			break;
			case "take":
			case "t":
			case "뺏기":
			case "빼앗기":
				if(!isset($sub[1])){
					$sender->sendMessage($rm . ($ik ? "<빼앗기> <플레이어명> <아이템ID> <갯수>": "<Take> <PlayerName> <ItemID> <Count>"));
					return true;
				}
				$i = Item::fromString($sub[2]);
				if($i->getID() == 0){
					$r = $sub[2] . " " . ($ik ? "는 잘못된 아이템ID입니다.": "is invalid itemID");
				}elseif(!is_numeric($sub[3]) || $sub[3] < 1){
					$r = $sub[3] . " " . ($ik ? "는 잘못된 갯수입니다.": "is invalid amount");
				}elseif(!$this->hasItem($player, $i, $sub[3])){
					$r = $ik ? "아이템을 가지고있지 않습니다.": "Don't have Item";
				}
				if(isset($r)){
					$sender->sendMessage($r);
					return true;
				}
				$i->setCount($sub[2]);
				foreach($inv->getContents() as $k => $item){
					if($item->getID() == $i->getID() && $item->getDamage() == $i->getDamage()){
						$sub[2] = $item->getCount() - $sub[2];
						if($sub[2] <= 0){
							$inv->clear($k);
							$sub[2] = -($sub[2]);
						}else{
							$inv->setItem($k, Item::get($item->getID(), $item->getDamage(), $sub[2]));
							break;
						}
					}
					$player->getInventory()->removeItem($i);
					$ii = " $i (" . $i->getCount() . ")";
					$r = $mn . ($ik ? "님의 아이템을 빼앗았습니다. ": "Take item the" . $n) . $ii;
					$player->sendMessage($mn . ($ik ? "님이 당신의 아이템을 빼앗았습니다. ": "is Take item from you ") . $ii);
				}
			break;
			case "clear":
			case "c":
			case "클리어":
			case "초기화":
				$inv->clearAll();
				$r = $mn . ($ik ? "의 인벤토리를 초기화했습니다.": "\'s Inventory is Clear") . " \n";
			break;
			default:
				return false;
			break;
		}
		if(isset($r)) $sender->sendMessage($r);
		return true;
	}

	public function hasItem($p, $i, $cnt){
		$c = $cnt;
		$cnt = 0;
		foreach($p->getInventory()->getContents() as $ii){
			if($ii->equals($i, $i->getDamage())) $cnt += $ii->getCount();
			if($cnt >= $c) break;
		}
		if($cnt < $c) return false;
		return true;
	}

	public function isKorean(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}