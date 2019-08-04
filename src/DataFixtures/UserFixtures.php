<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    public const DEFAULT_USER = 'default-user';

    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Load data fixtures with the passed EntityManager.
     */
    public function load(ObjectManager $manager): void
    {
        $this->createAdminUser($manager);
    }

    protected function createAdminUser(ObjectManager $manager): void
    {
        $items = [
            [
                'name' => 'Sergio Barbosa',
                'email' => 'sbarbosa115@gmail.com',
                'username' => 'sbarbosa115',
                'password' => '123456',
                'roles' => ['ROLE_ADMIN'],
            ],
        ];

        foreach ($items as $item) {
            $user = new User();
            $user->setName($item['name']);
            $user->setEmail($item['email']);
            $user->setUsername($item['username']);
            $user->setPassword($this->passwordEncoder->encodePassword($user, $item['password']));
            $user->setRoles($item['roles']);
            $user->setEnabled(1);
            $manager->persist($user);

            $this->addReference(self::DEFAULT_USER, $user);
        }
        $manager->flush();
    }
}
