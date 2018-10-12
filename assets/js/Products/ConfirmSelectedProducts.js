import React, { Component } from 'react';
import Modal from 'react-bootstrap4-modal';
import ReactTable from 'react-table';
import axios from 'axios';

class ConfirmSelectedProducts extends Component {
  constructor(props) {
    super(props);
    const { visible, data } = this.props;
    this.state = {
      visible,
      data,
      warehouses: [],
      loading: true,
      warehouseSelected: null,
    };
    this.close = this.close.bind(this);
    this.moveProducts = this.moveProducts.bind(this);
    this.renderEditable = this.renderEditable.bind(this);
    this.selectDestinationWarehouse = this.selectDestinationWarehouse.bind(this);
  }

  componentDidMount() {
    const { currentWarehouse } = this.props;
    axios.get(Routing.generate('warehouse_all')).then(res => res.data).then(
      (result) => {
        const warehouses = result.filter(item => (item.id !== currentWarehouse));
        if (warehouses.length <= 0) {
          throw new Error('The number of warehouses is 0, please add another Warehouse');
        }
        this.setState({
          loading: false,
          warehouses,
          warehouseSelected: warehouses[0].id,
        });
      },
    );
  }

  close() {
    this.setState({
      visible: false,
    });
  }

  moveProducts() {
    const { data, warehouseSelected } = this.state;
    axios.post(Routing.generate('product_move', { warehouse: warehouseSelected }), {
      data,
    }).then((response) => {
      console.log(response);
    });
  }

  selectDestinationWarehouse(id) {
    this.setState({
      warehouseSelected: id,
    });
  }

  renderEditable(cellInfo) {
    return (
      <div
        style={{ backgroundColor: '#fafafa' }}
        contentEditable
        suppressContentEditableWarning
        onBlur={(e) => {
          const data = [...this.state.data];
          data[cellInfo.index][cellInfo.column.id] = e.target.innerHTML;
          this.setState({ data });
        }}
        dangerouslySetInnerHTML={{
          __html: this.state.data[cellInfo.index][cellInfo.column.id],
        }}
      />
    );
  }

  render() {
    const {
      visible, data, loading, warehouses,
    } = this.state;
    const columns = [{
      Header: 'Code',
      accessor: 'code',
    }, {
      Header: 'Description',
      accessor: 'title',
    }, {
      Header: 'Quantity',
      accessor: 'quantity',
      Cell: this.renderEditable,
    }, {
      Header: 'Warehouse',
      accessor: 'warehouse.name',
    }];
    return (
      <Modal visible={visible} onClickBackdrop={this.modalBackdropClicked} dialogClassName="modal-lg">
        <div className="modal-header">
          <h5 className="modal-title">{Translator.trans('product.index.move_between_warehouses')}</h5>
        </div>
        <div className="modal-body">
          <div className="row">
            <div className="col-md-6">
              {Translator.trans('product.index.destination_warehouse')}
            </div>
            <div className="col-md-6">
              <select className="form-control" onChange={this.selectDestinationWarehouse}>
                {warehouses.map(item => (
                  <option value={item.id} key={item.id}>{item.name}</option>
                ))}
              </select>
            </div>
          </div>
          <hr />
          <ReactTable data={data} columns={columns} defaultPageSize={5} loading={loading} />
        </div>
        <div className="modal-footer">
          <button type="button" className="btn btn-secondary" onClick={this.moveProducts}>
            {Translator.trans('move')}
          </button>
          <button type="button" className="btn btn-primary" onClick={this.close}>
            {Translator.trans('close')}
          </button>
        </div>
      </Modal>
    );
  }
}

export default ConfirmSelectedProducts;
