<?php

namespace App\Controller;

use App\Entity\Warehouse;
use App\Form\WarehouseType;
use App\Repository\WarehouseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/warehouse", name="warehouse")
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
     * @Route("/edit/{warehouse}", name="warehouse_edit")
     */
    public function edit(Warehouse $warehouse): Response
    {
        $form =  $this->createForm(WarehouseType::class, $warehouse);
        return $this->render('warehouse/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
