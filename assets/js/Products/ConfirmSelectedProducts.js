import React, { Component } from 'react';
import Modal from 'react-bootstrap4-modal';
import ReactTable from 'react-table';

class ConfirmSelectedProducts extends Component {
  constructor(props) {
    super(props);
    this.state = {
      visible: props.visible,
      data: props.data,
    };
    this.close = this.close.bind(this);
    this.confirm = this.confirm.bind(this);
    this.renderEditable = this.renderEditable.bind(this);
  }

  close() {
    this.setState({
      visible: false,
    });
  }

  confirm() {
    console.log(this.state.data);
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
    const { visible, data } = this.state;
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
          <ReactTable data={data} columns={columns} defaultPageSize={5} />
        </div>
        <div className="modal-footer">
          <button type="button" className="btn btn-secondary" onClick={this.confirm}>
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
