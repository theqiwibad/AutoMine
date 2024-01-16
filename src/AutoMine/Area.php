<?php

namespace AutoMine;

use pocketmine\world\Position;
use Generator;
use pocketmine\block\VanillaBlocks;
use AutoMine;
use pocketmine\world\sound\BlockPlaceSound;
use pocketmine\block\Block;
use pocketmine\world\particle\BlockBreakParticle;

class Area
{
    public function __construct(public int $lengthX, public int $height, public int $lengthZ, public Position $position) { }

    /**
     * @return Generator
     */
    public function get() : Generator
    {
        for ($x = 0; $x < $this->lengthX; $x++)
        {
            for ($y = 0; $y < $this->height; $y++)
            {
                for ($z = 0; $z < $this->lengthZ; $z++):
                    yield $this->position->add($x, $y, $z);
                endfor;
            }
        }
    }

    /**
     * @param int $rarity
     * @return void
     */
    public function fill(int $rarity) : void
    {
        foreach ($this->get() as $currentPosition)
        {
            if ($currentPosition)
            {
                $world = $this->position->getWorld();
                if ($world->getBlock($currentPosition)->getTypeId() === (VanillaBlocks::AIR())->getTypeId())
                {
                    $randomBlock = AutoMine::getInstance()->getRandomBlock($rarity);
                    $world->setBlock($currentPosition, $randomBlock);
                    $world->addSound($currentPosition, new BlockPlaceSound($randomBlock));
                }
            }
        }
    }

    /**
     * @return void
     */
    public function empty() : void
    {
        foreach ($this->get() as $currentPosition)
        {
            if ($currentPosition)
            {
                /** @var Block $block */
                foreach (AutoMine::getInstance()->getBlocks(Rarity::MYTHIC, true) as $block)
                {
                    $world = $this->position->getWorld();
                    $currentBlock = $world->getBlock($currentPosition);
                    if ($currentBlock->getTypeId() === $block->getTypeId())
                    {
                        $world->setBlock($currentPosition, VanillaBlocks::AIR());
                        $world->addParticle($currentPosition, new BlockBreakParticle($currentBlock));
                    }
                }
            }
        }
    }
}
