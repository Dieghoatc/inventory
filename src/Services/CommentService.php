<?php

namespace App\Services;

use App\Entity\Comment;
use App\Entity\Order;
use App\Entity\User;
use App\Repository\CommentRepository;
use Doctrine\Common\Persistence\ObjectManager;

class CommentService
{
    private $objectManager;

    private $commentRepo;

    public function __construct(
        ObjectManager $objectManager,
        CommentRepository $commentRepo
    ) {
        $this->objectManager = $objectManager;
        $this->commentRepo = $commentRepo;
    }

    public function syncComments(array $comments, User $user, Order $order): array
    {
        foreach ($comments as $commentItemKey => $commentItem) {
            if (null === $commentItem['id']) {
                $comment = new Comment();
                $comment->setUser($user);
                $comment->setOrder($order);
                $comment->setContent('');
            } else {
                $comment = $this->commentRepo->find($commentItem['id']);
                if ($comment instanceof Comment) {
                    $comment->setContent($commentItem['content']);
                }
            }
            $this->objectManager->persist($comment);
            $comments['id'] = $comment->getId();
        }
        $this->objectManager->flush();
        return $comments;
    }
}
