<?php

namespace App\Controller;

use App\Entity\Warehouse;
use App\Form\WarehouseType;
use App\Repository\WarehouseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/warehouse", name="warehouse_")
 */
class WarehouseController extends AbstractController
{
    /**
     * @Route("/", name="warehouse_index")
     */
    public function index(WarehouseRepository $warehouseRepo): Response
    {
        $warehouses = $warehouseRepo->findAll();
        return $this->render('warehouse/index.html.twig', [
            'warehouses' => $warehouses
        ]);
    }

    /**
     * @Route("/edit/{warehouse}", name="warehouse_edit", methods={"post", "get"})
     */
    public function edit(Request $request, Warehouse $warehouse): Response
    {
        $form = $this->createForm(WarehouseType::class, $warehouse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'updated_successfully');
            return $this->redirectToRoute('warehouse_warehouse_index');
        }

        return $this->render('warehouse/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/all", name="all", methods={"get"}, options={"expose"=true})
     */
    public function all(WarehouseRepository $warehouseRepo) {
        $warehouses = $warehouseRepo->findAllAsArray();
        return new JsonResponse($warehouses);
    }
}
