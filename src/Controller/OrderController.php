<?php

namespace App\Controller;

use App\DataProviders\WooCommerceProvider;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Entity\Warehouse;
use App\Repository\CountryRepository;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use App\Repository\WarehouseRepository;
use App\Services\CommentService;
use App\Services\LogService;
use App\Services\OrderService;
use Doctrine\Common\Persistence\ObjectManager;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

/**
 * @Route("/order", name="order_")
 */
class OrderController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @IsGranted("ROLE_UPDATE_ORDERS")
     */
    public function index(): Response
    {
        return $this->render('order/index.html.twig');
    }

    /**
     * @Route("/new", name="new", options={"expose"=true})
     * @IsGranted("ROLE_UPDATE_ORDERS")
     */
    public function new(
        CountryRepository $countryRepo,
        WarehouseRepository $warehouseRepo,
        CustomerRepository $customerRepo
    ): Response {
        return $this->render('order/new.html.twig', [
            'locations' => $countryRepo->findAllAsArray(),
            'warehouses' => $warehouseRepo->findAllAsArray(),
            'customers' => $customerRepo->findAllAsArray(),
        ]);
    }

    /**
     * @Route("/create", name="create", options={"expose"=true})
     * @IsGranted("ROLE_UPDATE_ORDERS")
     */
    public function create(
        Request $request,
        OrderService $orderService,
        LogService $logService
    ): Response {
        $orderData = json_decode($request->getContent(), true);
        $order = $orderService->add($orderData, $this->getUser());

        $logService->add('Order','Order created', $order);

        $response = new Response();
        $response->setContent(json_encode(['status' => 'ok', 'route' => $this->generateUrl('order_index')]));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/all/{warehouse}", name="all", options={"expose"=true}, defaults={"status"=1}, methods={"get"})
     * @IsGranted("ROLE_CAN_READ_ORDERS")
     */
    public function all(
        OrderRepository $orderRepo,
        Warehouse $warehouse
    ): Response {
        $products = $orderRepo->findByWarehouse($warehouse);
        $response = new Response(json_encode($products));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/detail/{order}", name="detail", options={"expose"=true}, methods={"get"})
     * @IsGranted("ROLE_CAN_READ_ORDERS")
     */
    public function detail(
        Order $order,
        OrderService $orderService
    ): Response {
        $result = $orderService->getOrder($order);
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/sync-comments/{order}", name="sync_comments", options={"expose"=true}, methods={"post"})
     */
    public function syncComments(
        Order $order,
        CommentService $commentService,
        Request $request
    ): Response {
        $user = $this->getUser();
        $content = json_decode($request->getContent(), true);

        if (!\is_array($content)) {
            throw new \LogicException('Invalid content request format.');
        }

        $commentService->syncComments($content['comments'], $user, $order);
        $response = new Response(json_encode($commentService->getOrderComments($order)));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/pdf/{order}", name="pdf", options={"expose"=true})
     * @IsGranted("ROLE_CAN_READ_ORDERS")
     */
    public function pdf(
        Order $order
    ): Response {
        $orderAsPdf = new Dompdf();
        $html = $this->renderView('order/pdf.html.twig', [
            'order' => $order,
        ]);
        $orderAsPdf->loadHtml($html);
        $orderAsPdf->setPaper('letter', 'portrait');
        $orderAsPdf->render();

        $response = new Response();
        $response->setContent($orderAsPdf->output());
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/pdf');

        return $response;
    }

    /**
     * @Route("/edit/status/{order}/{status}", name="change_status", methods={"post"}, options={"expose"=true})
     * @IsGranted("ROLE_UPDATE_ORDERS")
     */
    public function changeStatus(
        Order $order,
        int $status,
        ObjectManager $manager,
        LogService $logService
    ): Response {
        $order->setStatus($status);
        $manager->persist($order);
        $manager->flush();

        $logService->add('Order',"Order {$order->getCode()} status was changed.");
        return new JsonResponse(['status' => 'ok']);
    }


    /**
     * @Route("/sync", name="sync_orders", options={"expose"=true})
     * @IsGranted("ROLE_CAN_SYNC_ORDERS")
     */
    public function syncRemoteOrders(
        WooCommerceProvider $woocommerceProvider
    ): Response {
        $woocommerceProvider->syncOrders($this->getUser());

        return new JsonResponse(['status' => 'ok']);
    }

    /**
     * @Route("/delete", name="delete", options={"expose"=true}, methods={"DELETE"})
     * @IsGranted("ROLE_CAN_DELETE_ORDERS")
     */
    public function remove(
        Request $request,
        OrderService $orderService,
        OrderRepository $orderRepo,
        ValidatorInterface $validator,
        LogService $logService
    ): Response {
        $data = json_decode($request->getContent(), true);

        $constraint = new Assert\All(['constraints' => [
            'order' => new Assert\NotBlank(), 'token' => new Assert\NotBlank(),
        ]]);

        if ($validator->validate($data, $constraint)->count() > 0) {
            throw new \LogicException('Malformed request.');
        }

        if (!$this->isCsrfTokenValid('delete-order', $data['token'])) {
            throw new \LogicException('Token does not math with the expected one.');
        }

        $order = $orderRepo->find($data['order']);
        if(!$order instanceof Order) {
            throw new \InvalidArgumentException('Order was not found.');
        }

        $logService->add('Order',"Order {$order->getCode()} was deleted");
        $orderService->deleteOrder($order);

        return new JsonResponse(['status' => 'ok']);
    }

    /**
     * @Route("/xls/{order}", name="xls", methods={"get"}, options={"expose"=true})
     */
    public function uploadProductsTemplate(
        Order $order,
        TranslatorInterface $translator
    ): Response {
        $template = new Spreadsheet();
        $template->getActiveSheet()->setCellValue('A1', $translator->trans('order.xls.date'));
        $template->getActiveSheet()->setCellValue('B1', $translator->trans('order.xls.productCode'));
        $template->getActiveSheet()->setCellValue('C1', $translator->trans('order.xls.quantity'));

        $products = $order->getOrderProducts();
        $line = 2;
        foreach ($products as $product) {
            if(!$product->getProduct() instanceof Product) {
                throw new \InvalidArgumentException('Product not found.');
            }
            /** @var $product OrderProduct */
            $template->getActiveSheet()->setCellValue("A{$line}", $order->getCreatedAt()->format('Y-m-d'));
            $template->getActiveSheet()->setCellValue("B{$line}", $product->getProduct()->getCode());
            $template->getActiveSheet()->setCellValue("C{$line}", $product->getQuantity());
            $line++;
        }

        $writer = new Xls($template);
        $response = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );

        $filename = "{$translator->trans('product.xls.filename')}-{$order->getCode()}";
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$filename.'.xls"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
