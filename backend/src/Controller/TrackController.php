<?php

namespace App\Controller;

use App\Service\TrackService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/tracks')]
class TrackController extends AbstractController
{
    private TrackService $trackService;

    public function __construct(TrackService $trackService)
    {
        $this->trackService = $trackService;
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $tracks = $this->trackService->findAll();

        $payload = array_map(fn($t) => $t->toArray(), $tracks);

        return $this->json($payload);
    }

    #[Route('', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON payload'], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->trackService->create($data);

        if (is_array($result)) {
            return $this->json(['errors' => $result], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($result->toArray(), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $track = $this->trackService->findById($id);

        if (!$track) {
            return $this->json(['error' => 'Track not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON payload'], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->trackService->update($track, $data);

        if (is_array($result)) {
            return $this->json(['errors' => $result], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($result->toArray());
    }
}
