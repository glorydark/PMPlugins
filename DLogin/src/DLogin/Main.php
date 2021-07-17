<?php
namespace DLogin;

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
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent; //若玩家未登录将禁止移动
use pocketmine\event\player\PlayerQuitEvent; //判断玩家退出事件
use pocketmine\event\player\PlayerDropItemEvent; //若玩家未登录将禁止丢弃物品
use pocketmine\event\inventory\InventoryPickupItemEvent; //若玩家未登录将禁止捡东西
use pocketmine\event\block\BlockBreakEvent; //若玩家未登录将禁止破坏方块
use pocketmine\event\block\BlockPlaceEvent; //若玩家未登录将禁止放置方块

error_reporting(0);

class Main extends PluginBase implements Listener
{
public $log = array();

public function onEnable()
{
	$this->getServer()->getPluginManager()->registerEvents($this,$this);
	$this->getLogger()->info("[DLogin]DLogin运行成功");
	$this->getLogger()->info("[DLogin]作者Glorydark");
	@mkdir($this->getDataFolder());
	@mkdir($this->getDataFolder()."players/",0777,true);
}
public function PlayerJoinEvent(PlayerJoinEvent $event)
{
	$player = $event->getPlayer();
	$player->sendMessage("使用/login <密码>");
	$player->sendMessage("注册玩家使用/register <密码> <QQ号>");
	$this->log[$player->getName()] = false;
}
public function onCommand(CommandSender $sender, Command $cmd, $label, array $arg)
{
	switch($cmd->getName()){
	case "register":
	if (!($sender instanceof Player)){
	$sender->sendMessage("你不应该在后台测试这个命令!");
	return true;
	}
	if ($sender instanceof Player){ //如果输入指令的是玩家
	if(!isset($arg[0]))
	{
		unset($sender,$cmd,$label,$arg);
		return false;
	}
	if(isset($arg[0]))
	{
		if(!isset($arg[1]))
		{
			unset($sender,$cmd,$label,$arg);
			return false;
		}
	}
	if(isset($arg[0]) && isset($arg[1]))
	{
		if($this->log[$sender->getName()] == false)
		{
		$player = $sender->getName();
		$this->cfg=new Config($this->getDataFolder()."/players/".$player.".yml",Config::YAML,array());
		$this->cfg->reload();
		if(!$this->cfg->exists("password") || !$this->cfg->exists("qq"))
		{
			$this->cfg->set("password",$arg[0]);
			$this->cfg->set("qq",$arg[1]);
			$this->cfg->save();
			$this->log[$player] = true;
			$sender->sendMessage("注册成功，下次登录使用 /login <密码> 登录!");
			return true;
		}else{
			$sender->sendMessage("您已经注册过了!");
			return true;
		}
	    }else{
			$sender->sendMessage("您已经登陆了！");
		}
	}
	}
	break;
	case "login":
	$player = $sender->getName();
	$this->cfg=new Config($this->getDataFolder()."/players/".$player.".yml",Config::YAML,array());
	$this->cfg->reload();
	$this->truepassword = $this->cfg->get("password");
	if ($sender instanceof Player){ //如果输入指令的是玩家
	if($this->log[$player] == false) //如果玩家状态为未登录
	{
	if(isset($arg[0])) //如果玩家输入了密码且密码不为空
	{
		if($arg[0] == $this->truepassword)  //如果玩家输入的密码正确
		{
			$sender->sendMessage("登陆成功!");
			$this->log[$player] = true;
			return true;
		}
		else{ //如果玩家输入的密码不正确
			$sender->sendMessage("您的密码错误，请重新登录!");
			return true;
		}
	}else{ //如果玩家输入的密码为空
		$sender->sendMessage("密码不能为空!");
		return true;
	    }
	}else{
		$sender->sendMessage("您已经登录了！");
		return true;
	}
	}
	break;
	default:
	unset($sender,$cmd,$label,$arg);
	return false;
	break;
	}
}

public function PlayerMoveEvent(PlayerMoveEvent $event)
{
	$player = $event->getPlayer();
	if($this->log[$player->getName()] == true)
	{
		return true;
	}else{
		$event->setCancelled(true);
	}
}

public function InventoryPickupItemEvent(InventoryPickupItemEvent $event)
{
	$player = $event->getInventory()->getHolder();
	if($this->log[$player->getName()] == true)
	{
		return true;
	}else{
		$event->setCancelled(true);
	}
}

public function PlayerDropItemEvent(PlayerDropItemEvent $event)
{
	$player = $event->getPlayer();
	if($this->log[$player->getName()] == true)
	{
		return true;
	}else{
		$event->setCancelled(true);
	}
}

public function BlockBreakEvent(BlockBreakEvent $event)
{
	$player = $event->getPlayer();
	if($this->log[$player->getName()] == true)
	{
		return true;
	}else{
		$event->setCancelled(true);
		$player->sendMessage("请不要在登录/注册过程中进行无关的行为！");
	}
}

public function BlockPlaceEvent(BlockPlaceEvent $event)
{
	$player = $event->getPlayer();
	if($this->log[$player->getName()] == true)
	{
		return true;
	}else{
		$event->setCancelled(true);
		$player->sendMessage("请不要在登录/注册过程中进行无关的行为！");
	}
}

public function PlayerQuitEvent(PlayerQuitEvent $event)
{
	$player = $event->getPlayer();
	if($this->log[$player->getName()] == true)
	{
		$this->log[$player->getName()] = false;
	}else{
		return true;
	}
}

public function PlayerCommandPreprocessEvent(PlayerCommandPreprocessEvent $event){
	$player = $event->getPlayer();
    if($this->log[$player->getName()] == true){
		return true;
	}else{
		if(substr($event->getMessage(),0,6) !== "/login" && substr($event->getMessage(),0,9) !== "/register"){
			$event->setCancelled(true);
			$event->getPlayer()->sendMessage("请不要在登录/注册过程中使用无关的指令！");
		}
	}
}
}