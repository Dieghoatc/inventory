<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 9/30/2018
 * Time: 9:11 AM
 */

namespace App\Fixtures;

use App\Entity\Warehouse;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class WarehouseFixtures extends Fixture
{


    /**
     * Load data fixtures with the passed EntityManager
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->createWarehouses($manager);
    }

    protected function createWarehouses(ObjectManager $manager): void
    {
        $items = [
          ['name' => 'Colombia'],
          ['name' => 'Usa']
        ];

        foreach ($items as $item) {
            $warehouse = new Warehouse();
            $warehouse->setName($item['name']);
            $manager->persist($warehouse);
        }
        $manager->flush();
    }
}