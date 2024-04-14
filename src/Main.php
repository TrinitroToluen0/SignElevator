<?php

declare(strict_types=1);

namespace Mencoreh\SignElevator;

use pocketmine\plugin\PluginBase;
use pocketmine\block\BaseSign;
use pocketmine\block\utils\SignText;
use pocketmine\entity\Location;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\player\Player;

class Main extends PluginBase implements Listener{


    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
    }

    public function onSignInteract(PlayerInteractEvent $event)
    {
        if($event->isCancelled()) return;

        $player = $event->getPlayer();
        $block = $event->getBlock();

        if ($event->getAction() == PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
            if ($block instanceof BaseSign) {
                $lines = $block->getText()->getLines();

                if ($lines[0] === "§1[Elevator]" && $lines[1] == "§0Up") {
                    $event->cancel();
                    $this->teleportPlayer($player, $block, "up");
                }

                if ($lines[0] === "§1[Elevator]" && $lines[1] == "§0Down") {
                    $event->cancel();
                    $this->teleportPlayer($player, $block, "down");
                }
            }
        }
    }

    public function onSignChange(SignChangeEvent $event)
    {
        $text = $event->getNewText();
        $lines = $text->getLines();

        if (strtolower($lines[0]) == "[elevator]" && strtolower($lines[1]) == "up") {
            $event->setNewText(new SignText(["§1[Elevator]", "§0Up"]));
        }

        if (strtolower($lines[0]) == "[elevator]" && strtolower($lines[1]) == "down") {
            $event->setNewText(new SignText(["§1[Elevator]", "§0Down"]));
        }
    }

    public function teleportPlayer(Player $player, BaseSign $sign, string $direction) {
        $signPosition = $sign->getPosition();
        $x = (int) $signPosition->getX();
        $y = (int) $signPosition->getY();
        $z = (int) $signPosition->getZ();

        $playerLocation = $player->getLocation();
        $world = $playerLocation->getWorld();
        $yaw = $playerLocation->getYaw();
        $pitch = $playerLocation->getPitch();

        if($direction === "up") {
            for ($count = $y + 1; $count <= $world->getMaxY(); $count++) {
                $block = $world->getBlockAt($x, $count, $z);
                $blockAbove = $world->getBlockAt($x, $count + 1, $z);
                $blockAboveTwo = $world->getBlockAt($x, $count + 2, $z);
                if(!$block->isTransparent() && ($blockAbove->isTransparent() && $blockAboveTwo->isTransparent())) {
                    $player->teleport(new Location($x + 0.5, $count + 1, $z + 0.5, $world, $yaw, $pitch));
                    return;
                }
            }
            $player->sendMessage("§cNo se pudo encontrar una ubicación válida para teletransportarse.");
        } elseif ($direction === "down") {
            $isFirstTerrain = true;
            for ($count = $y - 1; $count >= $world->getMinY(); $count--) {
                $block = $world->getBlockAt($x, $count, $z);
                $blockAbove = $world->getBlockAt($x, $count + 1, $z);
                $blockAboveTwo = $world->getBlockAt($x, $count + 2, $z);
                if (!$block->isTransparent() && ($blockAbove->isTransparent() && $blockAboveTwo->isTransparent())) {
                    if($isFirstTerrain === true) {
                        $isFirstTerrain = false;
                        continue;
                    }
                    $player->teleport(new Location($x + 0.5, $count + 1, $z + 0.5, $world, $yaw, $pitch));
                    return;
                }
            }
            $player->sendMessage("§cNo se pudo encontrar una ubicación válida para teletransportarse.");
        }
    }
}