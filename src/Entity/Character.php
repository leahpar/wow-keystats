<?php

namespace App\Entity;

class Character
{

    /** @var array<Key> */
    public array $keys = [];

    // avatar, inset, main, main-raw
    public array $medias = [];

    public int $rating = 0;
    public string $ratingRGB = "000000";

    public function __construct(
        public readonly string $name,
        public readonly string $realm,
        public ?string $displayName = null,
        public int $level = 0,
        public int $ilvl = 0,
    ) {}


    public function getKey(int $dungeonId, int $affixId): ?Key
    {
        foreach ($this->keys as $key) {
            if ($key->dungeonId === $dungeonId && $key->affixId === $affixId) {
                return $key;
            }
        }
        return null;
    }

}
