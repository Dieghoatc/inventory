import ReactDOM from 'react-dom';
import React from 'react';
import ManageOrder from './New/ManageOrder';

const container = document.getElementById('react-component');
ReactDOM.render(<ManageOrder
  locations={JSON.parse(container.dataset.locations)}
  warehouses={JSON.parse(container.dataset.warehouses)}
  customers={JSON.parse(container.dataset.customers)}
/>, container);
