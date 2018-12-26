import 'react-table/react-table.css';
import React, { Component } from 'react';
import axios from 'axios';
import { first } from 'lodash';
import ReactTable from 'react-table';
import moment from 'moment';
import checkboxHOC from 'react-table/lib/hoc/selectTable';

const CheckboxTable = checkboxHOC(ReactTable);

class Orders extends Component {
  constructor(props) {
    super(props);
    this.state = {
      order: null,
    };
  }

  detail() {
    console.log('editing');
  }

  render() {
    return (
      <div>
        <div className="row">
          <div className="col-md-6">
            <select className="form-control" onChange={e => this.loadOrders(e.target.value)}>
            </select>
          </div>
        </div>
        <hr />
        <CheckboxTable
          data={orders}
          defaultFilterMethod={(filter, row) => {
            const id = filter.pivotId || filter.id;
            return (
              row[id] !== undefined
                ? String(row[id].toLowerCase()).startsWith(filter.value.toLowerCase()) : true
            );
          }}
          columns={columns}
          loading={loading}
          defaultPageSize={10}
          filterable
          className="-striped -highlight"
          {...checkboxProps}
          keyField="id"
        />
      </div>
    );
  }
}

export default Orders;
