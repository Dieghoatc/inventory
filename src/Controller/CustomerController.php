<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/customer", name="customer_")
 */
class CustomerController extends AbstractController
{
    /**
     * @Route("/search", name="customer", name="search", options={"expose"=true})
     */
    public function search(
        Request $request
    ): Response {
        $data = json_decode($request->getContent(), true);
        dd($data);
    }
}
