<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixures extends Fixture
{
    /**
     * Load data fixtures with the passed EntityManager
     * @param ObjectManager $manager
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
                'email' => 'sergio@gmail.com',
                'username' => 'sbarbosa115',
                'password' => '123456',
                'roles' => ['ROLE_ADMIN']
            ],
            [
                'name' => 'Juan Diaz',
                'email' => 'sales@klassicfab.com',
                'username' => 'juan',
                'password' => 'Klassic2018',
                'roles' => ['ROLE_ADMIN']
            ],
        ];

        foreach ($items as $item) {
            $user = new User();
            $user->setName($item['name']);
            $user->setEmail($item['email']);
            $user->setUsername($item['username']);
            $user->setPlainPassword($item['password']);
            $user->setRoles($item['roles']);
            $user->setEnabled(1);
            $manager->persist($user);
        }
        $manager->flush();
    }
}
