import ReactDOM from 'react-dom';
import React from 'react';
import CustomerEditForm from './Edit/CustomerEditForm';

const container = document.getElementById('edit-customer');
ReactDOM.render(<CustomerEditForm
  customer={JSON.parse(container.dataset.customer)}
  locations={JSON.parse(container.dataset.locations)}
/>, container);
