<?php
namespace DCDK;

use pocketmine\plugin\PluginBase; //必需
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\utils\MainLogger;
use pocketmine\Player; //有关玩家
use pocketmine\utils\config;
use pocketmine\server\Server;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

error_reporting(0);

class Main extends PluginBase implements Listener
{
public function onEnable()
{
	@mkdir($this->getDataFolder());
	$this->getServer()->getPluginManager()->registerEvents($this,$this);
	$this->getLogger()->info("[DCDK]DCDK插件运行成功");
	$this->getLogger()->info("[DCDK]作者Glorydark Q1083215364");
	$this->cdkcfg=new Config($this->getDataFolder()."cdkcfg.yml",Config::YAML,array());
}

public function onCommand(CommandSender $sender, Command $cmd, $label, array $arg)
{
	switch($cmd->getName()){
		case "生成CDK":
		if($sender->isOp() || !$sender instanceof Player){
			$arr = [
				"items" => array(),
				"commands" => array(),
				"messages" => array(),
			];
			$str_time = $this->dec62($this->msectime());
			$code = $this->rand_char().$str_time; 
			$this->cdkcfg->set($code,$arr);
			$this->cdkcfg->save();
			$sender->sendMessage(TextFormat::GREEN."生成CDK成功请前往后台修改配置文件!");
			return true;
  		}else{
			$sender->sendMessage(TextFormat::RED."您没有指令的权限!");
		}
    	break;
    	case "兑换CDK":
		if(!isset($arg[0]))
		{
			return false;
		}else{
			$this->cdkcfg->reload();
			if(!$this->cdkcfg->exists($arg[0])){
    		$sender->sendMessage(TextFormat::RED."不存在此兑换码!");
    		return true;
		    }else{
				$cfg = $this->cdkcfg->get($arg[0]);
				$items = $cfg["items"];
				$cmds = $cfg["commands"];
				$messages = $cfg["messages"];
				if(count($cmds) != 0){
					foreach($cmds as $command){
						$c = str_replace("%p", $sender->getName(), $command);
						$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $c);
					}
				}
				if(count($items) != 0){
					foreach($items as $itemsname){
						$c = "give ".$sender->getName()." ".$itemsname;
						$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $c);
					}
				}
				if(count($messages) != 0){
					foreach($messages as $msg){
						$sender->sendMessage("兑换成功!");
					}
				}else{
					$sender->sendMessage("兑换成功!");
				}
				$this->cdkcfg->remove($arg[0]);
				$this->cdkcfg->save();
				return true;
		    }
		}
		break;
    }
}

    public function msectime(){
        $arr = explode(' ', microtime());  
        $tmp1 = $arr[0];  
        $tmp2 = $arr[1];
        return (float)sprintf('%.0f', (floatval($tmp1) + floatval($tmp2)) * 1000);  
    }

    public function dec62($dec){
        $base = 62;  
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';  
        $ret = '';  
       	for($t = floor(log10($dec) / log10($base)); $t >= 0; $t--){  
            $a = floor($dec / pow($base, $t));  
           	$ret .= substr($chars, $a, 1);  
           	$dec -= $a * pow($base, $t);  
        }  
        return $ret;  
    }
 
    public function rand_char(){  
        $base = 62;  
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';  
        return $chars[mt_rand(1, $base) - 1];  
    }
}