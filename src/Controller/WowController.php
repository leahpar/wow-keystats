<?php

namespace App\Controller;

use App\Service\WowService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class WowController extends AbstractController
{
    #[Route('/wow', name: 'app_wow')]
    public function index(WowService $wowService)
    {
        $params = $this->getParameter('wow.characters');

        $dungeons = $wowService->getDungeons();

        $characters = [];
        foreach ($params as $c) {
            $character = $wowService->getCharacter($c['realm'], $c['name']);
            $wowService->getMedias($character);
            $wowService->getKeys($character);

            $characters[] = $character;
        }

        return $this->render('wow/index.html.twig', [
            'characters' => $characters,
            'dungeons' => $dungeons,
        ]);
    }
}
