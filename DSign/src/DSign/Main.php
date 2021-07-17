<?php
namespace DSign;

//2019年8月30日 22:36

use pocketmine\plugin\PluginBase; //必需
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\utils\MainLogger;
use pocketmine\Player; //有关玩家
use pocketmine\item\Item;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\utils\config;
use pocketmine\Level\Level;
use pocketmine\tile\Tile;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\Server;
use pocketmine\entity\Entity;

use onebone\economyapi\EconomyAPI;

class Main extends PluginBase implements Listener
{
public function onEnable()
{
	$this->getServer()->getPluginManager()->registerEvents($this,$this);
	$this->getLogger()->info("[DSign]DSign插件运行成功");
	$this->getLogger()->info("[DSign]作者Glorydark Q1083215364");
	@mkdir($this->getDataFolder());
	$this->cfg=new Config($this->getDataFolder()."signcommand.yml",Config::YAML,array());
}
public function onCommand(CommandSender $sender, Command $cmd, $label, array $arg)
{
	switch($arg[0]){
	case "add":
	$this->cfg->reload();
	$cfg = $this->cfg->getAll();
	//sign add <name> <costmoney>
	if(isset($arg[1]) && isset($arg[2])){
		if(!$sender->isOp() || $sender instanceof Player){
			if(!in_array($arg[1],$cfg)){
				$this->cfg->set($arg[1],array(
				"messages" => array(),
				"costmoney" => $arg[2],
				"commands" => array(),
				"creater" => $sender->getName(),
				"createtime" => time(),
				));
				$this->cfg->save();
				$sender->sendMessage("§6木牌第一行为 {$arg[1]} 时触发指令");
				$sender->sendMessage("§6指令请前往配置文件修改!");
			}else{
				$sender->sendMessage("§6您已经添加过了!");
			}
			return true;
		}else{
			$sender->sendMessage("§c[警告]您不是管理，无法操作!");
			return true;
		}
	}
	return false;
	break;
	case "del":
	$this->cfg->reload();
	$cfg = $this->cfg->getAll();
	if(isset($arg[1])){
		if(in_array($arg[1],$cfg)){
			unset($cfg[$arg[1]]);
			$this->cfg->setAll($cfg);
			$this->cfg->save();
		}else{
			$sender->sendMessage("§6木牌数据不存在");
		}
		return true;
	}
	return false;
	break;
	default:
	unset($sender,$cmd,$label,$arg);
	return false;
	break;
	}
}
public function onTouch(PlayerInteractEvent $event){
	$this->cfg->reload();
	$p = $event->getPlayer();
	$block = $event->getBlock();
	$sign = $p->getLevel()->getTile($block);
	if($block->getId() == 323 || $block->getId() == 63 || $block->getId() == 68){
		$sign = $sign->getText();
		$config = $this->cfg->get($sign[0]);
		$commands = $config["commands"];
		$messages = $config["messages"];
		if(count($commands) > 0){
			foreach ($commands as $cmds){
				$this->getServer()->dispatchCommand($p,$cmds);
			}
			foreach ($messages as $msgs){
				$p->sendMessage($msgs);
			}
		}
	}
}
}