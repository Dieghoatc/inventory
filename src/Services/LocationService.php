<?php

namespace App\Services;

use App\Repository\CountryRepository;

class LocationService
{
    private $countryRepo;

    public function __construct(
        CountryRepository $countryRepo
    ) {
        $this->countryRepo = $countryRepo;
    }

    public function getNestedLocations(): array
    {
        return $this->countryRepo->findAllAsArray();
    }
}
