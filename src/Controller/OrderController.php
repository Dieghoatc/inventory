<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Warehouse;
use App\Form\OrderType;
use App\Repository\OrderRepository;
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
        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
        ]);
    }

    /**
     * @Route("/new", name="user_new")
     * @IsGranted("ROLE_CAN_MANAGE_ORDERS")
     */
    public function new(Request $request): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $em = $this->getDoctrine()->getManager();
            $em->persist($order);
            $em->flush();

            $this->addFlash('success', 'created_successfully');
            return $this->redirectToRoute('order_index');
        }

        return $this->render('order/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/all/{warehouse}", name="all", options={"expose"=true}, defaults={"status"=1}, methods={"get"})
     */
    public function all(
        OrderRepository $orderRepo,
        Warehouse $warehouse
    ): Response
    {
        $products = $orderRepo->findByWarehouse($warehouse);
        $response = new Response(json_encode($products));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
