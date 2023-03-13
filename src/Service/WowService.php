<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\Dungeon;
use App\Entity\Key;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\Cache\ItemInterface;

class WowService
{

    private FilesystemAdapter $cache;

    public function __construct(
        private readonly BattlenetApi $battlenetApi,
    ) {
        $this->cache = new FilesystemAdapter(
            'wow',
            0,
            __DIR__ . '/../../var/wowcache');
    }

    public function getCharacter(string $realm, string $name): Character
    {
        $cacheKey = 'character_eu' . '_' . $realm . '_' . $name;
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($realm, $name) {
            $item->expiresAfter(3600);
            $data = $this->battlenetApi->get(
                '/profile/wow/character/' . $realm . '/' . $name,
                ['namespace' => 'profile-eu', 'locale' => 'fr_FR']
            );
            return new Character(
                name: $name,
                realm: $realm,
                displayName: $data['name'],
                level: $data['level'],
                ilvl: $data['equipped_item_level'],
            );
        });
    }

    public function getMedias(Character $character)
    {
        $cacheKey = 'medias_eu' . '_' . $character->realm . '_' . $character->name;
        $medias = $this->cache->get($cacheKey, function (ItemInterface $item) use ($character) {
            $item->expiresAfter(3600);
            $data = $this->battlenetApi->get(
                '/profile/wow/character/' . $character->realm . '/' . $character->name . '/character-media',
                ['namespace' => 'profile-eu', 'locale' => 'fr_FR']
            );
            return array_combine(
                array_column($data['assets'], 'key'),
                array_column($data['assets'], 'value')
            );
        });
        $character->medias = $medias;
    }

    public function getDungeons(): array
    {
        $cacheKeys = 'dungeons_eu';
        return $this->cache->get($cacheKeys, function (ItemInterface $item) {
            $item->expiresAfter(3600);
            $data = $this->battlenetApi->get(
                '/data/wow/mythic-keystone/dungeon/index',
                ['namespace' => 'dynamic-eu', 'locale' => 'fr_FR']
            );
            return array_map(
                fn($d) => new Dungeon(id: $d['id'], name: $d['name']),
                $data['dungeons']
            );
        });
    }

    public function getKeys(Character $character)
    {
        $cacheKey = 'keys_eu' . '_' . $character->realm . '_' . $character->name;
        [$keys, $rating, $ratingRGB]  = $this->cache->get($cacheKey, function (ItemInterface $item) use ($character) {
            $item->expiresAfter(10);
            try {
                $data = $this->battlenetApi->get(
                    '/profile/wow/character/' . $character->realm . '/' . $character->name . '/mythic-keystone-profile/season/9',
                    ['namespace' => 'profile-eu', 'locale' => 'fr_FR']
                );
            } catch (ClientException $e) {
                return [[], 0, '000000'];
            }

            return [
                array_map(
                    fn($d) => new Key(
                        dungeonId: $d['dungeon']['id'],
                        affixId: $d['keystone_affixes'][0]['id'],
                        level: $d['keystone_level'],
                        //time: $d['duration'],
                        date: new \DateTime('@'.($d['completed_timestamp']/1000)),
                        rating: $d['mythic_rating']['rating'],
                        ratingRGB: $this->rgba2hex($d['mythic_rating']['color']),
                        isCompleted: $d['is_completed_within_time'],
                    ),
                    $data['best_runs']
                ),
                $data['mythic_rating']['rating'],
                $this->rgba2hex($data['mythic_rating']['color'])
            ];
        });
        $character->keys = $keys;
        $character->rating = $rating;
        $character->ratingRGB = $ratingRGB;
    }

    private function rgba2hex(array $rgba): string
    {
        return implode('', array_map(
            fn($d) => str_pad(dechex($d), 2, '0', STR_PAD_LEFT), array_slice($rgba, 0, 3)
        ));
    }

}
