<?php
namespace DSignReward;

use pocketmine\plugin\PluginBase; //必需
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\utils\MainLogger;
use pocketmine\Player; //有关玩家
use pocketmine\utils\config;
use pocketmine\command\ConsoleCommandSender;

use onebone\economyapi\EconomyAPI;

error_reporting(0);

class Main extends PluginBase implements Listener
{
public function onEnable()
{
	date_default_timezone_set('PRC');
	@mkdir($this->getDataFolder());
	$this->getServer()->getPluginManager()->registerEvents($this,$this);
	$this->getLogger()->info("[DSignReward]DSignBox插件运行成功");
	$this->getLogger()->info("[DSignReward]作者Glorydark Q1083215364");
	$this->qdcfg=new Config($this->getDataFolder()."qd.yml",Config::YAML,array(
	"dailysigncommand" => array(
	"1" => array(),
	"2" => array(),
	"3" => array(),
	"4" => array(),
	"5" => array(),
	"6" => array(),
	"7" => array(),
	"8" => array(),
	"9" => array(),
	"10" => array(),
	"11" => array(),
	"12" => array(),
	"13" => array(),
	"14" => array(),
	"15" => array(),
	"16" => array(),
	"17" => array(),
	"18" => array(),
	"19" => array(),
	"20" => array(),
	"21" => array(),
	"22" => array(),
	"23" => array(),
	"24" => array(),
	"25" => array(),
	"26" => array(),
	"27" => array(),
	"28" => array(),
	"29" => array(),
	"30" => array(),
	"31" => array(),
	),
	"dailysignmessage" => array(
	"1" => array(),
	"2" => array(),
	"3" => array(),
	"4" => array(),
	"5" => array(),
	"6" => array(),
	"7" => array(),
	"8" => array(),
	"9" => array(),
	"10" => array(),
	"11" => array(),
	"12" => array(),
	"13" => array(),
	"14" => array(),
	"15" => array(),
	"16" => array(),
	"17" => array(),
	"18" => array(),
	"19" => array(),
	"20" => array(),
	"21" => array(),
	"22" => array(),
	"23" => array(),
	"24" => array(),
	"25" => array(),
	"26" => array(),
	"27" => array(),
	"28" => array(),
	"29" => array(),
	"30" => array(),
	"31" => array(),
	),
	"cumulativesigncommand" => array(),
	"cumulativesignmessage" => array(),
	));
	$this->xscfg=new Config($this->getDataFolder()."xs.yml",Config::YAML,array(
	"cmd" => array(),
	"message" => array(),
	));
	$this->icdkcfg=new Config($this->getDataFolder()."icdk.yml",Config::YAML,array(
	"qd" => array("bs","el","elg","baidu","taptap"),
	"bs_cdk" => "bs",
	"bs_cmd" => array(),
	"bs_message" => array(),
	"el_cdk" => "el",
	"el_cmd" => array(),
	"el_message" => array(),
	"elg_cdk" => "elg",
	"elg_cmd" => array(),
	"elg_message" => array(),
	"baidu_cdk" => "baidu",
	"baidu_cmd" => array(),
	"baidu_message" => array(),
	"taptap_cdk" => "taptap",
	"taptap_cmd" => array(),
	"taptap_message" => array(),
	));
	$this->qdusercfg=new Config($this->getDataFolder()."qd_timerecord.yml",Config::YAML,array());
	$this->qdusercfg1=new Config($this->getDataFolder()."qd_cumulativesignrecord.yml",Config::YAML,array());
	$this->xsusercfg=new Config($this->getDataFolder()."xs_record.yml",Config::YAML,array());
	$this->icdkusercfg=new Config($this->getDataFolder()."icdk_record.yml",Config::YAML,array());
}

public function onCommand(CommandSender $sender, Command $cmd, $label, array $arg)
{
	switch($cmd->getName()){
	case "qd":
	if($sender instanceof Player){
	$this->qdusercfg->reload();
	$this->qdusercfg1->reload();
	$this->qdcfg->reload();
	$date = date('Y-m-d');
	$date1 = $date." "."00:00:00";
	$dateget1 = strtotime($date1);
	$playertime = $this->qdusercfg->get($sender->getName());
	$cumulativesigndays = $this->qdusercfg1->get($sender->getName());
	$cumulativesigncommand = $this->qdcfg->get("cumulativesigncommand");
	$cumulativesignmessage = $this->qdcfg->get("cumulativesignmessage");
	if(!empty($playertime)){
	if($playertime <= $dateget1){
		$day = date('d');
		$cmd1 = $this->qdcfg->get("dailysigncommand");
		$cmd = $cmd1[$day];
		$msg1 = $this->qdcfg->get("dailysignmessage");
		$msg = $msg1[$day];
		$sender->sendMessage("§6[DSignReward]签到成功!");
		$this->qdusercfg->set($sender->getName(),strtotime(date('Y-m-d H:i:s')));
		$this->qdusercfg->save();
		$this->qdusercfg1->set($sender->getName(),strval($cumulativesigndays + 1));
		$this->qdusercfg1->save();
		if(!empty($cmd1)){
		foreach($cmd as $command){
			$c = str_replace("%p", $sender->getName(), $command);
			$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $c);
		}}
		if(!empty($msg)){
		foreach($msg as $message){
			$sender->sendMessage("§6{$message}");
		}
	    }
		$this->qdusercfg1->reload();
		$cumulativesigndays = $this->qdusercfg1->get($sender->getName());
		if(!empty($cumulativesigncommand[$cumulativesigndays + 1])){
			foreach($cumulativesigncommand[$cumulativesigndays + 1] as $command)
			{
			$c = str_replace("%p", $sender->getName(), $command);
			$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $c);
			}
		}
		if(!empty($cumulativesignmessage[$cumulativesigndays + 1])){
			foreach($cumulativesignmessage[$cumulativesigndays + 1] as $msg)
			{
				$sender->sendMessage("§6{$msg}");
			}
		}
		}else{
		$sender->sendMessage("§6您今日已经签到过了!");
		}
		}else{
		$playertime = date('Y-m-d H:i:s');
		$this->qdusercfg->set($sender->getName(),strtotime($playertime));
		$this->qdusercfg1->set($sender->getName(),1);
		$this->qdusercfg->save();
		$this->qdusercfg1->save();
		$sender->sendMessage("§6签到成功!");
		$day = date('d');
		$cmd1 = $this->qdcfg->get("dailysigncommand");
		$cmd = $cmd1[$day];
		$msg1 = $this->qdcfg->get("dailysignmessage");
		$msg = $msg1[$day];
		foreach($cmd as $command){
			$c = str_replace("%p", $sender->getName(), $command);
			$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $c);
		}
		if(!empty($msg)){
		foreach($msg as $message){
			$sender->sendMessage("§6{$message}");
		}
		}
		if(!empty($cumulativesigncommand[1])){
			foreach($cumulativesigncommand[1] as $command)
			{
			$c = str_replace("%p", $sender->getName(), $command);
			$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $c);
			}
		}
		if(!empty($cumulativesignmessage[1])){
			foreach($cumulativesignmessage[1] as $msg)
			{
				$sender->sendMessage("§6{$msg}");
			}
		}
	    }
	}
	else{
		$sender->sendMessage("§6请不要在控制台调试本命令!");
	}
	return true;
	break;
	//签到系统
	case "xs":
	if($sender instanceof Player){
	$this->xscfg->reload();
	$this->xsusercfg->reload();
	if(!$this->xsusercfg->exists($sender->getName())){
		$date = date('Y-m-d H:i:s');
		$cmd = $this->xscfg->get("cmd");
		$msg = $this->xscfg->get("message");
		$this->xsusercfg->set($sender->getName(),strtotime($date));
		$this->xsusercfg->save();
		if(!empty($cmd)){
		foreach($cmd as $command){
			$c = str_replace("%p", $sender->getName(), $command);
			$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $c);
		}
		}
		if(!empty($msg)){
		foreach($msg as $message){
			$sender->sendMessage("§6{$message}");
		}
		$sender->sendMessage("§6领取新手礼包成功!");
		}
		return true;
	}else{
		$sender->sendMessage("§6您已经领取过了!");
		return true;
	}
	}else{
		$sender->sendMessage("§6请不要在控制台调试本命令!");
	}
	return true;
	break;
	//新手礼包
	case "icdk":
	if(!isset($arg[0]) && !isset($arg[1])) return false;
	if($sender instanceof Player){
	$this->icdkcfg->reload();
	$this->icdkusercfg->reload();
	$qudao = $this->icdkcfg->get("qd");
	if(in_array($arg[0],$qudao)){
	if($this->icdkcfg->get($arg[0]."_cdk") == $arg[1]){
	if(!$this->icdkusercfg->exists($sender->getName())){
		$pqd = $arg[0];
		$cmd = $this->icdkcfg->get($arg[0]."_cmd");
		$msg = $this->icdkcfg->get($arg[0]."_message");
		$this->icdkusercfg->set($sender->getName(),$pqd);
		$this->icdkusercfg->save();
		if(!empty($cmd)){
		foreach($cmd as $command){
			$c = str_replace("%p", $sender->getName(), $command);
			$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $c);
		}
		}
		if(!empty($msg)){
		foreach($msg as $message){
			$sender->sendMessage("§6{$message}");
		}
		$sender->sendMessage("§6领取 {$arg[0]} 渠道邀请礼包成功!");
		}
	}else{
		$sender->sendMessage("§6您已经领取过了!");
	}
	}else{
		$sender->sendMessage("§6您输入的该渠道邀请码错误，请重新输入!");
	}
	}else{
		$sender->sendMessage("§6您输入的渠道/渠道邀请码有误，请重新输入!");
	}
	}else{
		$sender->sendMessage("§6请不要在控制台调试本命令!");
	}
	return true;
	break;
	//渠道邀请码
	default:
	unset($sender,$cmd,$label,$arg);
	return false;
	break;
	}
}
}