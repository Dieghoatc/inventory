import React, { Component } from 'react';
import Modal from 'react-bootstrap4-modal';
import ReactTable from 'react-table';
import axios from 'axios';

class ConfirmSelectedProducts extends Component {
  constructor(props) {
    super(props);
    const { data, warehouseSelected, warehouses } = this.props;
    this.state = {
      data,
      dataRequest: {},
      warehouses,
      warehouseSelected,
      destinationWarehouse: null,
      loading: false,
      sending: false,
    };
    this.close = this.close.bind(this);
    this.moveProducts = this.moveProducts.bind(this);
    this.renderEditable = this.renderEditable.bind(this);
    this.selectDestinationWarehouse = this.selectDestinationWarehouse.bind(this);
  }

  close() {
    const { closeModal } = this.props;
    closeModal('confirmModal', false);
  }

  moveProducts() {
    const { dataRequest, destinationWarehouse } = this.state;
    axios.post(Routing.generate('product_move', { warehouse: destinationWarehouse }), {
      data: dataRequest,
    }).then(res => res.data).then(() => {
      this.close();
    });

    this.setState({
      sending: true,
    });
  }

  selectDestinationWarehouse(id) {
    this.setState({
      destinationWarehouse: id,
    });
  }

  renderEditable(cellInfo) {
    const { data, dataRequest } = this.state;
    const options = [];
    console.log(data);

    for (let i = 1; i <= data[cellInfo.index][cellInfo.column.id]; i += 1) {
      if (typeof dataRequest[data[cellInfo.index].uuid] === 'undefined' && i === 1) {
        dataRequest[data[cellInfo.index].uuid] = {
          uuid: data[cellInfo.index].uuid,
          quantity: Number(i),
        };
      }
      if (dataRequest[cellInfo.index] && dataRequest[cellInfo.index].uuid.quantity === i) {
        options.push(<option value={i} key={`${i}-KEY`} defaultValue>{i}</option>);
      } else {
        options.push(<option value={i} key={`${i}-KEY`}>{i}</option>);
      }
    }

    return (
      cellInfo.original.quantity > 0 ?
      <select
        className="form-control form-control-sm"
        onChange={(e) => {
          dataRequest[data[cellInfo.index].uuid] = {
            uuid: data[cellInfo.index].uuid,
            quantity: Number(e.target.value),
          };
          this.setState({ dataRequest });
        }}
      >
        {options}
      </select> :
      <span>{Translator.trans('product.template.no_available_product')}</span>
    );
  }

  render() {
    const {
      data, loading, warehouses, sending, warehouseSelected,
    } = this.state;
    console.log(warehouseSelected);
    console.log(warehouseSelected);
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
      <Modal visible onClickBackdrop={this.modalBackdropClicked} dialogClassName="modal-lg">
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
                {warehouses.map((item) => {
                  if (item.id !== warehouseSelected) {
                    return (<option value={item.id} key={item.id}>{item.name}</option>);
                  }
                  return false;
                })}
              </select>
            </div>
          </div>
          <hr />
          <ReactTable data={data} columns={columns} defaultPageSize={5} loading={loading} />
        </div>
        <div className="modal-footer">
          { !sending
            ? (
              <button type="button" className="btn btn-secondary" onClick={this.moveProducts}>
                {Translator.trans('move')}
              </button>
            ) : (
              <button type="button" className="btn btn-secondary disabled">
                <i className="fas fa-sync fa-spin">&nbsp;</i>
                {Translator.trans('moving')}
              </button>
            )
          }
          <button type="button" className="btn btn-primary" onClick={this.close}>
            {Translator.trans('close')}
          </button>
        </div>
      </Modal>
    );
  }
}

export default ConfirmSelectedProducts;
