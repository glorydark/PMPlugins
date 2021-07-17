<?php
namespace  DCurrencySystem;

use pocketmine\plugin\PluginBase; //必需
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\MainLogger;
use pocketmine\Player; //有关玩家
use pocketmine\utils\config;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\Server;

use onebone\economyapi\EconomyAPI;

class Main extends PluginBase implements Listener
{
public function onEnable()
{
	@mkdir($this->getDataFolder());
	$this->getServer()->getPluginManager()->registerEvents($this,$this);
	$this->getLogger()->info("[DCurrencySystem]DCurrencySystem插件运行成功");
	$this->getLogger()->info("[DCurrencySystem]作者Glorydark Q1083215364");
	$this->typelist=new Config($this->getDataFolder()."type.yml",Config::YAML,array());
	$this->pcfg=new Config($this->getDataFolder()."player.yml",Config::YAML,array());
	$this->redeemlist=new Config($this->getDataFolder()."redeemlist.yml",Config::YAML,array());
}//finish

public function onJoin(PlayerJoinEvent $event){
	$p = $event->getPlayer()->getName();
	$this->pcfg->reload();
	$this->typelist->reload();
	$typelist = $this->typelist->getAll();
	$pcfg = $this->pcfg->get($p);
	foreach($typelist as $type){
		if(!isset($pcfg[$type])){
			$pcfg[$type] = 0;
		}
	}
	$this->pcfg->set($p, $pcfg);
	$this->pcfg->save();
}

public function onCommand(CommandSender $sender, Command $cmd, $label, array $arg)
{
	$costsfg = array();
	$costsfg1 = array();
	switch($arg[0]){
		case "兑换":
		$this->typelist->reload();
		$this->redeemlist->reload();
		$typearray = $this->typelist->getAll();
		if($sender instanceof Player){
			if($this->redeemlist->exists($arg[1])){
				$this->redeemlist->reload();
				$itemcfg = $this->redeemlist->get($arg[1]);
				$displayname = $itemcfg["displayname"];
				$descriptions = $itemcfg["descriptions"];
				$costs = $itemcfg["costs"];
				$moneycost = $itemcfg["moneycost"];
				$commands = $itemcfg["commands"];
				$messages = $itemcfg["messages"];
				$this->pcfg->reload();
				$pown = $this->pcfg->get($sender->getName());
			    $costsfg = explode("#",$costs);
				$costsfgbf = explode("#",$costs);
				$state += 0;
				$surplus = array();
				$use = array();
				foreach($costsfg as $c){
					$costsfg1 = explode("@",$c);
					array_push($use,$costsfg1[0].$costsfg1[1]."个");
					if(intval($costsfg1[1]) > intval($pown[$costsfg1[0]])){
						$state += 1;
					}
				}
				$use1 = implode(",",$use);
				if($moneycost != 0){
					if(EconomyAPI::getInstance()->MyMoney($sender) < intval($moneycost)){
						$state += 2;
						$sender->sendMessage("金币不足");
					}
				}
				if($state == 0){
					if($moneycost != 0){
						EconomyAPI::getInstance()->reduceMoney($sender,$moneycost);
					};
					foreach($costsfgbf as $c){
						$costsfg1 = explode("@",$c);
						$pown[$costsfg1[0]] -= $costsfg1[1];
						array_push($surplus,$costsfg1[0]."剩余".$pown[$costsfg1[0]]);
				    }
					$surplus1 = implode(",",$surplus);
					if(count($commands) != 0){
						foreach($commands as $cmd){
							$cmd = str_replace("%p", $sender->getName(), $cmd);
							$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $cmd);
						}
					}
					if(count($messages) != 0){
						foreach($messages as $msg){
							$get = $arg[1];
							$msg = str_replace("%p", $sender->getName(), $msg);
							$msg = str_replace("%surplus", $surplus1, $msg);
							$msg = str_replace("%use", $use1, $msg);
							$msg = str_replace("%get", $get, $msg);
							$sender->sendMessage($msg);
							Server::getInstance()->broadcastMessage(TextFormat::YELLOW.$p."通过晶石合成系统获得了".$get);
						}
					}
					$this->pcfg->set($sender->getName(),$pown);
					$this->pcfg->save();
				}else{
					$sender->sendMessage("[兑换系统]您的物品或金币不够");
			    }
			}else{
				$sender->sendMessage("[兑换系统]没有此兑换项，请重试！");
			}
		}else{
			$sender->sendMessage("请前往游戏内使用指令！");
    	}
		unset($costsfg,$costsfg1);
	return true;
   	break;
	case "添加类型":
	$this->typelist->reload();
	$typearray = $this->typelist->getAll();
	if(isset($arg[1])){
		if(!in_array($arg[1],$typearray)){
			if($sender instanceof Player){
				if($sender->isOp()){
					$sender->sendMessage(TextFormat::GREEN."Succeeded!");
					array_push($arg[1],$typearray);
					$this->typelist->setAll($typearray);
					$this->typelist->save();
					var_dump($typearray);
				}else{
					$sender->sendMessage(TextFormat::RED."Failed!");
				}
			}else{
				$sender->sendMessage(TextFormat::GREEN."Succeeded!");
				array_push($arg[1],$typearray);
				$this->typelist->setAll($typearray);
				$this->typelist->save();
				var_dump($typearray);
			}
		}else{
			$sender->sendMessage(TextFormat::RED."[ElN Security]Sorry,this type of currency is loaded.Please check it again!");
		}
	}else{
		$sender->sendMessage(TextFormat::RED."[ElN Security]Please check your command!");
	}
	return true;
	break;
	case "给予"://例:dcs 给予 sender T1武器晶石 1
	$this->pcfg->reload();
	if($sender instanceof Player){
		if($sender->isOp()){
			if(isset($arg[1]) && isset($arg[2]) && isset($arg[3])){
				$pown = $this->pcfg->get($arg[1]);
				$pown[$arg[2]] = (int)$pown[$arg[2]] + (int)$arg[3];
				$this->pcfg->set($arg[1],$pown);
				$this->pcfg->save();
				$sender->sendMessage($sender->getName()."成功给予玩家".$arg[2].$arg[3]."个");
			}else{
				$sender->sendMessage(TextFormat::RED."[ElN Security]Please check your command!");
			}
		}else{
		 	$sender->kick("\r".TextFormat::RED."suspicious operation!",true);
		}
	}else{
		$pown = $this->pcfg->get($sender->getName());
		if(isset($arg[1]) && isset($arg[2]) && isset($arg[3])){
			$pown = $this->pcfg->get($arg[1]);
			$pown[$arg[2]] = intval($pown[$arg[2]]) + intval($arg[3]);
			$this->pcfg->set($arg[1],$pown);
			$this->pcfg->save();
			$sender->sendMessage($sender->getName()."成功给予玩家".$arg[2].$arg[3]."个");
			Server::getInstance()->broadcastMessage(TextFormat::YELLOW."玩家".$arg[1]."获得了".$arg[2]." ".$arg[3]."个");
		}else{
			$sender->sendMessage(TextFormat::RED."[ElN Security]Please check your command!");
		}
	}
	return true;
	break;
	case "info":
	case "查询":
	if(!$sender instanceof Player) { $sender->sendMessage("请在游戏内查询！"); return; }
	$this->pcfg->reload();
	$this->typelist->reload();
	$own = $this->pcfg->get($sender->getName());
	$sender->sendMessage(TextFormat::YELLOW."您的所有货币类型的数量如下:");
	foreach($this->typelist->getAll() as $type){
		$sender->sendMessage(TextFormat::YELLOW.$type.$own[$type]."个");
	}
	return true;
	break;
	default:
	$sender->sendMessage("/dcs info/创建类型/给予/兑换");
	return true;
	break;
    }
}
}