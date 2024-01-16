<?php

namespace AutoMine\scheduler;

use pocketmine\scheduler\Task;
use pocketmine\world\particle\FloatingTextParticle;
use pocketmine\Server;
use AutoMine\Area;
use pocketmine\world\Position;
use AutoMine\Rarity;
use pocketmine\world\format\Chunk;
use AutoMine;
use pocketmine\entity\Location;
use pocketmine\scheduler\ClosureTask;

class AutoMineTask extends Task
{
    private ?FloatingTextParticle $particle = null;

    public function onRun() : void
    {
        $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
        $area = new Area(10, 5, 20, new Position(5000, 100, 5000, $world));
        $position = $area->position;
        $chance = mt_rand(1, 100);
        $rarity = match (true)
        {
            ($chance <= 10) => Rarity::MYTHIC,
            ($chance <= 40) => Rarity::UNCOMMON,
            default => Rarity::COMMON
        };
        $rarityText = [
            Rarity::MYTHIC => "мифическая",
            Rarity::UNCOMMON => "необычная",
            Rarity::COMMON => "обычная"
        ];
        $period = $this->getHandler()->getPeriod();
        $world->orderChunkPopulation(($position->getFloorX() >> Chunk::COORD_BIT_SIZE), ($position->getFloorZ() >> Chunk::COORD_BIT_SIZE), null)->onCompletion(
            function () use ($area, $rarity) : void
            {
                $area->empty();
                $area->fill($rarity);
            },
            fn () => null
        );
        foreach (Server::getInstance()->getOnlinePlayers() as $player)
        {
            AutoMine::getInstance()->teleportPlayersOut($area, $player, new Location(4995, 105, 5010, $world, 270, 0));
            $player->sendMessage("§9Авто-шахта§f обновилась, успей выкопать ресурсы!");
        }
        AutoMine::getInstance()->getScheduler()->scheduleRepeatingTask(
            new ClosureTask(
                function () use (&$period, $world, &$rarityText, $rarity) : void
                {
                    if ($period > 0)
                    {
                        $period -= 20;
                        $position = new Position(4998, 107, 5010, $world);
                        $particle = new FloatingTextParticle("Редкость:§9 " . $rarityText[$rarity] . "\n" . "Обновление через:§9 " . ($period / 20));
                        if ($this->particle)
                        {
                            $this->particle->setInvisible();
                            $world->addParticle($position, $this->particle);
                        }
                        $this->particle = $particle;
                        $world->addParticle($position, $particle);
                    }
                }
            ),
            20
        );
    }
}
