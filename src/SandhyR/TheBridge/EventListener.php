<?php

namespace SandhyR\TheBridge;

use jackmd\scorefactory\ScoreFactory;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use SandhyR\TheBridge\game\Game;
use SandhyR\TheBridge\utils\Utils;

class EventListener implements Listener{

    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        if(($game = TheBridge::getInstance()->getPlayerGame($player)) instanceof Game){
            $game->broadcastCustomMessage(TextFormat::RED . $player->getName() . " disconnected!");
            $game->removePlayer($player);
        }
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function onBreak(BlockBreakEvent $event){
        $player = $event->getPlayer();
        if(($game = TheBridge::getInstance()->getPlayerGame($player)) instanceof Game){
            if(isset($game->placedblock[Utils::vectorToString($event->getBlock()->getPosition()->asVector3())])){
                unset($game->placedblock[Utils::vectorToString($event->getBlock()->getPosition()->asVector3())]);
            } else {
                $event->cancel();
            }
        }
    }
    /**
     * @param PlayerExhaustEvent $event
     */
    public function onExhaust(PlayerExhaustEvent $event){
        $player = $event->getPlayer();
        if(($game = TheBridge::getInstance()->getPlayerGame($player)) instanceof Game){
            $event->cancel();
            $event->getPlayer()->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
        }
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onDamage(EntityDamageEvent $event){
        $player = $event->getEntity();
        if($player instanceof Player) {
            if (($game = TheBridge::getInstance()->getPlayerGame($player)) instanceof Game) {
                if($game->phase == "LOBBY" || $game->phase == "COUNTDOWN" || $game->phase == "RESTARTING"){
                    $event->cancel();
                    return;
                }
                if($event->getCause() == $event::CAUSE_FALL){
                    $event->cancel();
                    return;
                }
                if($event instanceof EntityDamageByEntityEvent) {
                    if (($damager = $event->getDamager()) instanceof Player && $game->isInGame($damager))
                        $game->playerinfo[strtolower($player->getName())]["damager"] = $damager;
                        TheBridge::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(
                            function() use($player, $game){
                                $game->playerinfo[strtolower($player->getName())]["damager"] = null;
                            }
                        ), 20 * 5);
                    if ($event->getFinalDamage() >= $player->getHealth()) {
                        $game->handleDeath($player, $event);
                        $event->cancel();
                    }
                }
            }
        }
    }

    public function onChat(PlayerChatEvent $event){
        $player = $event->getPlayer();
        if(($game = TheBridge::getInstance()->getPlayerGame($player)) instanceof Game) {
            $game->broadcastMessage($player, $event->getMessage());
            $event->cancel();
        }
    }

    public function onUse(PlayerItemUseEvent $event){
        $player = $event->getPlayer();
        if(($game = TheBridge::getInstance()->getPlayerGame($player)) instanceof Game) {
            if($event->getItem()->getId() == ItemIds::BED){
                $game->removePlayer($player);
                ScoreFactory::removeObjective($player);
                $player->teleport($game->getHub());
                $player->getInventory()->clearAll();
            }
        }
    }
}
