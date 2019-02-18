<?php

namespace App\Services;

use App\Entity\Log;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogService
{
    private $objectManager;

    private $user;

    public function __construct(ObjectManager $objectManager, TokenStorageInterface $user)
    {
        $this->objectManager = $objectManager;
        $this->user = $user;
    }

    public function add(string $entity, string $event, array $detail = []): void
    {
        $detailAsString = '';
        if (count($detail) > 0) {
            $detailAsString = json_encode($detail);
        }

        $log = new Log();
        $log->setCreatedAt(new \DateTime('now'));
        $log->setUser($this->user->getToken()->getUser());
        $log->setEvent($event);
        $log->setEntity(mb_strtolower($entity));
        $log->setDetail($detailAsString);
        $this->objectManager->persist($log);
        $this->objectManager->flush();
    }
}
