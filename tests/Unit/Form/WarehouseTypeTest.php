<?php

namespace App\Tests\Unit\Form;

use App\Entity\Warehouse;
use App\Form\WarehouseType;
use Symfony\Component\Form\Test\TypeTestCase;

class WarehouseTypeTest extends TypeTestCase
{
    public function testSomething()
    {
        $formData = [
            'name' => 'TEST-WAREHOUSE-01',
        ];
        $objectToCompare = new Warehouse();
        $form = $this->factory->create(WarehouseType::class, $objectToCompare);

        $warehouse = new Warehouse();
        $warehouse->setName('TEST-WAREHOUSE-01');
        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertSame($warehouse, $objectToCompare);
    }
}
