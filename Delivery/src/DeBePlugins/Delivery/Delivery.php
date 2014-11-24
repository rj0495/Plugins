<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\Delivery;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\utils\Config;

class Delivery extends PluginBase{

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!isset($sub[0])) return false;
		$rm = "Usage: /Delivery ";
		$mm = "[Delivery] ";
		$ik = $this->isKorean();
		if($sender->getName() == "CONSOLE"){
			$r = $ik ? "게임내에서만 사용가능합니다.": "Please run this command in-game";
		}elseif(!isset($sub[0]) || !isset($sub[1]) || !isset($sub[2])){
			$r = $ik ? $rm . "<플레이어명> <아이템ID> <갯수>": $rm . "<PlayerName> <ItemID> <Amount>";
		}
		if(isset($r)){
			$sender->sendMessage($r);
			return true;
		}
		$player = $this->getServer()->getPlayer(strtolower($sub[0]));
		$i = Item::fromString($sub[1]);
		if($player == null){
			$r = $sub[0] . ($ik ? " 는 잘못된 플레이어명입니다.": "is invalid player");
		}elseif($i->getID() == 0){
			$r = $sub[1] . ($ik ? " 는 잘못된 아이템ID입니다.": "is invalid itemID");
		}elseif(!is_numeric($sub[2]) || $sub[2] < 1){
			$r = $sub[2] . ($ik ? " 는 잘못된 갯수입니다.": "is invalid amount");
		}elseif($player->isCreative()){
			$r = $mm . $player->getName() . ($ik ? " 님은 크리에이티브입니다.": " is Creative mode");
		}elseif(!$this->hasItem($sender, $i, $sub[2])){
			$r = $ik ? "아이템을 가지고있지 않습니다.": "Don't have Item";
		}
		if(isset($r)){
			$sender->sendMessage($r);
			return true;
		}
		$i->setCount($sub[2]);
		$inv = $sender->getInventory();
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
			$player->getInventory()->addItem($i);
			$pn = $player->getName();
 			$ii = "\n $i (" . $i->getCount() . ")";
			$sender->sendMessage($mm . ($ik ? $pn . "님에게 아이템을 전송했습니다.": "SendItem to " . $pn) . " $ii");
			$player->sendMessage($mm . $sender->getName() . ($ik ? "님이 당신에게 아이템을 전송했습니다.": "is SendItem to you") . " $ii");
		}
		return true;
	}

	public function hasItem($p, $i, $cnt){
		$c = $cnt;
		$cnt = 0;
		foreach($p->getInventory()->getContents() as $ii){
			if($ii->equals($i, $i->getDamage())) $cnt += $ik->getCount();
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