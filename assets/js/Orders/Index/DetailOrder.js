import React, { Component } from 'react';
import Modal from 'react-bootstrap4-modal';
import ReactTable from 'react-table';

class DetailOrder extends Component {
  constructor(props) {
    super(props);
    this.state = {
      showDetailModal: false,
      loading: false,
      data: [],
    };
  }

  render() {
    const {
      showDetailModal, data, loading,
    } = this.state;

    const columns = [{
      Header: Translator.trans('product.template.code'),
      accessor: 'code',
    }, {
      Header: Translator.trans('product.template.description'),
      accessor: 'title',
    }, {
      Header: Translator.trans('product.template.quantity'),
      accessor: 'quantity',
      Cell: this.renderEditable,
    }, {
      Header: Translator.trans('product.template.warehouse'),
      accessor: 'warehouse.name',
    }];

    return (
      <Modal visible dialogClassName="modal-lg" isOpen={showDetailModal}>
        <div className="modal-header">
          <h5 className="modal-title">{Translator.trans('product.index.move_between_warehouses')}</h5>
        </div>
        <div className="modal-body">
          <div className="row">
            <div className="col-md-6">
              {Translator.trans('product.index.destination_warehouse')}
            </div>
          </div>
          <hr />
          <ReactTable data={data} columns={columns} defaultPageSize={5} loading={loading} />
        </div>
        <div className="modal-footer">
          <button type="button" className="btn btn-primary" onClick={this.close}>
            {Translator.trans('close')}
          </button>
        </div>
      </Modal>
    );
  }
}

export default DetailOrder;
