<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CountryRepository;
use App\Services\CustomerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/customer", name="customer_")
 */
class CustomerController extends AbstractController
{
    /**
     * @Route("/", name="index", options={"expose"=true})
     */
    public function index(
        CustomerService $customerService
    ): Response {
        $customers = $customerService->getCustomersAsArray();

        return $this->render('customer/index.html.twig', [
            'customers' => $customers
        ]);
    }

    /**
     * @Route("/edit/{customer}", name="edit", options={"expose"=true})
     */
    public function edit(
        CustomerService $customerService,
        CountryRepository $countryRepo,
        Customer $customer
    ): Response {
        $customer = $customerService->getCustomerAsArray($customer);

        return $this->render('customer/edit.html.twig', [
            'customer' => $customer,
            'locations' => $countryRepo->findAllAsArray(),
        ]);
    }
}
