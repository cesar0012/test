<?php

namespace App\Service;

use App\Entity\Track;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TrackService
{
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->validator = $validator;
    }

    public function findAll(): array
    {
        return $this->em->getRepository(Track::class)->findAll();
    }

    public function findById(int $id): ?Track
    {
        return $this->em->getRepository(Track::class)->find($id);
    }

    public function create(array $data): Track|array
    {
        $track = new Track();

        return $this->hydrate($track, $data);
    }

    public function update(Track $track, array $data): Track|array
    {
        return $this->hydrate($track, $data);
    }

    private function hydrate(Track $track, array $data): Track|array
    {
        if (isset($data['title'])) {
            $track->setTitle($data['title']);
        }

        if (isset($data['artist'])) {
            $track->setArtist($data['artist']);
        }

        if (isset($data['duration'])) {
            $track->setDuration((int) $data['duration']);
        }

        if (array_key_exists('isrc', $data)) {
            $track->setIsrc($data['isrc'] ?: null);
        }

        $errors = $this->validator->validate($track);

        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $violation) {
                $messages[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return $messages;
        }

        $this->em->persist($track);
        $this->em->flush();

        return $track;
    }
}
