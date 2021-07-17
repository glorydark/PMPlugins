<?php
namespace DCrapsLottery;

use pocketmine\plugin\PluginBase; //必需
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\config;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\utils\MainLogger;
use pocketmine\Player; //有关玩家
use pocketmine\OfflinePlayer;
use pocketmine\permission\PermissibleBase;
use pocketmine\command\ConsoleCommandSender;

error_reporting(0);

class Main extends PluginBase implements Listener
{
public function onEnable()
{
	$this->getServer()->getPluginManager()->registerEvents($this,$this);
	$this->getLogger()->info("[DCrapsLottery]DCrapsLottery运行成功");
	$this->getLogger()->info("[DCrapsLottery]作者Glorydark");
	@mkdir($this->getDataFolder());
	@mkdir($this->getDataFolder()."lottery/",0777,true);
	$this->listcfg=new Config($this->getDataFolder()."list.yml",Config::YAML,array(
	"list"=>array(),
	));
	$this->config=new Config($this->getDataFolder()."config.yml",Config::YAML,array(
	"allow-lottery"=>"false",
	));
}
public function onCommand(CommandSender $sender, Command $cmd, $label, array $arg)
{
	switch($arg[0]){
	case "give":
	$this->listcfg->reload();
	$list = $this->listcfg->get("list");
	$this->ticketscfg = new Config($this->getDataFolder().$arg[2]."_pc.yml",Config::YAML,array());//pc为playerconfig
	$this->ticketscfg->reload();
	if(isset($arg[1]) && isset($arg[2]) && isset($arg[3])){
		if(in_array($arg[2],$list)){
		if($this->ticketscfg->exists($sender->getName())){
		$amount = intval($this->ticketscfg->get($arg[1]));
		$this->ticketscfg->set($arg[1],strval($amount + intval($arg[3])));
		$this->ticketscfg->save();
		$sender->sendMessage("成功给予 {$arg[1]} 玩家 {$arg[3]} 个用于抽奖箱 {$arg[2]} 抽奖券!");
		return true;
		}else{
		$amount = $this->ticketscfg->get($arg[1]);
		$this->ticketscfg->set($arg[1],$arg[3]);
		$this->ticketscfg->save();
		$sender->sendMessage("成功给予 {$arg[1]} 玩家 {$arg[3]} 个用于抽奖箱 {$arg[2]} 抽奖券!");
		}
		}
		else{
		$sender->sendMessage("没有这个抽奖箱id");
		return true;
	}
	}else{
		return false;
	}
	break;
	case "start":
	$switch = $this->config->get("allow-lottery");
	if($switch != "true"){
		$sender->sendMessage("§6[DCrapsLottery]抽奖系统维护中!");
		return null;
	}else{
	if (!($sender instanceof Player)){
	$sender->sendMessage("§e[DCrapsLottery]你不应该在后台测试这个命令!");
	return true;
	}
	if ($sender instanceof Player){ //如果输入指令的是玩家
	$this->listcfg->reload();
	$list = $this->listcfg->get("list");
	if(isset($arg[1]) && in_array($arg[1],$list))
	{
		$name = $sender->getName();
		$this->stepscfg = new Config($this->getDataFolder().$arg[1]."_sa.yml",Config::YAML,array());//sa为stepamount
		$this->stepscfg->reload();
		$this->ticketscfg = new Config($this->getDataFolder().$arg[1]."_pc.yml",Config::YAML,array());//pc为playerconfig
		$this->ticketscfg->reload();
		$this->cfg = new Config($this->getDataFolder()."lottery/".$arg[1]."_command.yml",Config::YAML,array());
		$this->cfg1 = new Config($this->getDataFolder()."lottery/".$arg[1]."_message.yml",Config::YAML,array());//抽奖每次奖励的信息配置
		$ownamount = $this->ticketscfg->get($name);
		if(!$this->ticketscfg->exists($name))
		{
			$this->ticketscfg->set($name,"0");
			$this->ticketscfg->save();
			$this->stepscfg->set($name,"0");
			$this->stepscfg->save();
			$sender->sendMessage("§6[DCrapsLottery]您的抽奖券数量不够!");
		}else{
		if(intval($ownamount) > 0){
				$amount = $this->ticketscfg->get($name);
				$finalamount = (int)$amount - 1;
				$this->ticketscfg->set($name,strval($finalamount));
				$this->ticketscfg->save();//储存抽奖券数据
				$this->stepscfg = new Config($this->getDataFolder().$arg[1]."_sa.yml",Config::YAML,array());
				$step = intval($this->stepscfg->get($name));
				$lstep = rand(1, 6);
				$finalstep = $lstep + $step;
				$finalstep = strval($finalstep);
				$this->stepscfg->set($name,$finalstep);
				$pcmd = $this->cfg->get($finalstep);//获取对应步数的指令
				$this->stepscfg->save();//储存步数数据
				$c = str_replace("%p", $sender->getName(), $pcmd);
				if($pcmd !== "~" || !$this->cfg->exists("finalstep")){
				$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $c);
				}
				$msg = $this->cfg1->get(strval($finalstep));
				$sender->sendMessage("§6[DCrapsLottery]您本次抽奖抽取到的步数为 {$lstep} , 目前总步数为 {$finalstep}");
				if($msg !== "~" || !$this->cfg1->exists("finalstep")){
				$sender->sendMessage($msg);
				}
				$sender->sendMessage("§6[DCrapsLottery]本次消耗了您1张抽奖券!");
			}else{
				$sender->sendMessage("§6[DCrapsLottery]您好，您的抽奖券数量不够!");
			}
		}
		return true;
	}else{
		return false;
	}
	}
	}
	break;
	case "create":
	$this->listcfg->reload();
	$list = $this->listcfg->get("list");
	if(isset($arg[1]) && !in_array($arg[1],$list)){
		if($sender->isOp() == true){
		$this->stepscfg = new Config($this->getDataFolder().$arg[1]."_sa.yml",Config::YAML,array());//sa为stepamount
		$this->ticketscfg = new Config($this->getDataFolder().$arg[1]."_pc.yml",Config::YAML,array());//pc为playerconfig
		$this->cfg = new Config($this->getDataFolder()."lottery/".$arg[1]."_command.yml",Config::YAML,array());
		$this->cfg1 = new Config($this->getDataFolder()."lottery/".$arg[1]."_message.yml",Config::YAML,array());//抽奖每次奖励的信息配置
		$this->stepscfg->reload();
		$this->ticketscfg->reload();
		$this->cfg->reload();
		$this->cfg1->reload();
		if(!in_array($arg[1],$list)){
		array_push($list , $arg[1]);
		$this->listcfg->set("list",$list);
		$this->listcfg->save();//储存抽奖列表
		$this->ticketscfg = new Config($this->getDataFolder().$arg[1]."_pc.yml",Config::YAML,array());//新建玩家该抽奖的券数配置,pc为playerconfig
		for($i = 0 ; $i < 300 ; $i++){
		$time = $i + 1;
		$this->cfg = new Config($this->getDataFolder()."lottery/".$arg[1]."_command.yml",Config::YAML,array());//新建抽奖每次奖励的指令配置
		if(!$this->cfg->exists($time)){
			$this->cfg->set($time,"~");
			$this->cfg->save();
		}
		}
		for($a = 0 ; $a < 300 ; $a++){
		$time = $a + 1;
		$this->cfg1 = new Config($this->getDataFolder()."lottery/".$arg[1]."_message.yml",Config::YAML,array());//新建抽奖每次奖励的信息配置
		if(!$this->cfg1->exists($time)){
			$this->cfg1->set($time,"~");
			$this->cfg1->save();
		}
		}
		$sender->sendMessage("§5[DCrapsLottery]创建成功，请到后台配置文件处进行修改!");
		}else{
		$sender->sendMessage("§5[DCrapsLottery]您已经创建过这个id的抽奖了!");
		}
		return true;
	}else{
		$sender->sendMessage("§6您没有该权限");
	}
	}else{
		$sender->sendMessage("§6[DCrapsLottery]用法:/dcl create <id>");
		return false;
	}
	break;
	case "help":
	$sender->sendMessage("§6---------DCrapsLottery帮助列表---------");
	$sender->sendMessage("§6/dcl start <lotteryid>         抽奖一次");
	$sender->sendMessage("§6/dcl create <lotteryid>        创建抽奖");
	$sender->sendMessage("§6/dcl give <name> <id> <数量> 给予抽奖券");
	$sender->sendMessage("§6id指抽奖箱的id，name指玩家名字喔！     ");
	$sender->sendMessage("§6---------DCrapsLottery帮助列表---------");
	return true;
	break;
	default:
	unset($sender,$cmd,$label,$arg);
	return false;
	break;
	}
}
}