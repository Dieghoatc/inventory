<?php

namespace App\Services;

use App\Repository\CustomerRepository;
use Doctrine\Common\Persistence\ObjectManager;

class CustomerService
{
    private $objectManager;

    private $customerRepo;

    public function __construct(
        ObjectManager $objectManager,
        CustomerRepository $customerRepo
    ) {
        $this->objectManager = $objectManager;
        $this->customerRepo = $customerRepo;
    }

    public function search(string $field, string $query): array
    {
        return $this->customerRepo->findBy([$field => $query], null, 5);
    }
}
