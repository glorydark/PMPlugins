<?php
namespace DFix;

use pocketmine\plugin\PluginBase; //必需
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\utils\MainLogger;
use pocketmine\Player; //有关玩家
use pocketmine\item\Item;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\utils\config;
use pocketmine\event\player\PlayerInteractEvent;

use onebone\economyapi\EconomyAPI;

error_reporting(0);

class Main extends PluginBase implements Listener
{
public function onEnable()
{
	$this->getServer()->getPluginManager()->registerEvents($this,$this);
	$this->getLogger()->info("[DFix]DFix修复装备插件运行成功");
	$this->getLogger()->info("[DFix]作者Glorydark Q1083215364");
	@mkdir($this->getDataFolder());
	$this->cfg=new Config($this->getDataFolder()."config.yml",Config::YAML,array());
	$this->cfglist=new Config($this->getDataFolder()."canbefixedlist.yml",Config::YAML,array(
	"允许被修复的物品列表" => array(256,257,258,259,261,267,268,269,270,271,272,273,274,275,276,277,278,279,283,284,285,286,290,291,292,293,294,298,299,300,301,302,303,304,305,306,307,308,309,310,311,312,313,314,315,316,317,346,444),
	));
	if(!$this->cfg->exists("costmoney")){
		$this->cfg->set("costmoney","1000");
		$this->cfg->save();
	}
}
public function onCommand(CommandSender $sender, Command $cmd, $label, array $arg)
{
	switch($cmd->getName()){
	case "DFix":
	$sender->sendMessage("§6DFix-v1.0.3的更新增加了木牌显示功能");
	$sender->sendMessage("§6您只需要把木牌的第一行写为修复武器即可");
	$sender->sendMessage("§6修复了无法修复盔甲的bug以及未判断的bug");
	$sender->sendMessage("§6输入/fix或使用设置木牌即可修复盔甲&武器");
	$sender->sendMessage("§6所花费的金币可以在后台自定义");
	return true;
	break;
	case "fix":
	$canfixedid = $this->cfglist->get("允许被修复的物品列表");
	$index = $sender->getInventory()->getHeldItemIndex();
	$item = $sender->getInventory()->getItem($index);
	$id = $item->getId();
	$cost = intval($this->cfg->get("costmoney"));
	if($sender instanceof player){
	if(in_array($id,$canfixedid))
	{
	if(EconomyAPI::getInstance()->MyMoney($sender) > $cost){
	$item->setDamage(0);
	$sender->getInventory()->setItemInHand($item);
	EconomyAPI::getInstance()->reduceMoney($sender,$cost);
	$sender->sendMessage("§6[DFix]已将您手上的物品修复,本次花费 {$cost} 金币!");
	}else{
		$sender->sendMessage("§6[DFix]对不起，您的金币不足!");
	}
	}else{
		$sender->sendMessage("§6[DFix]对不起，您手上的物品无法被修复!");
	}
	}else{
		$sender->sendMessage("§6[DFix]请不要在后台调试命令!");
	}
	return true;
	break;
	default:
	unset($sender,$cmd,$label,$arg);
	return false;
	break;
	}
}
public function onTouch(PlayerInteractEvent $event){
	$p = $event->getPlayer();
	$block = $event->getBlock();
	$sign = $p->getLevel()->getTile($block);
	$canfixedid = $this->cfglist->get("允许被修复的物品列表");
	if($block->getId() == 323 || $block->getId() == 63 || $block->getId() == 68){
		$sign = $sign->getText();
		if($sign[0] == "§a修复装备"){
			$cm = intval($this->cfg->get("costmoney"));
			$pm = EconomyAPI::getInstance()->MyMoney($p);
			$item = $event->getItem();
			$id = $item->getId();
			if(in_array($id,$canfixedid))
			{
			if($pm > $cm){
					$item->setDamage(0);
					$p->sendMessage("§6[DFix]已经将您手上的物品修复!");
					EconomyAPI::getInstance()->reduceMoney($event->getName(),$cm);
			}else{
				$p->sendMessage("§6[DFix]您的金币不足,本次修复需要金币 {cm} 个!");
			}
			}else{
				$p->sendMessage("§6[DFix]您手上的物品无法被修复!");
			}
		}	
		}
	}
}