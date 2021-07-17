<?php
namespace DWorldProtect;

use pocketmine\plugin\PluginBase; //必需
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\config;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\utils\MainLogger;
use pocketmine\Player; //有关玩家
use pocketmine\Level\Position;

use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

class Main extends PluginBase implements Listener
{
public function onEnable()
{
	$this->getServer()->getPluginManager()->registerEvents($this,$this);
	$this->getLogger()->info("[DWorldProtect]DWorldProtect运行成功");
	$this->getLogger()->info("[DWorldProtect]作者Glorydark");
	@mkdir($this->getDataFolder());
	$this->cfg = new Config($this->getDataFolder()."config.yml",Config::YAML,array(
	"worlds" => array(),
	"admins" => array(),
	));
}

public function onCommand(CommandSender $sender, Command $cmd, $label, array $arg)
{
	switch($arg[0]){
	case "world":
	if($sender instanceof Player)
	{
		$sender->sendMessage("§6[DWorldProtect]请在后台使用此指令");
		return true;
	}else{
		if(isset($arg[1]))
		{
			$this->cfg->reload();
			$list = $this->cfg->get("worlds");
			if(in_array($arg[1],$list)){
				$key = array_search($arg[1], $world);
				array_splice($world, $key, 1);
				$this->cfg->set("worlds", $list);
				$this->cfg->save();
				$sender->sendMessage("§6[DWorldProtect]成功删除对 {$arg[1]} 世界的保护!");
				return true;
			}else{
				array_push($list,$arg[1]);
				$this->cfg->set("worlds", $list);
				$this->cfg->save();
				$sender->sendMessage("§6[DWorldProtect]成功添加对 {$arg[1]} 世界的保护!");
				return true;
			}
		}else{
			return false;
		}
	}
	break;
	case "admin":
	if($sender instanceof Player)
	{
		$sender->sendMessage("§6[DWorldProtect]请在后台使用此指令");
		return true;
	}else{
		if(isset($arg[1]))
		{
			$this->cfg->reload();
			$list = $this->cfg->get("admins");
			if(in_array($arg[1],$list)){
				$key = array_search($arg[1], $world);
				array_splice($world, $key, 1);
				$this->cfg->set("admins", $list);
				$this->cfg->save();
				$sender->sendMessage("§6[DWorldProtect]成功删除世界管理员: {$arg[1]} !");
				return true;
			}else{
				array_push($list,$arg[1]);
				$this->cfg->set("admins", $list);
				$this->cfg->save();
				$sender->sendMessage("§6[DWorldProtect]成功添加世界管理员: {$arg[1]} !");
				return true;
			}
		}else{
			return false;
		}
	}
	break;
	case "list":
	$this->cfg->reload();
	$list .=implode(",",$this->cfg->get("worlds"));
	$list1 .=implode(",",$this->cfg->get("admins"));
	$sender->sendMessage("§6[DWorldProtect]目前受保护的世界:");
	$sender->sendMessage($list);
	$sender->sendMessage("§6[DWorldProtect]目前受信任的管理员:");
	$sender->sendMessage($list1);
	return true;
	break;
	default:
	unset($sender,$cmd,$label,$arg);
	return false;
	break;
	}
}

public function BlockBreakEvent(BlockBreakEvent $event){
	$this->cfg->reload();
	$plist = $this->cfg->get("admins");
	$wlist = $this->cfg->get("worlds");
	if(!in_array($event->getPlayer()->getName(),$plist) && in_array($event->getPlayer()->getLevel()->getName(),$wlist))
	{
		$event->setCancelled(true);
		$event->getPlayer()->sendPopup("§6[DWorldProtect]您所在的世界被保护了!");
	}
}

public function BlockPlaceEvent(BlockPlaceEvent $event){
	$this->cfg->reload();
	$plist = $this->cfg->get("admins");
	$wlist = $this->cfg->get("worlds");
	if(!in_array($event->getPlayer()->getName(),$plist) && in_array($event->getPlayer()->getLevel()->getName(),$wlist))
	{
		$event->setCancelled(true);
		$event->getPlayer()->sendPopup("§6[DWorldProtect]您所在的世界被保护了!");
	}
}
}