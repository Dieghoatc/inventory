<?php

namespace App\Controller;

use App\Form\UploadProductsType;
use App\Repository\ProductRepository;
use App\Services\ProductService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use PhpOffice\PhpSpreadsheet\Writer as Writer;

/**
 * @Route("/product", name="product_")
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
    public function upload(TranslatorInterface $translator, Request $request, ProductService $productService): Response
    {
        $form = $this->createForm(UploadProductsType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $productService->processXls($form->getData());
            $this->addFlash('success', $translator->trans('product.uploaded_successfully'));
            return $this->redirectToRoute('product_product_index');
        }
        return $this->render('product/upload.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/template", name="template", methods={"get"})
     */
    public function uploadProductsTemplate(TranslatorInterface $translator): Response
    {
        $template = new Spreadsheet();
        $template->getActiveSheet()->setCellValue('A1', $translator->trans('product.template.code'));
        $template->getActiveSheet()->setCellValue('B1', $translator->trans('product.template.title'));
        $template->getActiveSheet()->setCellValue('C1', $translator->trans('product.template.quantity'));

        $writer = new Writer\Xls($template);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );

        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$translator->trans('product.template.products').'.xls"');
        $response->headers->set('Cache-Control','max-age=0');
        return $response;
    }

}
