<?php
// This Plugin is Made by DeBe (hu6677@naver.com)
namespace DeBePlugins\Login;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

class Login extends PluginBase implements Listener{

	public function onEnable(){
		$this->player = [];
		$this->spawn = [];
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->loadYml();
	}

	public function onCommand(CommandSender $sender, Command $cmd, $label, array $sub){
		$rm = TextFormat::RED . "Usage: /" . $cmd->getName();
		$mm = "[Login] ";
		$ik = $this->isKorean();
		$cmd = strtolower($cmd->getName());
 		if($sender->getName() == "CONSOLE" && $cmd !== "loginop"){
			$sender->sendMessage($mm . ($ik ? "게임내에서만 사용가능합니다.": "Please run this command in-game"));
			return true;
		}elseif(!isset($sub[0]) || $sub[0] == "") return false;
		switch($cmd){
			case "login":
				if($this->isLogin($sender)){
					$sender->sendMessage($mm . ($ik ? "이미 로그인되었습니다.": "Already logined"));
				}else{
					$this->login($sender,$sub[0]);
				}
			break;
			case "register":
				if($this->isRegister($sender)){
					$sender->sendMessage($mm . ($ik ? "이미 가입되었습니다.": "Already registered"));
				}elseif(!isset($sub[1]) || $sub[1] == "" || $sub[0] !== $sub[1]){
					return false;
				}elseif(strlen($sub[0]) < 5){
					$sender->sendMessage($mm . ($ik ? "비밀번호가 너무 짧습니다.": "Password is too short"));
					return false;
				}else{
					$this->register($sender,$sub[0]);
					$this->login($sender,$sub[0]);
				}
			break;
			case "loginop":
				if(!isset($sub[1]) || $sub[1] == "" || !isset($this->lg[strtolower($sub[1])])){
					$sender->sendMessage($mm . ($ik ? "<플레이어명>을 확인해주세요.": "Please check <PlayerName>"));
					return false;
				}else{
					$sub[1] = strtolower($sub[1]);
					$pass = $this->lg[strtolower($sub[1])]["PW"];
					switch(strtolower($sub[0])){
						case "view":
						case "v":
						case "password":
						case "pw":
						case "비밀번호":
						case "비번":
							$sender->sendMessage($mm . $sub[1] . ($ik ? "님의 비밀번호 : ": "'s Password : ") . $pass);
						break;
						case "unregister":
						case "ur":
						case "u":
						case "탈퇴":
								unset($this->lg[$sub[1]]);
							$sender->sendMessage($mm . ($ik ? "$sub[1] 님의 비밀번호을 제거합니다." : "Delete $sub[1] 's password"));						
						break;
						case "change":
						case "c":
							if(!isset($sub[2]) || $sub[2] == ""){
								$sender->sendMessage($mm . ($ik ? "<플레이어명>을 확인해주세요.": "Please check <PlayerName>"));
								return false;
							}else{
								$this->lg[$sub[1]]["PW"] = $sub[2];
								$sender->sendMessage($mm . $sub[1] . ($ik ? "님의 비밀번호를 바꿨습니다. : ": "'s Password is changed : ") . "$pass => $sub[2]");
							}
						break;						
					}
				}
				$this->saveYml();
			break;
			default:
				return false;
			break;
		}
		return true;
	}

	public function onPlayerJoin(PlayerJoinEvent $event){
		$this->sendLogin($event->getPlayer(),true);
	}

	public function onPlayerRespawn(PlayerRespawnEvent $event){
		$this->spawn[$event->getPlayer()->getName()] = $event->getRespawnPosition();
	}

	public function onPlayerQuit(PlayerQuitEvent $event){
		$this->unLogin($event->getPlayer());
	}

	public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event){
		$p = $event->getPlayer();
		if(!$this->isLogin($p) && !in_array(strtolower(explode(" ",substr($event->getMessage(),1))[0]),["register","login"])) $event->setCancelled($this->sendLogin($p));
	}

	public function onPlayerChat(PlayerChatEvent $event){
		$p = $event->getPlayer();
		if(!$this->isLogin($p)){
			$this->sendLogin($p);
			$event->setCancelled();
		}
	}

	public function onPlayerMove(PlayerMoveEvent $event){
		$p = $event->getPlayer();
		if(!$this->isLogin($p)) $p->teleport($this->spawn[$event->getPlayer()->getName()]);
	}

	public function onPlayerInteract(PlayerInteractEvent $event){
		$event->setCancelled($this->isLogin($event->getPlayer()) ? false: true);
	}

	public function onBlockBreak(BlockBreakEvent $event){
		$event->setCancelled($this->isLogin($event->getPlayer()) ? false: true);
	}

	public function onBlockPlace(BlockPlaceEvent $event){
		$event->setCancelled($this->isLogin($event->getPlayer()) ? false: true);
	}

	public function onPlayerDropItem(PlayerDropItemEvent $event){
		$event->setCancelled($this->isLogin($event->getPlayer()) ? false: true);
	}

	public function onPlayerItemConsume(PlayerItemConsumeEvent $event){
		$event->setCancelled($this->isLogin($event->getPlayer()) ? false: true);
	}

	public function onInventoryOpen(InventoryOpenEvent $event){
		if($event->getPlayer()->getInventory() !== $event->getInventory()){
			$event->setCancelled($this->isLogin($event->getPlayer()) ? false: true);
		}
	}

	public function onEntityDamage(EntityDamageEvent $event){
		$event->setCancelled($this->isLogin($event->getEntity()) ? false: true);
	}

	public function register($p,$pw){
		$p->sendMessage("[Login] " . ($this->isKorean() ? "가입 완료": "Register to complete"));			
		$this->lg[strtolower($p->getName())] = ["PW" => $pw, "IP" => $p->getAddress()];
		$this->saveYml();
	}

	public function isRegister($p){
		return $p instanceof Player && isset($this->lg[strtolower($p->getName())]) ? true: false;
	}

	public function login($p,$pw = "",$auto = false){
		if($this->isLogin($p)) return;
		$n = strtolower($p->getName());
		$ik = $this->isKorean();
		if(!$auto){
			if($pw !== $this->lg[$n]["PW"]){
				$p->sendMessage("[Login] " . ($ik ? "로그인 실패": "Login to failed"));			
				return false;
			}
		}
 		$this->player[$n] = true;
		$this->lg[$n]["IP"] = $p->getAddress();
		$p->sendMessage("[Login] " . ($auto ? ($ik ? "자동": "Auto"): "") . ($ik ? "로그인 완료": "Login to complete"));			
		$this->saveYml();
		return true;
	}

	public function isLogin($p){
		return $p instanceof Player && isset($this->player[strtolower($p->getName())]) ? true: false;
	}

	public function unLogin($p){
		unset($this->player[strtolower($p->getName())]);
	}

	public function sendLogin($p,$l = false){
		if($p instanceof Player){
 			$mm = "[Login] ";
			$ik = $this->isKorean();
			$n = strtolower($p->getName());
			if($this->isLogin($p)){
			}elseif(!isset($this->lg[$n])){
				$p->sendMessage($mm . ($ik ? "당신은 가입되지 않았습니다.\n/Register <비밀번호> <비밀번호>": "You are not registered.\n/Register <Password> <Password>"));
			}elseif($l && $this->lg[$n]["IP"] == $p->getAddress()){
				$this->login($p,"",true);
			}else{
				$p->sendMessage($mm . ($ik ? "당신은 로그인하지 않았습니다.\n/Login <비밀번호>": "You are not logined.\n/Login <Password>"));
			}
		}
		return true;
	}

	public function loadYml(){
		@mkdir($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/");
		$this->login = new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "Login.yml", Config::YAML);
		$this->lg = $this->login->getAll();
	}

	public function saveYml(){
		ksort($this->lg);
		$this->login->setAll($this->lg);
		$this->login->save();
		$this->loadYml();
	}

	public function isKorean(){
		return (new Config($this->getServer()->getDataPath() . "/plugins/! DeBePlugins/" . "! Korean.yml", Config::YAML, ["Korean" => false ]))->get("Korean");
	}
}