import ReactDOM from 'react-dom';
import React from 'react';
import CreateOrder from './New/CreateOrder';

const container = document.getElementById('react-component');
ReactDOM.render(<CreateOrder
  locations={JSON.parse(container.dataset.locations)}
  warehouses={JSON.parse(container.dataset.warehouses)}
  customers={JSON.parse(container.dataset.customers)}
/>, container);
