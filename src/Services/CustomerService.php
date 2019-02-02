<?php

namespace App\Services;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\Customer;
use App\Entity\CustomerAddress;
use App\Entity\State;
use App\Repository\CityRepository;
use App\Repository\CountryRepository;
use App\Repository\CustomerAddressRepository;
use App\Repository\CustomerRepository;
use App\Repository\StateRepository;
use Doctrine\Common\Persistence\ObjectManager;

class CustomerService
{
    private $objectManager;

    private $customerRepo;

    private $customerAddressRepo;

    private $cityRepo;

    private $stateRepo;

    private $countryRepo;

    public function __construct(
        ObjectManager $objectManager,
        CustomerRepository $customerRepo,
        CustomerAddressRepository $customerAddressRepo,
        CityRepository $cityRepo,
        StateRepository $stateRepo,
        CountryRepository $countryRepo
    ) {
        $this->objectManager = $objectManager;
        $this->customerRepo = $customerRepo;
        $this->customerAddressRepo = $customerAddressRepo;
        $this->cityRepo = $cityRepo;
        $this->stateRepo = $stateRepo;
        $this->countryRepo = $countryRepo;
    }

    public function addOrUpdate(array $customerData): Customer
    {
        if (array_key_exists('id', $customerData)) {
            $customer = $this->customerRepo->find($customerData['id']);
            if (!$customer instanceof Customer) {
                throw new \LogicException('Customer not found');
            }
        } else {
            $customer = new Customer();
            $customer->setEmail($customerData['email']);
            $customer->setFirstName($customerData['firstName']);
            $customer->setLastName($customerData['lastName']);
            $customer->setPhone($customerData['phone']);
            $this->attachAddresses($customer, $customerData['addresses']);

            $this->objectManager->persist($customer);
            $this->objectManager->flush();
        }

        return $customer;
    }

    public function findOrCreateCity(array $cityData): City
    {
        if (!array_key_exists('id', $cityData)) {
            throw new \InvalidArgumentException('Missing city ID?');
        }

        if ($cityData['id'] !== null) {
            $city = $this->cityRepo->find($cityData['id']);

            if (!$city instanceof City) {
                throw new \InvalidArgumentException('This state not was found.');
            }
        } else {
            $state = $this->findOrCreateState($cityData['state']);

            $city = new City();
            $city->setState($state);
            $city->setName($cityData['name']);
            $this->objectManager->persist($city);
            $this->objectManager->flush();
        }

        return $city;
    }

    public function findOrCreateState(array $stateData): State
    {
        if (!array_key_exists('id', $stateData)){
            throw new \InvalidArgumentException('Missing state ID?');
        }

        if ($stateData['id'] !== null) {
            $state = $this->stateRepo->find($stateData['id']);

            if(!$state instanceof State) {
                throw new \InvalidArgumentException('This state not was found.');
            }

        } else {
            $country = $this->findOrCreateCountry($stateData['country']);

            $state = new State();
            $state->setName($stateData['name']);
            $state->setCountry($country);
            $this->objectManager->persist($state);
            $this->objectManager->flush();
        }

        return $state;
    }

    public function findOrCreateCountry(array $countryData): Country
    {
        if (!array_key_exists('id', $countryData)) {
            throw new \InvalidArgumentException('Missing country ID?');
        }

        if ($countryData['id'] !== null) {
            $country = $this->countryRepo->find($countryData['id']);
            if(!$country instanceof Country) {
                throw new \InvalidArgumentException('This country not was found.');
            }
        } else {
            $country = new Country();
            $country->setName($countryData['name']);
            $this->objectManager->persist($country);
            $this->objectManager->flush();
        }

        return $country;
    }

    public function attachAddresses(Customer $customer, array $addresses): void
    {
        foreach ($addresses as $addressData) {
            if (array_key_exists('id', $addressData)) {
                $customerAddress = $this->customerAddressRepo->find($addressData['id']);
            } else {
                $customerAddress = new CustomerAddress();
            }

            $city = $this->findOrCreateCity($addressData['city']);

            if (!$city instanceof City) {
                throw new \LogicException('This city was not found.');
            }

            $customerAddress->setAddress($addressData['address']);
            $customerAddress->setZipCode($addressData['zipCode']);
            $customerAddress->setCity($city);
            $customer->addAddress($customerAddress);
            $customerAddress->setCustomer($customer);

            $this->objectManager->persist($customerAddress);
            $this->objectManager->persist($customer);

            if (!$customer instanceof Customer) {
                throw new \LogicException('Customer not found');
            }
        }
        $this->objectManager->flush();
    }

    public function findOrCreate(array $customerData): Customer
    {

    }
}
