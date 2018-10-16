import React, { Component } from 'react';
import Modal from 'react-bootstrap4-modal';
import axios from 'axios';

class View extends Component {
  constructor(props) {
    super(props);

    this.state = {
      currentText: '',
      products: [],
      warehouse: null,
      showConfirm: false,
      warehouses: [],
      sending: false,
    };
    this.removeProduct = this.removeProduct.bind(this);
    this.addProduct = this.addProduct.bind(this);
    this.handleWarehouse = this.handleWarehouse.bind(this);
    this.toUpdateQuantities = this.toUpdateQuantities.bind(this);
  }

  componentDidMount() {
    axios.get(Routing.generate('warehouse_all')).then(res => res.data).then(
      (result) => {
        if (result.length <= 0) {
          throw new Error('The number of warehouses is 0, please add another Warehouse');
        }
        this.setState({
          warehouses: result,
        });
      },
    );
  }

  handleWarehouse(e) {
    this.setState({
      warehouse: e.target.value,
    });
  }

  addProduct() {
    const { products, currentText } = this.state;
    if (currentText !== '') {
      products.push({
        code: currentText,
      });
      this.setState({
        products,
        currentText: '',
      });
    }
  }

  removeProduct(code) {
    const { products } = this.state;
    const filtered = products.filter(item => (
      item.code === code
    ));
    this.setState({
      products: filtered,
    });
  }

  currentText(e) {
    this.setState({
      currentText: e.target.value,
    });
  }

  addProductKeyPressHandler(e) {
    if (e.key === 'Enter') {
      this.addProduct();
      e.preventDefault();
    }
  }

  toUpdateQuantities() {
    const { products, warehouse } = this.state;
    this.setState({
      sending: true,
    });
    axios.post(Routing.generate('product_bar_code_save', { warehouse }), {
      data: products,
    }).then(res => res.data).then(() => {
      this.setState({
        showConfirm: false,
        sending: false,
        products: [],
      });
    });
  }

  render() {
    const {
      products, currentText, warehouse, showConfirm, warehouses, sending
    } = this.state;
    return (
      <div>
        <div className="row">
          <div className="col-md-12">
            <form>
              <p>
                {Translator.trans('product.update.bar-code.description')}
              </p>
              <div className="form-inline">
                <input
                  type="text"
                  className="form-control form-control-sm my-1 mr-sm-2"
                  placeholder={Translator.trans('product.update.bar-code.bar_code')}
                  value={currentText}
                  onChange={e => this.currentText(e)}
                  onKeyPress={e => this.addProductKeyPressHandler(e)}
                />
                <button type="button" className="btn btn-primary btn-sm my-2" onClick={this.addProduct}>
                  {Translator.trans('product.update.bar-code.add_action')}
                </button>
              </div>

              <table className="table table-sm">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Code</th>
                    <th scope="col">Options</th>
                  </tr>
                </thead>
                <tbody>
                  {products.map((item, key) => (
                    <tr key={item.code}>
                      <th scope="row">{key + 1}</th>
                      <td width="75%">
                        {item.code}
                      </td>
                      <td>
                        <button
                          type="button"
                          className="btn btn-sm btn-danger"
                          onClick={e => this.removeProduct(e, item.code)}
                        >
                          <i className="fas fa-trash-alt" />
                        </button>
                      </td>
                    </tr>
                  ))}
                  { products.length === 0
                  && (
                    <tr>
                      <td colSpan="3" className="text-center">
                        {Translator.trans('product.update.bar-code.no_products')}
                      </td>
                    </tr>
                  )
                }
                </tbody>
              </table>

              <div className="form-group">
                <label htmlFor="destinationWarehouse">{Translator.trans('product.update.bar-code.destination')}</label>
                <select className="form-control form-control-sm" onChange={this.handleWarehouse}>
                  <option key={0} defaultValue>{Translator.trans('product.update.bar-code.select_some_warehouse')}</option>
                  {warehouses.map(item => (
                    <option value={item.id} key={item.id}>{item.name}</option>
                  ))}
                </select>
              </div>

              <div className="form-inline">
                {warehouse === null || products.length === 0 ? (
                  <button type="button" className="btn btn-primary my-2 disabled" onClick={e => (e.preventDefault())}>
                    {Translator.trans('product.update.bar-code.upload')}
                  </button>
                ) : (
                  <button type="button" className="btn btn-primary my-2" onClick={() => (this.setState({ showConfirm: true }))}>
                    {Translator.trans('product.update.bar-code.upload')}
                  </button>
                )}
              </div>
            </form>
          </div>
        </div>

        {showConfirm && (
          <Modal visible onClickBackdrop={this.modalBackdropClicked} dialogClassName="modal-lg">
            <div className="modal-header">
              <h5 className="modal-title">{Translator.trans('product.update.bar-code.confirm.title')}</h5>
            </div>
            <div className="modal-body">
              <div className="row">
                <div className="col-md-12">
                  {Translator.trans('product.update.bar-code.confirm.body', { warehouse })}
                  <hr />
                  <table className="table table-sm">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">Code</th>
                      </tr>
                    </thead>
                    <tbody>
                      {products.map((item, key) => (
                        <tr key={item.code}>
                          <td>
                            {key + 1}
                          </td>
                          <td width="75%">
                            {item.code}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div className="modal-footer">
              <button type="button" className="btn btn-danger">
                {Translator.trans('cancel')}
              </button>
              {sending ? (
                <button type="button" className="btn btn-primary disabled">
                  {Translator.trans('product.update.bar-code.confirm.action_doing')}
                  &nbsp;
                  <i className="fas fa-sync fa-spin" />
                </button>
              ) : (
                <button type="button" className="btn btn-primary" onClick={this.toUpdateQuantities}>
                  {Translator.trans('product.update.bar-code.confirm.action')}
                </button>
              )}
            </div>
          </Modal>
        )}
      </div>
    );
  }
}

export default View;
