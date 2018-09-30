<?php

namespace App\Controller;

use App\Form\UploadProductsType;
use App\Repository\ProductRepository;
use App\Services\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/product", name="product")
 */
class ProductController extends AbstractController
{

    /**
     * @Route("/", name="product_index", methods={"get"})
     */
    public function index(ProductRepository $productRepo): Response
    {
        $products = $productRepo->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * @Route("/upload", name="product_upload", methods={"get", "post"})
     */
    public function upload(Request $request, ProductService $productService): Response
    {
        $form = $this->createForm(UploadProductsType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->getData()['products'];
            $productService->processXls($uploadedFile);
        }

        return $this->render('product/upload.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
