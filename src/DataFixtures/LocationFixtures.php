<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\State;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LocationFixtures extends Fixture
{
    public const DEFAULT_CITY = 'city';
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->createLocation($manager);
    }

    protected function createLocation(ObjectManager $manager): void
    {
        $country = new Country();
        $country->setName('USA');
        $manager->persist($country);

        $state = new State();
        $state->setName('Florida');
        $state->setCountry($country);
        $manager->persist($state);

        $city = new City();
        $city->setName('West Palm Beach');
        $city->setState($state);
        $manager->persist($city);
        $manager->flush();

        $this->addReference(self::DEFAULT_CITY, $city);
    }


}
