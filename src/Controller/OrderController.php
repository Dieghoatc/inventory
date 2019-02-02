<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Warehouse;
use App\Repository\CountryRepository;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use App\Repository\WarehouseRepository;
use App\Services\CommentService;
use App\Services\OrderService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order", name="order_")
 */
class OrderController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->render('order/index.html.twig');
    }

    /**
     * @Route("/new", name="new", options={"expose"=true})
     * @IsGranted("ROLE_CAN_MANAGE_ORDERS")
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
     * @IsGranted("ROLE_CAN_MANAGE_ORDERS")
     */
    public function create(
        Request $request,
        OrderService $orderService
    ): Response {
        $orderData = json_decode($request->getContent(), true);
        $orderService->add($orderData, $this->getUser());

        $response = new Response();
        $response->setContent(json_encode(['status' => 'ok', 'route' => $this->generateUrl('order_index')]));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/all/{warehouse}", name="all", options={"expose"=true}, defaults={"status"=1}, methods={"get"})
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

        if (!is_array($content)) {
            throw new \LogicException('Invalid content request format.');
        }

        $commentService->syncComments($content['comments'], $user, $order);
        $response = new Response(json_encode($commentService->getOrderComments($order)));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/pdf/{order}", name="pdf", options={"expose"=true})
     */
    public function pdf(
        Order $order
    ): Response {
        $pdfOptions = new Options();


        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('order/pdf.html.twig', [
            'order' => $order
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $response = new Response();
        $response->setContent($dompdf->output());
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/pdf');
        return $response;
    }

}
