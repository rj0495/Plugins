<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\ChatMute;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerChatEvent;

class ChatMute extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		if(!isset($sub[0])) return false;
		$rm = "Usage: /ChatMute ";
		$mm = "[ChatMute] ";
		$ik = $this->isKorean();
		switch(strtolower($sub[0])){
			case "mute":
			case "m":
			case "추가":
			case "차단":
			case "음소거":
				if(!isset($sub[1])){
					$sender->sendMessage($rm . ($ik ? "음소거 <플레이어명>": "Mute(M) <PlayerName>"));
					return true;
				}else{
					$player = $this->getServer()->getPlayer(strtolower($sub[1]));
					if($player == null){
						$sender->sendMessage($mm . $sub[1] . ($ik ? " 는 잘못된 플레이어명입니다.": "is invalid player"));
						return true;
					}else{
						$name = $player->getName();
						$mute = $this->mute->get("Mute");
						if(isset($mute[$name])){
							unset($mute[$name]);
							$r = $mm . $name . ($ik ? "의 음소거를 해제합니다.": " has UnMute");
						}else{
							$mute[$name] = true;
							$r = $mm . $name . ($ik ? "의 음소거를 설정합니다.": " has Mute");
						}
						$this->mute->set("Mute", $mute);
					}
				}
			break;
			case "allmute":
			case "a":
			case "전체추가":
			case "전체차단":
			case "전체음소거":
				if($this->mute->get("AllMute")){
					$this->mute->set("AllMute", false);
					$m = $mm . ($ik ? "모든 채팅 음소거를 해제합니다.": "AllMute Off");
				}else{
					$this->mute->set("AllMute", true);
					$m = $mm . ($ik ? "모든 채팅 음소거를 설정합니다.": "AllMute On");
				}
			break;
			default:
				return false;
			break;
		}
		if(isset($r)){
			$sender->sendMessage($r);
		}elseif(isset($m)){
			$this->getServer()->broadcastMessage($m);
		}
		$this->saveYml();
		return true;
	}

	public function onPlayerChat(PlayerChatEvent $event){
		if($event->isCancelled()) return;
		$player = $event->getPlayer();
		$m = "[ChatMute] ";
		if($this->mute->get("AllMute")){
			$player->sendMessage($m . ($ik ? "모든 채팅 음소거 상태입니다..": "All Mute"));
			$event->setCancelled();
		}else{
			$name = $player->getName();
			if(isset($this->mute->get("Mute")[$name])){
				$player->sendMessage($m . ($ik ? "당신은 채팅 음소거 상태입니다.": "ChatMute"));
				$event->setCancelled();
			}
		}
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$this->mute = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "ChatMute.yml", Config::YAML, ["AllMute" => false,"Mute" => [] ]);
	}

	public function saveYml(){
		$this->mute->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}