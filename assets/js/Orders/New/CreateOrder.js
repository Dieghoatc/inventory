import React, { Component } from 'react';
import PropTypes from 'prop-types';
import axios from 'axios';
import Select from 'react-select';

import CreatableSelect from 'react-select/lib/Creatable';

const isValidNewOption = (inputValue, selectValue, selectOptions) => (
  !(inputValue.trim().length === 0 || selectOptions.find(option => option.name === inputValue))
);

class CreateOrder extends Component {
  static isCurrentProductFilled(product) {
    return (product.quantity !== '' && product.uuid !== '');
  }

  static isAnyCustomerSelected(customer) {
    return (customer.id !== undefined && customer.id !== null);
  }

  constructor(props) {
    super(props);

    const { warehouses } = props;
    const customers = props.customers.map((customer) => {
      customer.value = (customer.id).toString();
      customer.label = `${customer.firstName} ${customer.lastName} [${customer.email}] [${customer.phone}]`;
      return customer;
    });

    const countries = props.locations.map((country) => {
      country.value = (country.id).toString();
      country.label = country.name;
      return country;
    });

    this.state = {
      sending: false,
      warehouses,
      customers,
      orderStates: [
        { name: Translator.trans('order_statuses.1'), id: 1 },
        { name: Translator.trans('order_statuses.2'), id: 2 },
        { name: Translator.trans('order_statuses.3'), id: 3 },
        { name: Translator.trans('order_statuses.4'), id: 4 },
        { name: Translator.trans('order_statuses.5'), id: 5 },
        { name: Translator.trans('order_statuses.6'), id: 6 },
      ],
      orderSources: [
        { name: Translator.trans('order_source.1'), id: 1 },
        { name: Translator.trans('order_source.2'), id: 2 },
      ],
      countries,
      productsByWarehouse: [],
      states: [],
      cities: [],
      order: {
        code: '',
        status: '',
        customer: {
          firstName: '',
          lastName: '',
          email: '',
          phone: '',
          addresses: [
            {
              address: '',
              zipCode: '',
              city: {
                state: {
                  country: {
                  },
                },
              },
            },
          ],
        },
        warehouse: {},
        products: [
          {
            uuid: '',
            quantity: '',
          },
        ],
      },
    };
  }

  setOrder(order) {
    this.setState({
      order,
    });
  }

  setOrderSource(el) {
    const sourceId = el.target.value;
    if (sourceId) {
      const { order } = this.state;
      order.source = Number(sourceId);
      this.setState({
        order,
      });
    }
  }

  setOrderState(el) {
    const stateId = el.target.value;
    if (stateId) {
      const { order } = this.state;
      order.status = Number(stateId);
      this.setState({
        order,
      });
    }
  }

  setProducts(products) {
    const { order } = this.state;
    order.products = products;
    this.setState({
      order,
    });
  }

  setWarehouse(el) {
    const orderId = el.target.value;
    this.getProductsByWarehouse(el);
    const { warehouses, order } = this.state;
    order.warehouse = warehouses.find(warehouseItem => (
      Number(warehouseItem.id) === Number(orderId)
    ));

    this.setState({
      order,
    });
  }

  setCustomer(customer) {
    const { order } = this.state;
    if (customer) {
      order.customer = customer;
    } else {
      order.customer = {
        firstName: '',
        lastName: '',
        email: '',
        phone: '',
        addresses: [
          {
            address: '',
            zipCode: '',
            city: {
              state: {
                country: {
                },
              },
            },
          },
        ],
      };
    }

    this.setOrder(order);
  }

  getProductsByWarehouse(el) {
    const warehouseId = el.target.value;
    if (warehouseId) {
      axios.get(Routing.generate('product_all', { warehouse: warehouseId }))
        .then((response) => {
          const productsByWarehouse = response.data.map((product) => {
            product.value = product.uuid;
            product.label = `${product.title} (${product.code})`;
            return product;
          });
          this.setState({
            productsByWarehouse,
          });
        });
    }
  }

  getCountry() {
    const { order } = this.state;
    const { customer } = order;
    if (customer.addresses.length > 0
      && customer.addresses[0].city
      && customer.addresses[0].city.state
      && customer.addresses[0].city.state.country
      && customer.addresses[0].city.state.country.name
    ) {
      return customer.addresses[0].city.state.country;
    }
    return '';
  }

  getState() {
    const { order } = this.state;
    const { customer } = order;
    if (customer.addresses.length > 0
      && customer.addresses[0].city
      && customer.addresses[0].city.state
      && customer.addresses[0].city.state.name
    ) {
      return customer.addresses[0].city.state;
    }
    return '';
  }

  getCity() {
    const { order } = this.state;
    const { customer } = order;
    if (customer.addresses.length > 0
      && customer.addresses[0].city
      && customer.addresses[0].city.name
    ) {
      return customer.addresses[0].city;
    }
    return '';
  }

  filterStates(countryId) {
    const states = [];
    if (countryId) {
      const { locations } = this.props;
      locations.forEach((locationItem) => {
        if (Number(locationItem.id) === Number(countryId)) {
          locationItem.states.forEach((state) => {
            states.push({
              cities: state.cities,
              name: state.name,
              id: (state.id).toString(),
            });
          });
        }
      });
    }

    this.setState({ states });
  }

  filterCities(stateId) {
    let cities = [];
    if (stateId) {
      const { states } = this.state;
      const state = states.find(stateItem => (Number(stateItem.id) === Number(stateId)));
      ({ cities } = state);
    }
    this.setState({ cities });
  }

  removeProduct(productUuid) {
    const { order } = this.state;
    const products = order.products.filter(product => (
      productUuid !== product.uuid
    ));
    this.setProducts(products);
  }

  addEmptyProduct() {
    const { order } = this.state;
    const { products } = order;
    products.push({
      uuid: '',
      quantity: '',
    });
    this.setProducts(products);
  }

  orderValidation() {
    const { order } = this.state;
    return order.customer !== ''
      && order.customer.firstName !== ''
      && order.customer.lastName !== ''
      && order.customer.email !== ''
      && order.warehouse && Object.keys(order.warehouse).length > 0
      && order.products && order.products.length > 0
      && order.products.some(product => (product.quantity !== '' && product.uuid !== ''))
      && order.source
      && order.status;
  }

  saveOrder() {
    const { order } = this.state;
    this.setState({
      sending: true,
    });
    axios.post(Routing.generate('order_create'), order).then((response) => {
      const destinationUrl = response.data.route;
      window.location.href = destinationUrl;
    });
  }

  render() {
    const {
      countries, states, cities, order, warehouses, orderStates, orderSources, productsByWarehouse,
      customers, sending,
    } = this.state;
    const { products, customer, code } = order;
    return (
      <div className="row">
        <div className="col-sm-6">
          <h4>{Translator.trans('order.new.customer_information')}</h4>

          <div className="form-group">
            <Select
              isClearable
              placeholder={Translator.trans('order.new.search_customer')}
              options={customers}
              value={customers.filter(customerItem => (
                customerItem.id === customer.id
              ))}
              onChange={(selectedCustomer) => {
                if (selectedCustomer) {
                  customer.id = selectedCustomer.id;
                }
                this.setCustomer(selectedCustomer);
              }}
            />
          </div>
          <div className="form-group">
            <div className="form-row">
              <div className="col-auto">
                <input
                  type="text"
                  className="form-control"
                  placeholder={Translator.trans('order.new.first_name')}
                  value={customer.firstName}
                  onChange={(e) => {
                    order.customer.firstName = e.target.value;
                    this.setOrder(order);
                  }}
                />
              </div>
              <div className="col-auto">
                <input
                  type="text"
                  className="form-control"
                  placeholder={Translator.trans('order.new.last_name')}
                  value={customer.lastName}
                  onChange={(e) => {
                    order.customer.lastName = e.target.value;
                    this.setOrder(order);
                  }}
                />
              </div>
            </div>
          </div>

          <div className="form-group">
            <input
              type="text"
              className="form-control"
              placeholder={Translator.trans('order.new.email')}
              value={customer.email}
              onChange={(e) => {
                order.customer.email = e.target.value;
                this.setOrder(order);
              }}
            />
          </div>

          <div className="form-group">
            <input
              type="text"
              className="form-control"
              placeholder={Translator.trans('order.new.phone')}
              value={customer.phone}
              onChange={(e) => {
                order.customer.phone = e.target.value;
                this.setOrder(order);
              }}
            />
          </div>

          <div className="form-group">
            <div className="form-row">
              <div className="col-md-auto">
                <input
                  type="text"
                  className="form-control"
                  placeholder={Translator.trans('order.new.address')}
                  value={customer.addresses[0].address}
                  onChange={(e) => {
                    order.customer.addresses[0].address = e.target.value;
                    this.setOrder(order);
                  }}
                />
              </div>
              <div className="col-md-auto">
                <input
                  type="text"
                  className="form-control"
                  placeholder={Translator.trans('order.new.zip_code')}
                  value={customer.addresses[0].zipCode}
                  onChange={(e) => {
                    order.customer.addresses[0].zipCode = e.target.value;
                    this.setOrder(order);
                  }}
                />
              </div>
            </div>
          </div>

          <div className="form-row">
            <div className="col-4">
              <CreatableSelect
                isClearable
                getOptionLabel={option => option.name}
                getOptionValue={option => option.id}
                getNewOptionData={(inputValue, optionLabel) => ({
                  id: null,
                  name: optionLabel,
                })}
                isValidNewOption={isValidNewOption}
                value={this.getCountry()}
                options={countries}
                onChange={(country) => {
                  if (country !== null) {
                    order.customer.addresses[0].city.state.country.id = country.id;
                    order.customer.addresses[0].city.state.country.name = country.name;
                    this.filterStates(country.id);
                  } else {
                    order.customer.addresses[0].city.state.country = { };
                    this.filterStates(null);
                  }
                  this.setOrder(order);
                }}
              />
            </div>
            <div className="col-4">
              <CreatableSelect
                isClearable
                getOptionLabel={option => option.name}
                getOptionValue={option => option.id}
                getNewOptionData={(inputValue, optionLabel) => ({
                  id: null,
                  name: optionLabel,
                })}
                isValidNewOption={isValidNewOption}
                value={this.getState()}
                options={states}
                onChange={(state) => {
                  if (state !== null) {
                    order.customer.addresses[0].city.state.id = state.id;
                    order.customer.addresses[0].city.state.name = state.name;
                    this.filterCities(state.id);
                  } else {
                    order.customer.addresses[0].city.state = {
                      country: {},
                    };
                    this.filterCities(null);
                  }
                  this.setOrder(order);
                }}
              />
            </div>
            <div className="col-4">
              <CreatableSelect
                isClearable
                getOptionLabel={option => option.name}
                getOptionValue={option => option.id}
                getNewOptionData={(inputValue, optionLabel) => ({
                  id: null,
                  name: optionLabel,
                })}
                isValidNewOption={isValidNewOption}
                value={this.getCity()}
                options={cities}
                onChange={(city) => {
                  if (city !== null) {
                    order.customer.addresses[0].city.id = city.id;
                    order.customer.addresses[0].city.name = city.name;
                  } else {
                    order.customer.addresses[0].city = {
                      state: {
                        country: {},
                      },
                    };
                  }
                  this.setOrder(order);
                }}
              />
            </div>
          </div>

        </div>
        <div className="col-sm-6">
          <h4>{Translator.trans('order.new.order_detail')}</h4>

          <div className="form-group">
            <select className="form-control" onChange={e => (this.setWarehouse(e))}>
              <option>{Translator.trans('order.new.select_warehouse')}</option>
              { warehouses.map(warehouse => (
                <option value={warehouse.id} key={warehouse.id}>{warehouse.name}</option>
              ))}
            </select>
          </div>

          <div className="form-group">
            <div className="form-row">
              <div className="col-4">
                <input
                  type="text"
                  className="form-control"
                  value={code}
                  placeholder={Translator.trans('order.new.consecutive')}
                  onChange={(e) => {
                    order.code = e.target.value;
                    this.setOrder(order);
                  }}
                />
              </div>
              <div className="col-4">
                <select className="form-control" onChange={e => this.setOrderSource(e)}>
                  <option>{Translator.trans('order.new.select_source')}</option>
                  { orderSources.map(source => (
                    <option value={source.id} key={source.id}>{source.name}</option>
                  ))}
                </select>
              </div>
              <div className="col-4">
                <select className="form-control" onChange={e => this.setOrderState(e)}>
                  <option>{Translator.trans('order.new.select_status')}</option>
                  { orderStates.map(status => (
                    <option value={status.id} key={status.id}>{status.name}</option>
                  ))}
                </select>
              </div>
            </div>
          </div>

          <div className="col-sm-12 p-1">
            { products.map((product, index) => (
              <div className="form-group" key={index.toString()}>
                <div className="form-row">
                  <div className="col-6">
                    <Select
                      options={productsByWarehouse}
                      value={productsByWarehouse.filter(productItem => (
                        productItem.uuid === product.uuid
                      ))}
                      onChange={(selectedProduct) => {
                        product.uuid = selectedProduct.uuid;
                        this.setOrder(order);
                      }}
                    />
                  </div>
                  <div className="col-3">
                    <input
                      type="number"
                      className="form-control"
                      placeholder={Translator.trans('order.new.quantity')}
                      onChange={(e) => {
                        product.quantity = e.target.value;
                        this.setOrder(order);
                      }}
                      value={product.quantity}
                    />
                  </div>
                  <div className="col-2">
                    {((index + 1) === products.length
                      && CreateOrder.isCurrentProductFilled(product)) && (
                      <button type="button" className="btn btn-success" onClick={() => this.addEmptyProduct()}>
                        <i className="fas fa-plus-circle" />
                      </button>
                    )}
                    { ' ' }
                    {products.length > 1 && (
                      <button type="button" className="btn btn-danger" onClick={() => this.removeProduct(product.uuid)}>
                        <i className="fas fa-times-circle" />
                      </button>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>
          {
            this.orderValidation()
            && (
              <div className="col-sm-12">
                <button type="button" className="btn btn-success" onClick={() => this.saveOrder()} disabled={sending}>
                  <i className="fas fa-save" />
                  { ' ' }
                  {Translator.trans('submit')}
                  { ' ' }
                  { sending && <i className="fas fa-spinner fa-pulse" /> }
                </button>
              </div>
            )
          }
        </div>
      </div>
    );
  }
}

export default CreateOrder;

CreateOrder.propTypes = {
  locations: PropTypes.instanceOf(Array).isRequired,
  warehouses: PropTypes.instanceOf(Array).isRequired,
  customers: PropTypes.instanceOf(Array).isRequired,
};
