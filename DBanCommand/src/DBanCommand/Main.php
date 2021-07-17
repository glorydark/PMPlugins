<?php
namespace DBanCommand;

use pocketmine\plugin\PluginBase; //必需
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\config;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\utils\MainLogger;
use pocketmine\Player; //有关玩家

use pocketmine\event\player\PlayerCommandPreprocessEvent;

error_reporting(0);

class Main extends PluginBase implements Listener
{
public function onEnable()
{
	$this->getServer()->getPluginManager()->registerEvents($this,$this);
	$this->getLogger()->info("[DBanCommand]DBanCommand运行成功");
	$this->getLogger()->info("[DBanCommand]作者Glorydark");
	@mkdir($this->getDataFolder());
	$this->cfg = new Config($this->getDataFolder()."bancommand.yml",Config::YAML,array(
	"list" => array(),
	"admin" => array(),
	));
}

public function onCommand(CommandSender $sender, Command $cmd, $label, array $arg)
{
	switch($arg[0]){
	case "add":
	if($sender instanceof Player)
	{
		$sender->sendMessage("§6[DBanCommand]请在后台使用此指令");
		return true;
	}else{
		if(isset($arg[1]))
		{
			$this->cfg->reload();
			$list = $this->cfg->get("list");
			if(in_array($arg[1],$list)){
				$sender->sendMessage("§6[DBanCommand]您已经禁用了此命令了!");
				return true;
			}else{
				array_push($list,$arg[1]);
				$this->cfg->set("list", $list);
				$this->cfg->save();
				$sender->sendMessage("§6[DBanCommand]禁用指令 [ {$arg[1]} ] 成功!");
				return true;
			}
		}else{
			return false;
		}
	}
	break;
	case "remove":
	if($sender instanceof Player)
	{
		$sender->sendMessage("§6[DBanCommand]请在后台使用此指令");
		return true;
	}else{
		if(isset($arg[1]))
		{
			$this->cfg->reload();
			$list = $this->cfg->get("list");
			if(in_array($arg[1],$list)){
				$key = array_search($arg[1], $list);
				array_splice($list, $key, 1);
				$this->cfg->set("list", $list);
				$this->cfg->save();
				$sender->sendMessage("§6[DBanCommand]解禁指令 [ {$arg[1]} ] 成功!");
				return true;
			}else{
				$sender->sendMessage("禁止指令中暂无此指令喔!");
				return true;
			}
		}else{
			return false;
		}
	}
	break;
	case "添加管理":
	if(!isset($arg[1])) return false;
	if(!$sender instanceof Player){
		$this->cfg->reload();
		$adminlist = $this->cfg->get("admin");
		if(!in_array($arg[1],$adminlist)){
			array_push($adminlist,$arg[1]);
			$this->cfg->set("admin",$adminlist);
			$this->cfg->save();
			$sender->sendMessage("成功添加 {$arg[1]} 至管理员名单!");
		}else{
			$sender->sendMessage("您已经添加过该管理了!");
		}
	}else{
		$sender->sendMessage("请不要在游戏内尝试本指令!");
	}
	return true;
	break;
	case "list":
	$this->cfg->reload();
	$list .=implode(",",$this->cfg->get("list"));
	$adminlist .=implode(",",$this->cfg->get("adminlist"));
	$sender->sendMessage("受信任的世界:".$list);
	$sender->sendMessage("受信任的世界编辑员:".$adminlist);
	return true;
	break;
	default:
	unset($sender,$cmd,$label,$arg);
	return false;
	break;
	}
}

public function PlayerCommandPreprocessEvent(PlayerCommandPreprocessEvent $event){
	$this->cfg->reload();
	$list = $this->cfg->get("list");
	$pc = $event->getMessage();
	$arr = explode(" ",$pc);
	$admin = $this->cfg->get("admin");
	$p = $event->getPlayer();
	if($p instanceof Player){
		if(!in_array($event->getPlayer()->getName(),$admin)){
			foreach($list as $bancommand){
				$bc = "/".$bancommand;
				if($arr[0] == $bc || $pc == $bc){
					$event->setCancelled(true);
					$event->getPlayer()->sendMessage("§6[DBanCommand]本指令已经被禁止使用了!");
				}
			}
		}else{
			return true;
		}
	}else{
		return true;
	}
}
}