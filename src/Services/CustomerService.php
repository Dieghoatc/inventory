<?php

namespace App\Services;

use App\Entity\City;
use App\Entity\Customer;
use App\Entity\CustomerAddress;
use App\Repository\CityRepository;
use App\Repository\CustomerAddressRepository;
use App\Repository\CustomerRepository;
use Doctrine\Common\Persistence\ObjectManager;

class CustomerService
{
    private $objectManager;

    private $customerRepo;

    private $customerAddressRepo;

    private $cityRepo;

    public function __construct(
        ObjectManager $objectManager,
        CustomerRepository $customerRepo,
        CustomerAddressRepository $customerAddressRepo,
        CityRepository $cityRepo
    ) {
        $this->objectManager = $objectManager;
        $this->customerRepo = $customerRepo;
        $this->customerAddressRepo = $customerAddressRepo;
        $this->cityRepo = $cityRepo;
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

    public function attachAddresses(Customer $customer, array $addresses): void
    {
        foreach ($addresses as $addressData) {
            if (array_key_exists('id', $addressData)) {
                $customerAddress = $this->customerAddressRepo->find($addressData['id']);
            } else {
                $customerAddress = new CustomerAddress();
            }

            $city = $this->cityRepo->find($addressData['city']['id']);

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
