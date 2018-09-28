<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/product", name="product")
 */
class ProductController extends AbstractController
{

    /**
     * @Route("/", name="product_index")
     */
    public function index(ProductRepository $productRepo): Response
    {
        $products = $productRepo->findAll();
        dd($products);
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }
}
