<?php

namespace App\Entity;

class Dungeon
{

    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {}

}
