<?php

namespace App\Controller;

use App\Entity\Warehouse;
use App\Form\UploadProductsType;
use App\Repository\ProductRepository;
use App\Services\ProductService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/product", name="product_")
 */
class ProductController extends AbstractController
{

    /**
     * @Route("/", name="product_index", methods={"get"})
     */
    public function index(): Response
    {
        return $this->render('product/index.html.twig');
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

        $writer = new Xls($template);
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

    /**
     * @Route("/all/{warehouse}", name="all", options={"expose"=true}, methods={"get"})
     */
    public function all(ProductRepository $productRepo, Warehouse $warehouse): Response
    {
        $products = $productRepo->findAllAsArray($warehouse);
        $response = new Response(json_encode($products));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/move/{warehouse}", name="move", options={"expose"=true}, methods={"post"})
     */
    public function move(ProductService $productService, Request $request, Warehouse $warehouse): Response
    {
        $products = json_decode($request->getContent(), true);
        if(!\is_array($products)){
            throw new BadRequestHttpException('Malformed JSON request');
        }

        $productService->moveProducts($products['data'], $warehouse);
        return new JsonResponse(['status' => 'ok']);
    }

    /**
     * @Route("/update/bar-code", name="bar_code", methods={"get"})
     */
    public function updateBarCode(): Response
    {
        return $this->render('product/bar-code.html.twig');
    }
}
