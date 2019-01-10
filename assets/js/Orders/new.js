import ReactDOM from 'react-dom';
import React from 'react';
import CreateOrder from './New/CreateOrder';

const container = document.getElementById('react-component');
ReactDOM.render(<CreateOrder locations={JSON.parse(container.dataset.locations)} />, container);
