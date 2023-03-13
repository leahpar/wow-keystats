<?php

namespace App\Entity;

class Key
{
    public function __construct(
        public int $dungeonId,
        public int $affixId,
        public int $level,
        //public int $time,
        public ?\DateTime $date = null,
        public int $rating = 0,
        public string $ratingRGB = "ffffff",
        public bool $isCompleted = false,
    ) {}

}
