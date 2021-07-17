<?php
namespace DBonus;

use pocketmine\plugin\PluginBase; //必需
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\config;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\utils\MainLogger;
use pocketmine\Player; //有关玩家
use pocketmine\Server;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

use onebone\economyapi\EconomyAPI;

error_reporting(0);

if(!function_exists("randomget")){
	function randomget($total,$num,$min){
		$rl = array();
		$i = 0;
		for ($i=1;$i<$num;$i++)
		{
			$safe_total=($total-($num-$i)*$min)/($num-$i);//随机安全上限  
			$money=mt_rand($min*100,$safe_total*100)/100;  
			$total=$total-$money;
			array_push($rl,$money);
		}
		return $rl;
	}
}

class Main extends PluginBase implements Listener
{

public $eapi;

public function onEnable()
{
	date_default_timezone_set('PRC');
	$this->getServer()->getPluginManager()->registerEvents($this,$this);
	$this->getLogger()->info("[DBonus]DBonus运行成功");
	$this->getLogger()->info("[DBonus]作者Glorydark");
	$this->eapi = EconomyAPI::getInstance();
	@mkdir($this->getDataFolder());
	@mkdir($this->getDataFolder()."config/",0777,true);
}

public function onCommand(CommandSender $sender, Command $cmd, $label, array $arg)
{
	switch($cmd){
	case "发金币红包": //<红包类型> <红包金额> <红包个数>
	if(isset($arg[0]) && isset($arg[1]) && isset($arg[2]))
	{
		if($sender instanceof player){
			if(EconomyAPI::getInstance()->reduceMoney($sender,(int)arg[1]) !== 1)
			{
				$sender->sendMessage(TextFormat::RED."金币不足!");
			}else{
				if((int)$arg[1] >= 10000 && (int)$arg[1]/(int)$arg[2] >= 100){
					$sender->sendMessage(TextFormat::RED."发送成功!");
					$addlist = array_push($list,$time);
					$time = time();
					$bonusrec = new Config($this->getDataFolder()."config/".$time.".yml",Config::YAML,array(
					"player" => array(),
				    "cfg" => array("红包类型" => "1","红包金额" => "0","红包个数" => "0","红包发放者" => $sender->getName()),
				    "random" => array(),
				));
					$bonuscfg = $bonusrec->get("cfg");
					$bonusrec->reload();
					$bonuscfg["红包类型"] = $arg[0];
					$bonuscfg["红包金额"] = $arg[1];
					$bonuscfg["红包个数"] = $arg[2];
					$bonuscfg["红包发放者"] = $sender->getName();
					$bonusrec->set("cfg",$bonuscfg);
					$bonusrec->set("random",randomget($arg[1],$arg[2],1));
					$bonusrec->save();
					Server::getInstance()->broadcastMessage(TextFormat::RED."玩家".$sender->getName()."发放了".$arg[1]."金币的红包(含".$arg[2]."份，输入 /抢金币红包 ".(string)$time."领取!");
				}else{
			    $sender->sendMessage(TextFormat::RED."金币红包金额至少为10000金币，每一份至少为100金币!");
				}
			}
		}else{
			if((int)$arg[1] >= 10000 && (int)$arg[1]/(int)$arg[2] >= 100){
				$sender->sendMessage(TextFormat::RED."发送成功!");
				$time = time();
				$bonusrec = new Config($this->getDataFolder()."config/".$time.".yml",Config::YAML,array(
				"player" => array(),
				"cfg" => array("红包类型" => "1","红包金额" => "0","红包个数" => "0","红包发放者" => $sender->getName()),
				"random" => array(),
				));
				$bonuscfg = $bonusrec->get("cfg");
				$bonusrec->reload();
				$bonuscfg["红包类型"] = $arg[0];
				$bonuscfg["红包金额"] = $arg[1];
				$bonuscfg["红包个数"] = $arg[2];
				$bonuscfg["红包发放者"] = $sender->getName();
				$bonusrec->set("cfg",$bonuscfg);
				$bonusrec->set("random",randomget($arg[1],$arg[2],1));
				$bonusrec->save();
				Server::getInstance()->broadcastMessage(TextFormat::RED."GM发放了".$arg[1]."金币的红包(含".$arg[2]."份，输入 /抢金币红包 ".(string)$time."领取!");
			}else{
			    $sender->sendMessage(TextFormat::RED."金币红包金额至少为10000金币，每一份至少为100金币!");
			}
		}
		return true;
		unset($bonusrec,$time);
	}else{
		$sender->sendMessage("指令格式错误!");
		return true;
	}
	break;
	case "抢金币红包":
	if(isset($arg[0])){
		//if($sender instanceof Player)
		//{
			$bonusrec = new Config($this->getDataFolder()."config/".$arg[0].".yml",Config::YAML,array());
			$bonuscfg = $bonusrec->get("cfg");
			$bonusmoneyall = $bonuscfg["红包金额"];
			$bonusmoneyamount = 0;
			$bonusmoneyamount = $bonuscfg["红包个数"];
			$bonussender = $bonuscfg["红包发放者"];
			$bonustype = $bonuscfg["红包类型"];
			
			$alreadyget = $bonusrec->player;
			if(!in_array($sender->getName(),$alreadyget)){
				if($bonustype == 1){
					if(count($alreadyget) + 1 <= (int)$bonusmoneyamount){
						$getmoney = $bonusmoneyall/$bonusmoneyamount;
						$sender->sendMessage("恭喜您，领取".$bonussender."的红包成功并获得金币*".(string)$getmoney);
						EconomyAPI::getInstance()->addMoney($sender,(int)$getmoney);
						Server::getInstance()->broadcastMessage(TextFormat::RED."玩家".$sender->getName()."领取".$bonussender."的普通红包获得了金币*".(string)$getmoney);
						$bonusrec->set("player",array_push($alreadyget,$sender->getName()));
						$bonusrec->save();
					}else{
						$sender->sendMessage("对不起，红包已领完!");
					}
				    }
					
					if($bonustype == 2){
					if(count($alreadyget) + 1 <= (int)$bonusmoneyamount){
						$randomcfg = $bonusrec->get("random");
						$getmoney = $randomcfg[count($alreadyget)];
						$sender->sendMessage("恭喜您，领取".$bonussender."的红包成功并获得金币*".(string)$getmoney);
						EconomyAPI::getInstance()->addMoney($sender,(int)$getmoney);
						Server::getInstance()->broadcastMessage(TextFormat::RED."玩家".$sender->getName()."领取".$bonussender."的随机红包获得了金币*".(string)$getmoney);
						$bonusrec->set("player",array_push($alreadyget,$sender->getName()));
						$bonusrec->save();
					}else{
						$sender->sendMessage("对不起，红包已领完!");
					}
				}
				return true;
			}else{
				$sender->sendMessage("您已经领取过了!");
				return false;
			}
		//}else{
			//$sender->sendMessage("请在游戏内使用指令!");
		//}
	}else{
		$sender->sendMessage("§6请输入红包id!");
	}
	unset($bonusrec,$bonuscfg,$bonusmoneyall,$bonusmoneyamount,$bonussender,$getmoney,$alreadyget);
	break;
    }
}
}