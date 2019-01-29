import 'react-table/react-table.css';
import React, { Component } from 'react';
import axios from 'axios';
import { first } from 'lodash';
import ReactTable from 'react-table';
import moment from 'moment';
import DetailOrder from './DetailOrder';


class Orders extends Component {
  constructor(props) {
    super(props);
    this.state = {
      warehouses: [],
      loading: true,
      orders: [],
      orderDetailId: null,
    };

    this.detail = this.detail.bind(this);
    this.closeDetailModal = this.closeDetailModal.bind(this);
  }

  componentDidMount() {
    axios.get(Routing.generate('warehouse_all')).then(res => res.data).then(
      (result) => {
        if (result.length === 0) {
          throw new Error('The number of warehouses is 0, please add another Warehouse');
        }

        const warehouse = first(result);
        this.setState({
          warehouses: result,
        });
        this.loadOrders(warehouse.id);
      },
    );
  }

  loadOrders(warehouse) {
    this.setState({
      loading: true,
    });
    axios.get(Routing.generate('order_all', { warehouse })).then(res => res.data).then(
      (result) => {
        this.setState({
          orders: result,
          loading: false,
        });
      },
    );
  }

  detail(orderId) {
    this.setState({
      orderDetailId: orderId,
    });
  }

  closeDetailModal() {
    this.setState({
      orderDetailId: null,
    });
  }

  render() {
    const {
      selectAll, orders, warehouses, loading, orderDetailId,
    } = this.state;

    const { toggleSelection, toggleAll, isSelected } = this;
    const columns = [{
      Header: Translator.trans('order.index.customer'),
      Cell: row => `${row.original.customer.firstName} ${row.original.customer.lastName} [${row.original.customer.email}]`,
    }, {
      Header: Translator.trans('order.index.code'),
      accessor: 'code',
    }, {
      Cell: (row) => {
        switch (row.original.source) {
          case 1:
            return Translator.trans('order.index.sources.web');
          case 2:
            return Translator.trans('order.index.sources.phone');
          default:
            return Translator.trans('order.index.sources.unknown');
        }
      },
      Header: Translator.trans('order.index.source'),
    }, {
      Cell: (row) => {
        switch (row.original.status) {
          case 1:
            return Translator.trans('order.index.statuses.created');
          case 2:
            return Translator.trans('order.index.statuses.invoiced');
          case 3:
            return Translator.trans('order.index.statuses.ready_to_send');
          default:
            return Translator.trans('order.index.statuses.unknown');
        }
      },
      Header: Translator.trans('order.index.status'),
    }, {
      Cell: row => (moment(row.original.created_at).format('DD MMM YYYY')),
      Header: Translator.trans('order.index.date'),
    }, {
      Cell: row => (
        <div>
          <button type="button" className="btn btn-sm btn-success" onClick={() => this.detail(row.original.id)}>
            {Translator.trans('order.index.detail')}
          </button>
          { ' ' }
          <a href={Routing.generate('order_pdf', { order: row.original.id })} className="btn btn-sm btn-success" target="_blank" rel="noopener noreferrer">
            <i className="fas fa-file-pdf" />
          </a>
        </div>
      ),
      Header: Translator.trans('order.index.options'),
    }];
    const checkboxProps = {
      selectAll,
      isSelected,
      toggleSelection,
      toggleAll,
      selectType: 'checkbox',
    };
    return (
      <div>
        <div className="row">
          <div className="col-md-6">
            <select className="form-control" onChange={e => this.loadOrders(e.target.value)}>
              {warehouses.map(item => (
                <option
                  value={item.id}
                  key={item.id}
                >
                  {item.name}
                </option>
              ))}
            </select>
          </div>
          <div className="col-md-6">
            <a
              className="btn btn-success"
              href={Routing.generate('order_new')}
            >
              {Translator.trans('order.index.new')}
            </a>
          </div>
        </div>
        <hr />
        <ReactTable
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
        { orderDetailId !== null
          && <DetailOrder orderDetailId={orderDetailId} closeModal={this.closeDetailModal} />
        }
      </div>
    );
  }
}

export default Orders;
