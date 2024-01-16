<?php

use pocketmine\plugin\PluginBase;
use AutoMine\scheduler\AutoMineTask;
use AutoMine\Rarity;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Block;
use AutoMine\Area;
use pocketmine\player\Player;
use pocketmine\entity\Location;

class AutoMine extends PluginBase
{
    private static self $instance;

    public function onEnable() : void
    {
        self::$instance = $this;
        $this->getScheduler()->scheduleDelayedRepeatingTask(new AutoMineTask(), 20, 12000);
    }

    /**
     * @return $this
     */
    public static function getInstance() : self
    {
        return self::$instance;
    }

    /**
     * @param int $rarity
     * @param bool $merge
     * @return array
     */
    public function getBlocks(int $rarity = Rarity::DEFAULT, bool $merge = false) : array
    {
        $default = [
            VanillaBlocks::STONE(),
            VanillaBlocks::COBBLESTONE()
        ];
        $common = [
            VanillaBlocks::COAL_ORE(),
            VanillaBlocks::IRON_ORE(),
            VanillaBlocks::COPPER_ORE(),
            VanillaBlocks::GOLD_ORE(),
            VanillaBlocks::REDSTONE_ORE(),
            VanillaBlocks::LAPIS_LAZULI_ORE()
        ];
        $uncommon = [
            VanillaBlocks::EMERALD_ORE(),
            VanillaBlocks::DIAMOND_ORE()
        ];
        $mythic = [
            VanillaBlocks::ANCIENT_DEBRIS()
        ];
        return match ($rarity)
        {
            Rarity::MYTHIC => (($merge) ? array_merge($default, $common, $uncommon, $mythic) : $mythic),
            Rarity::UNCOMMON => (($merge) ? array_merge($default, $common, $uncommon) : $uncommon),
            Rarity::COMMON => (($merge) ? array_merge($default, $common) : $common),
            default => $default
        };
    }

    /**
     * @param int $rarity
     * @return Block
     */
    public function getRandomBlock(int $rarity = Rarity::MYTHIC) : Block
    {
        $rarityMap = [
            Rarity::MYTHIC => [1 => Rarity::MYTHIC, 4 => Rarity::UNCOMMON, 25 => Rarity::COMMON],
            Rarity::UNCOMMON => [4 => Rarity::UNCOMMON, 25 => Rarity::COMMON],
            Rarity::COMMON => [25 => Rarity::COMMON]
        ];
        $blocks = $this->getBlocks();
        foreach (($rarityMap[$rarity] ?? []) as $threshold => $selected)
        {
            if (mt_rand(1, 100) <= $threshold)
            {
                $blocks = self::getBlocks($selected);
                break;
            }
        }
        return $blocks[array_rand($blocks)];
    }

    /**
     * @param Area $area
     * @param Player $player
     * @param Location $targetPosition
     * @return void
     */
    public function teleportPlayersOut(Area $area, Player $player, Location $targetPosition) : void
    {
        foreach ($area->get() as $currentPosition)
        {
            if ($currentPosition)
            {
                if ($currentPosition->equals($player->getPosition()->floor())):
                    $player->teleport($targetPosition);
                endif;
            }
        }
    }
}
