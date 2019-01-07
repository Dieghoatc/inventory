import React, { Component } from 'react';
import Modal from 'react-bootstrap4-modal';
import ReactTable from 'react-table';
import PropTypes from 'prop-types';
import axios from 'axios';
import moment from 'moment';

class DetailOrder extends Component {
  constructor(props) {
    super(props);

    this.state = {
      loading: false,
      products: [],
      order: [],
    };
  }

  componentWillMount() {
    const { orderDetailId } = this.props;
    axios.get(Routing.generate('order_detail', { order: orderDetailId }))
      .then((response) => {
        console.log(response.data)
        this.setState({
          products: response.data.products,
          order: response.data.order,
        });
      });
  }

  updateComment(id) {
    console.log('updating comment');
  }

  deleteComment(id) {
    const { order } = this.state;
    order.comments.push({
      id: null,
      content: null,
    });

    this.setState({
      order,
    });
  }

  saveComment() {
    console.log('save comment');
  }

  addComment() {
    const { order } = this.state;
    order.comments.push({
      id: null,
      content: null,
    });

    this.setState({
      order,
    });
  }

  render() {
    const { products, order, loading } = this.state;
    const { closeModal } = this.props;
    const columns = [{
      Header: Translator.trans('product.template.code'),
      accessor: 'product.code',
    }, {
      Header: Translator.trans('product.template.description'),
      accessor: 'product.title',
    }, {
      Header: Translator.trans('product.template.quantity'),
      accessor: 'quantity',
      Cell: this.renderEditable,
    }];
    let comments = [];
    if (order.comments !== undefined) {
      comments = order.comments.map((comment, index) => (
        <div className="form-inline" key={`comment-${index}`}>
          <div className="form-group mb-2 col-md-10">
            <textarea defaultValue={comment.content} className="form-control col-md-12" />
          </div>
          <button type="button" className="btn btn-sm btn-primary m-1">
            <i className="fas fa-save" />
          </button>
          { ' ' }
          <button type="button" className="btn btn-sm btn-danger m-1">
            <i className="fas fa-times" />
          </button>
          { ' ' }
        </div>
      ));
      console.log(comments);
    }

    return (
      <Modal dialogClassName="modal-lg" visible>
        <div className="modal-header">
          <h5 className="modal-title">{Translator.trans('order.index.detail')}</h5>
        </div>
        <div className="modal-body">
          { order.customer !== undefined
          && <div className="row">
            <div className="col-md-12">
              <span>
                { Translator.trans('order.index.source') }
                { ':' }
                <strong>
                  { ' ' }
                  { Translator.trans(`order_statuses.${order.source}`) }
                </strong>
              </span>
              { ' ' }
              <span>
                { Translator.trans('order.index.status') }
                { ':' }
                <strong>
                  { ' ' }
                  { Translator.trans(`order_statuses.${order.status}`) }
                </strong>
              </span>
            </div>
            <div className="col-md-12">
              <span>
                { Translator.trans('order.index.customer') }
                { ':' }
                <strong>
                  { ' ' }
                  { order.customer.firstName }
                  { order.customer.lastName }
                </strong>
              </span>
              { ' ' }
              <span>
                { Translator.trans('order.index.email') }
                { ':' }
                <strong>
                  { ' ' }
                  { order.customer.email }
                </strong>
              </span>
            </div>
            <div className="col-md-12">
              <span>
                { Translator.trans('order.index.code') }
                { ':' }
                <strong>
                  { ' ' }
                  { order.code }
                </strong>
              </span>
              { ' ' }
              <span>
                { Translator.trans('order.index.created_at') }
                { ':' }
                <strong>
                  { ' ' }
                  { moment(order.customer.createdAtAsIso8601).format('MMMM D, YYYY') }
                </strong>
              </span>
            </div>
            <div className="col-md-12">
              <span>
                { Translator.trans('order.index.address') }
                { ':' }
                <strong>
                  { ' ' }
                  { order.customer.defaultAddress.address }
                </strong>
              </span>
              { ' ' }
              <span>
                { Translator.trans('order.index.zip_code') }
                { ':' }
                <strong>
                  { ' ' }
                  { order.customer.defaultAddress.zipCode }
                </strong>
              </span>
            </div>
            <div className="col-md-12">
              <span>
                { Translator.trans('order.index.city') }
                { ':' }
                <strong>
                  { ' ' }
                  { order.customer.defaultAddress.city.name }
                </strong>
              </span>
              { ' ' }
              <span>
                { Translator.trans('order.index.state') }
                { ':' }
                <strong>
                  { ' ' }
                  { order.customer.defaultAddress.city.state.name }
                </strong>
              </span>
              { ' ' }
              <span>
                { Translator.trans('order.index.country') }
                { ':' }
                <strong>
                  { ' ' }
                  { order.customer.defaultAddress.city.state.country.name }
                </strong>
              </span>
            </div>
          </div>
          }
          <hr />

          <ul className="nav nav-tabs" id="myTab" role="tablist">
            <li className="nav-item">
              <a
                className="nav-link active"
                id="products-detail-tab"
                data-toggle="tab"
                href="#products-detail"
                role="tab"
                aria-controls="home"
                aria-selected="true"
              >
                {Translator.trans('order.index.order_products')}
              </a>
            </li>
            <li className="nav-item">
              <a
                className="nav-link"
                id="order-detail-tab"
                data-toggle="tab"
                href="#order-comments"
                role="tab"
                aria-controls="profile"
                aria-selected="false"
              >
                {Translator.trans('order.index.order_comments')}
              </a>
            </li>
          </ul>
          <div className="tab-content">
            <div
              className="tab-pane fade show active"
              id="products-detail"
              role="tabpanel"
              aria-labelledby="home-tab"
            >
              <ReactTable data={products} columns={columns} defaultPageSize={5} loading={loading} />
            </div>
            <div
              className="tab-pane fade"
              id="order-comments"
              role="tabpanel"
              aria-labelledby="profile-tab"
            >
              <hr />
              { order.comments !== undefined && comments }
              <div className="col-md-12">
                <button type="button" className="btn btn-sm btn-success" onClick={() => (this.addComment())}>
                  <i className="fas fa-plus" />
                </button>
              </div>
            </div>
          </div>
        </div>
        <div className="modal-footer">
          <button type="button" className="btn btn-primary" onClick={() => closeModal()}>
            {Translator.trans('close')}
          </button>
        </div>
      </Modal>
    );
  }
}

export default DetailOrder;

DetailOrder.propTypes = {
  orderDetailId: PropTypes.number.isRequired,
  closeModal: PropTypes.func.isRequired,
};
