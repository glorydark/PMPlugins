<?php
namespace DEventEditor;

use pocketmine\plugin\PluginBase; //必需
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\config;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\utils\MainLogger;
use pocketmine\Player; //有关玩家
use pocketmine\inventory\Inventory; //引用inventory有关的命令
use pocketmine\inventory\InventoryHolder; //引用inventory有关的命令

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
	@mkdir($this->getDataFolder()."event/",0777,true);
	@mkdir($this->getDataFolder()."players/",0777,true);
	$this->cfg1=new Config($this->getDataFolder()."/list.yml",Config::YAML,array(
	"list"=>array()
	));
}

public function onCommand(CommandSender $sender, Command $cmd, $label, array $arg)
{
	if(!isset($arg[0])){unset($sender,$cmd,$label,$arg);return false;};
	switch($arg[0]){
	case "help":
	$sender->sendMessage("DEventEditor使用指令列表");
	$sender->sendMessage("/event help 查看帮助");
	$sender->sendMessage("/event create <id> 创建一个id为id的活动");
    $sender->sendMessage("/event list 查看目前所有活动的id");
	$sender->sendMessage("/event participate <id> 参与id为id的活动");
	return true;
	break;
	case "create":
	if(isset($arg[1]))
	{
		$list = $this->cfg1->get("list");
		array_push($list, $arg[1]);
		$this->cfg1->set("list",$list);
		$this->cfg=new Config($this->getDataFolder()."/event/".$arg[1].".yml",Config::YAML,array());
		$this->cfg->set("id",$arg[1]);
		$this->cfg->set("type","0");
		$this->cfg->set("description","任务介绍");
		$this->cfg->set("give-item","1:0");
		$this->cfg->set("AllowOpen","false");
		$sender->sendMessage("创建成功，请前往后台修改文件!");
		$this->cfg2=new Config($this->getDataFolder()."/players/".$arg[1].".yml",Config::YAML,array(
		"list" => array()
		));
		$this->cfg->save();
		$this->cfg1->save();
		$this->cfg2->save();
		return true;
	}else{
		return false;
	}
	break;
	case "list":
	$this->cfg1->reload();
	$list .=implode(",",$this->cfg1->get("list"));
	$sender->sendMessage("目前存在的活动id列表如下:");
	$sender->sendMessage($list);
	return true;
	break;
	case "search":
    if(isset($arg[1]))
	{
		$this->cfg1->reload();
		$list = $this->cfg1->get("list");
		if(in_array($arg[1],$list))
		{
			$this->cfg=new Config($this->getDataFolder()."/event/".$arg[1].".yml",Config::YAML,array());
			$this->cfg2=new Config($this->getDataFolder()."/player/".$arg[1].".yml",Config::YAML,array(
			"list"=>array()
			));
			$this->cfg->reload();
			$this->cfg2->reload();
			$data1 = $this->cfg->get("id");
			$data2 = $this->cfg->get("type");
			$data3 = $this->cfg->get("description");
			$data4 = $this->cfg->get("give-item");
			$data5 = $this->cfg->get("AllowOpen");
			$list .=implode(",",$this->cfg2->get("list"));
			$sender->sendMessage("您所查询的id为 {$data1} 的活动信息如下:");
			$sender->sendMessage("获得类型: {$data2}");
			$sender->sendMessage("介绍: {$data3}");
			$sender->sendMessage("获得物品: {$data4}");
			$sender->sendMessage("是否开放: {$data5}");
			$sender->sendMessage("已完成玩家: {$list}");
			return true;
		}
	}
	else{
		return false;
	}
	break;
	case "participate":
	if ($sender instanceof Player){
	if(isset($arg[1]))
	{
		$id = $arg[1];
		$player = $sender->getName();
		$this->cfg=new Config($this->getDataFolder()."/event/".$arg[1].".yml",Config::YAML,array());
		$this->cfg2=new Config($this->getDataFolder()."/player/".$arg[1].".yml",Config::YAML,array(
		"list"=>array()
		));
		$this->cfg->reload();
		$this->cfg2->reload();
		$list = $this->cfg2->get("list");
		$item = $this->cfg->get("give-item");
		if(in_array($player,$list))
		{
			if($sender->getInventory()->canAddItem($item))
			{
				$sender->getInventory()->addItem($item);
				$list1 = array_push($player,$list);
				$this->cfg2->set("list",$list1);
				$this->cfg2->save();
			}
			else{
				$sender->sendMessage("您的背包可能满了，或者被禁止添加物品了！");
				return true;
			}
		}else{
			$sender->sendMessage("您参加这次活动过了！");
			return true;
		}
	}else{
		return false;
	}
	}else{
		$sender->sendMessage("请不要在后台测试这种指令！");
	}
	break;
}
}
}