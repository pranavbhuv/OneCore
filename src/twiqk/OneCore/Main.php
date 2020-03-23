<?php

declare(strict_types=1);

namespace twiqk\OneCore;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;



class Main extends PluginBase implements Listener
{



    public function onEnable() : void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->notice("Enabled.");
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $dis = $this->getConfig()->get("delay");
        $dis1 = $this->getConfig()->get("mdelay:");
        $this->getScheduler()->scheduleRepeatingTask(new BroadcastTask($this->getServer(), $this->getConfig()), (int)$dis);
        $this->getScheduler()->scheduleRepeatingTask(new MotdTask($this->getServer(), $this->getConfig()), (int)$dis1);
        $this->getLogger()->notice("Config Loaded.");
        $this->getLogger()->notice("Tasks Enabled.");
    }

    public function onLogin(PlayerLoginEvent $event)
    {
        if ($this->getConfig()->get("truespawn") === "yes")
        {
            $event->getPlayer()->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
        }
    }


    public function onJoin(PlayerJoinEvent $e)
    {
        $person = $e->getPlayer();
        $person->sendMessage($this->getConfig()->get("welcomemsg"));
        $c1 = $this->getConfig()->get("customjoinmsg");
        if ($c1 === "yes")
        {
            $message = str_replace("{player}", $person->getDisplayName(), $this->getConfig()->get("joinmsg"));
            $e->setJoinMessage($message);
        }
        else
        {
            $e->setJoinMessage(null);
        }
    }

    public function onQuit(PlayerQuitEvent $e)
    {
        $person = $e->getPlayer();
        $c1 = $this->getConfig()->get("customquitmsg");
        if ($c1 === "yes")
        {
            $message = str_replace("{player}", $person->getDisplayName(), $this->getConfig()->get("quitmsg"));
            $e->setQuitMessage($message);
        }
        else
        {
            $e->setQuitMessage(null);
        }
    }

    public function onDeath(PlayerDeathEvent $ev): void
    {
        $player = $ev->getPlayer();
        $cause = $player->getLastDamageCause();
        if ($cause instanceof EntityDamageByEntityEvent)
        {
            $killer = $cause->getDamager();
            if ($killer instanceof Player)
            {
                if ($this->getConfig()->get("customdeath") === "yes")
                {
                    $message = str_replace(["{killer}", "{victim}"], [$killer->getDisplayName(), $player->getDisplayName()], $this->getConfig()->get("deathmsg"));
                    $ev->setDeathMessage($message);
                }
                else
                {
                    $ev->setDeathMessage(null);
                }

            }
        }
    }


    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName())
        {
            case "gmc":
                    if($sender->hasPermission("castleraid.gmc"))
                    {
                        if($sender instanceof Player)
                        {
                          $sender->sendMessage(TextFormat::GRAY . "Your gamemode has been updated.");
                          $sender->setGamemode(1);
                        }
                    }
                break;

            case "gms":
                if ($sender->hasPermission("castleraid.gms"))
                {
                    if ($sender instanceof Player)
                    {
                        $sender->sendMessage(TextFormat::GRAY . "Your gamemode has been updated.");
                        $sender->setGamemode(0);
                    }
                }
                break;

            case "kickall":
                if($sender->hasPermission("castleraid.kickall"))
                {
                    if ($sender instanceof Player)
                    {
                        foreach ($this->getServer()->getOnlinePlayers() as $players)
                        {
                            if ($players !== $sender)
                            {
                                $msg = $this->getConfig()->get("kickallmsg");
                                $players->kick((string)$msg, false);
                            }

                        }
                    }
                }
                break;

            case "vanish":
                if($sender->hasPermission("castleraid.vanish"))
                {
                    if ($sender instanceof Player)
                    {
                        $sender->sendMessage(TextFormat::GRAY . "You are now vanished.");
                        $sender->setInvisible(true);
                    }
                }
                break;

            case "unvanish":
                if($sender->hasPermission("castleraid.unvanish"))
                {
                    if($sender instanceof Player)
                    {
                        $sender->sendMessage(TextFormat::GRAY . "You are now unvanished.");
                        $sender->setInvisible(false);
                    }
                }
                break;

            case "freeze":
                if($sender->hasPermission("castleraid.freeze"))
                {
                    if(!isset($args[0]) || !isset($args[1]))
                    {
                        $sender->sendMessage(TextFormat::GRAY . "Usage: /freeze (player_name) true/false");
                    }
                    if(isset($args[0]) && isset($args[1]))
                    {
                        $playername = $args[0];
                        $conditional = $args[1];
                        if($conditional === "true")
                        {
                            $this->getServer()->getPlayer($playername)->setImmobile(true);
                            $sender->sendMessage(TextFormat::GRAY . "Player " . $playername . " is now frozen.");
                        }
                        elseif ($conditional === "false")
                        {
                            $this->getServer()->getPlayer($playername)->setImmobile(false);
                            $sender->sendMessage(TextFormat::GRAY . "Player " . $playername . " is now unfrozen.");
                        }
                        else
                        {
                            $sender->sendMessage(TextFormat::GRAY . "Please say either true or false.");
                        }
                    }
                }
                break;

            case "nick":
                if ($sender instanceof Player)
                {
                    if ($sender->hasPermission("castleraid.nick"))
                    {
                        if (!isset($args[0]) || !isset($args[1]))
                        {
                            $sender->sendMessage(TextFormat::GRAY . "Usage: /nick (target_player) (nicked_name)");
                        }
                        elseif (isset($args[0]) && isset($args[1]))
                        {
                            $tarperson = $args[0];
                            $nickedname = $args[1];
                            $player = $this->getServer()->getPlayer($tarperson);
                            if ($player === null)
                            {
                                $sender->sendMessage(TextFormat::GRAY . "Player does not exist or is not online.");
                            }
                            else {
                                $this->getServer()->getPlayer((string)$tarperson)->setDisplayName((string)$nickedname);
                                $sender->sendMessage(TextFormat::GRAY . "Player " . (string)$tarperson . " has been nicked to " . $nickedname . ".");
                            }
                        }
                        else {
                            $sender->sendMessage(TextFormat::GRAY . "Incorrect Usage.");
                        }
                    }
                }
                break;

            case "warn":
                if($sender instanceof Player)
                {
                    if($sender->hasPermission("castleraid.warn"))
                    {
                        if (!isset($args[0]))
                        {
                            $sender->sendMessage(TextFormat::GRAY . "Usage: /warn (target_player)");
                        }
                        elseif(isset($args[0]))
                        {
                            $targperson = $args[0];
                            $player1 = $this->getServer()->getPlayer($targperson);
                            if ($player1 === null)
                            {
                                $sender->sendMessage(TextFormat::GRAY . "Player does not exist or is not online.");
                            }
                            else
                            {
                                $player1->setHealth(1);
                                $player1->sendPopup(TextFormat::RED . TextFormat::BOLD . "You have been WARNED.");
                                $player1->sendTip(TextFormat::RED . TextFormat::BOLD . "You have been WARNED.");
                            }
                        }
                    }
                }
                break;

            case "heal":
                if ($sender instanceof Player)
                {
                    if($sender->hasPermission("castleraid.heal"))
                    {
                        $sender->sendMessage(TextFormat::GRAY . "The Gods have healed you!");
                        $sender->setHealth(20);
                    }
                }
                break;

            case "feed":
                if ($sender instanceof Player)
                {
                    if($sender->hasPermission("castleraid.feed"))
                    {
                        $sender->sendMessage(TextFormat::GRAY . "The Gods have feeded you!");
                        $sender->setFood(20);
                    }
                }
                break;

            case "clrinv":
                if ($sender->hasPermission("castleraid.clrinv"))
                {
                    if ($sender instanceof Player)
                    {
                        if (isset($args[0]))
                        {
                            $whotoclr = $args[0];
                            $player2 = $this->getServer()->getPlayer($whotoclr);
                            if ($player2 === null)
                            {
                                $sender->sendMessage(TextFormat::GRAY . "Player does not exist, or is not online.");
                            }
                            else
                            {
                                $player2->getInventory()->clearAll(true);
                                $sender->sendMessage(TextFormat::GRAY . $player2 . "'s inventory has been cleared.");
                                $player2->sendPopup(TextFormat::RED . "Your inventory has been cleared.");
                            }
                        }
                        else
                        {
                            $sender->getInventory()->clearAll();
                            $sender->sendPopup(TextFormat::RED . "Your inventory has been cleared.");
                        }
                    }
                }
                break;

            case "requestadmin":
                if($sender instanceof Player)
                {
                    if(!isset($args[0]))
                    {
                        $sender->sendMessage(TextFormat::GRAY . "Usage: /requestadmin (reason) **THE REASON MUST BE ONE WORD**");
                    }
                    if(isset($args[0]))
                    {
                        $reason = $args[0];
                        foreach ($this->getServer()->getOnlinePlayers() as $ops)
                        {
                            if ($ops->isOp())
                            {
                                if ($ops instanceof Player)
                                {
                                    $pname = $sender->getDisplayName();
                                    $ops->sendMessage(TextFormat::GRAY . "-- Admin Help Requested --\nPlayer: " . $pname . "\nReason: $reason\n----");
                                    $sender->sendMessage(TextFormat::GRAY . "If an admin is online, he has recieved your help request.");
                                }
                            }
                        }
                    }
                }
                break;

            case "getpos":
                if($sender instanceof Player)
                {
                    if(!isset($args[0]))
                    {
                        $getx = $sender->getX();
                        $gety = $sender->getYaw();
                        $getz = $sender->getZ();
                        $sender->sendMessage(TextFormat::GRAY . "Your X: " . $getx . "\nYour Y: " . $gety . "\nYour Z: " . $getz);
                    }
                }
                break;

            case "coinflip":
                if($sender instanceof Player)
                {
                    if (!isset($args[0]))
                    {
                        $digit = (int)(rand(0,1));
                        if ($digit === 1)
                        {
                            $sender->sendMessage(TextFormat::GRAY . "Heads!");
                        }
                        else
                        {
                            $sender->sendMessage(TextFormat::GRAY . "Tails!");
                        }
                    }
                }
                break;

            case "spawn":
                if($sender instanceof Player)
                {
                    if(!isset($args[0]))
                    {
                        $sender->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
                        $sender->sendMessage(TextFormat::GRAY . "You have been teleported to spawn.");
                    }
                }
                break;
        }

        return false;
    }

}
