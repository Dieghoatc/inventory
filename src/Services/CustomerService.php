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
use InvalidArgumentException;
use LogicException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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
                throw new LogicException('Customer not found');
            }
            $customer = $this->update($customerData, $customer);
        } else {
            $customer = $this->add($customerData);
        }

        return $customer;
    }

    public function add(
        array $customerData
    ): Customer {
        $customer = new Customer();
        $this->setCustomerData($customerData, $customer);
        return $customer;
    }

    public function update(
        array $customerData,
        Customer $customer
    ): Customer {
        $this->setCustomerData($customerData, $customer);
        return $customer;
    }

    public function setCustomerData(
        array $customerData,
        Customer $customer
    ): void {
        $customer->setEmail($customerData['email']);
        $customer->setFirstName($customerData['firstName']);
        $customer->setLastName($customerData['lastName']);
        $customer->setPhone($customerData['phone']);
        $this->syncAddresses($customer, $customerData['addresses']);

        $this->objectManager->persist($customer);
        $this->objectManager->flush();
    }

    public function delete(Customer $customer): void
    {
        $this->objectManager->remove($customer);
        $this->objectManager->flush();
    }

    public function findOrCreateCity(array $cityData): City
    {
        if (!array_key_exists('id', $cityData) && !array_key_exists('name', $cityData)) {
            throw new InvalidArgumentException('Missing city ID?');
        }

        $city = $this->cityRepo->findOneByIdOrName(
            $cityData['id'] ?? null,
            $cityData['name'] ?? null
        );

        if (!$city instanceof City) {
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
        if (!array_key_exists('id', $stateData) && !array_key_exists('name', $stateData) ) {
            throw new InvalidArgumentException('Missing state ID?');
        }

        $state = $this->stateRepo->findOneByIdOrName(
            $stateData['id'] ?? null,
            $stateData['name'] ?? null
        );

        if (!$state instanceof State) {
            $country = $this->findOrCreateCountry($stateData['country']);
            $state = new State();
            $state->setName($stateData['name']);
            $state->setCode($stateData['name']);
            $state->setCountry($country);
            $this->objectManager->persist($state);
            $this->objectManager->flush();
        }


        return $state;
    }

    public function findOrCreateCountry(array $countryData): Country
    {
        if (!array_key_exists('id', $countryData) && !array_key_exists('name', $countryData)) {
            throw new InvalidArgumentException('Missing country ID?');
        }

        $country = $this->countryRepo->findOneByIdOrName(
            $countryData['id'] ?? null,
            $countryData['name'] ?? null
        );

        if (!$country instanceof Country) {
            $country = new Country();
            $country->setName($countryData['name']);
            $this->objectManager->persist($country);
            $this->objectManager->flush();
        }

        return $country;
    }

    public function syncAddresses(Customer $customer, array $addresses): void
    {
        foreach ($customer->getAddresses() as $address) {
            $someFound = array_filter($addresses, static function($addressItem) use ($address) {
                return array_key_exists('id', $addressItem) && (int) $addressItem['id'] === $address->getId();
            });

            if (count($someFound) === 0) {
                $customer->removeAddress($address);
            }
        }

        $this->objectManager->persist($customer);

        foreach ($addresses as $addressData) {
            $customerAddress = new CustomerAddress();

            if (array_key_exists('id', $addressData)) {
                $customerAddress = $this->customerAddressRepo->find($addressData['id']);
            }

            $city = $this->findOrCreateCity($addressData['city']);
            if (!$city instanceof City) {
                throw new LogicException('This city was not found.');
            }

            $customerAddress->setAddress($addressData['address']);
            $customerAddress->setZipCode($addressData['zipCode']);
            $customerAddress->setCity($city);
            $customer->addAddress($customerAddress);
            $customerAddress->setCustomer($customer);

            $this->objectManager->persist($customerAddress);
            $this->objectManager->persist($customer);

            if (!$customer instanceof Customer) {
                throw new LogicException('Customer not found');
            }
        }

        $this->objectManager->flush();
    }

    public function getCustomerArrayTemplate(): array
    {
        return [
            'id',
            'firstName',
            'lastName',
            'email',
            'phone',
            'addresses' => [
                'id', 'zipCode', 'address', 'city' => [
                    'id', 'name', 'state' => [
                        'id', 'name', 'code', 'country' => [
                            'id', 'name'
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * @return Customer[]
     */
    public function getCustomers(): array
    {
        return $this->customerRepo->findAll();
    }

    /**
     * @throws ExceptionInterface
     */
    private function serializeCustomer(Customer $customer = null, array $customers = null): array
    {
        $entityOrCollection = $customer;
        if ($customers !== null) {
            $entityOrCollection = $customers;
        }
        $serializer = new Serializer([new ObjectNormalizer()]);
        return $serializer->normalize($entityOrCollection, 'array', ['attributes' => $this->getCustomerArrayTemplate()]);
    }

    public function getCustomersAsArray(): array
    {
        return $this->serializeCustomer(null, $this->getCustomers());
    }

    public function getCustomerAsArray(Customer $customer): array
    {
        return $this->serializeCustomer($customer);
    }

    public function getCustomerById(int $customerId): Customer
    {
        $customer = $this->customerRepo->find($customerId);

        if(!$customer instanceof Customer) {
            throw new InvalidArgumentException('Invalid exception');
        }

        return $customer;
    }

}
