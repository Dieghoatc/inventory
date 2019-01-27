import React, { Component } from 'react';
import PropTypes from 'prop-types';
import axios from 'axios';
import Select from 'react-select';

class CreateOrder extends Component {
  static customerHasAddress(customer) {
    return customer
      && customer.addresses && customer.addresses.length > 0 && customer.addresses[0].city !== '';
  }

  static isCurrentProductFilled(product) {
    return (product.quantity !== '' && product.uuid !== '');
  }

  static isAnyCustomerSelected(customer) {
    return (customer.id !== undefined && customer.id !== null);
  }

  constructor(props) {
    super(props);

    const { locations, warehouses } = props;
    const customers = props.customers.map((customer) => {
      customer.value = (customer.id).toString();
      customer.label = `${customer.firstName} ${customer.lastName} [${customer.email}] [${customer.phone}]`;
      return customer;
    });

    this.state = {
      warehouses,
      customers,
      orderStates: [
        { name: Translator.trans('order_statuses.1'), id: 1 },
        { name: Translator.trans('order_statuses.2'), id: 2 },
        { name: Translator.trans('order_statuses.3'), id: 3 },
      ],
      orderSources: [
        { name: Translator.trans('order_source.1'), id: 1 },
        { name: Translator.trans('order_source.2'), id: 2 },
      ],
      countries: locations,
      productsByWarehouse: [],
      states: [],
      cities: [],
      order: {
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
      order.state = Number(stateId);
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
    this.setDefaultAddress(customer);
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

  getCurrentCountry() {
    const { order } = this.state;
    const { customer } = order;
    const { city } = customer.addresses[0];
    const { country } = city.state;
    return Object.keys(country).length === 0 ? '' : country.id;
  }

  setCountryByCity(customer) {
    if (!CreateOrder.customerHasAddress(customer)) {
      const { locations } = this.props;
      this.setState({
        countries: locations,
      });
      return;
    }

    const { city } = customer.addresses[0];
    const { countries } = this.state;
    const country = countries.find(countryItem => (
      countryItem.states.find(state => (
        state.cities.find(cityItem => (
          Number(cityItem.id) === Number(city.id)
        ))))
    ));

    this.setState({
      countries: [country],
    });
  }

  setStateByCity(customer) {
    if (!CreateOrder.customerHasAddress(customer)) {
      this.setState({
        states: [],
      });
      return;
    }

    const { countries } = this.state;
    if (countries.length === 0) {
      return;
    }

    const country = countries[0];
    const { city } = customer.addresses[0];
    const state = country.states.find(stateItem => (
      stateItem.cities.find(cityItem => (
        Number(cityItem.id) === Number(city.id)
      ))
    ));

    this.setState({
      states: [state],
    });
  }

  setDefaultCity(customer) {
    if (!CreateOrder.customerHasAddress(customer)) {
      this.setState({
        cities: [],
      });
      return;
    }
    const { city } = customer.addresses[0];
    this.setState({
      cities: [city],
    });
  }

  setDefaultAddress(customer) {
    this.setCountryByCity(customer);
    this.setStateByCity(customer);
    this.setDefaultCity(customer);
  }

  removeProduct(productUuid) {
    const { order } = this.state;
    const products = order.products.filter(product => (
      productUuid !== product.uuid
    ));
    this.setProducts(products);
  }

  filterStates(el) {
    const countryId = el.target.value;
    const { countries } = this.state;
    countries.forEach((countryItem) => {
      if (Number(countryItem.id) === Number(countryId)) {
        this.setState({
          states: countryItem.states,
        });
      }
    });
  }

  filterCities(el) {
    const stateId = el.target.value;
    if (stateId) {
      const { states } = this.state;
      const state = states.find(cityItem => (Number(cityItem.id) === Number(stateId)));
      this.setState({
        cities: state.cities,
      });
    }
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

  render() {
    const {
      countries, states, cities, order, warehouses, orderStates, orderSources, productsByWarehouse,
      customers,
    } = this.state;
    const { products, customer } = order;
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
              <select className="form-control" onChange={e => (this.filterStates(e))} defaultValue={!this.getCurrentCountry()}>
                { !CreateOrder.isAnyCustomerSelected(customer) && <option value="">{Translator.trans('order.new.select_country')}</option> }
                { countries.map(country => (
                  <option value={country.id} key={country.id}>{country.name}</option>
                ))}
              </select>
            </div>
            <div className="col-4">
              <select className="form-control" onChange={e => (this.filterCities(e))} disabled={states.length === 0}>
                { states.length === 0 && <option>{Translator.trans('order.new.country_required')}</option> }
                { states.length > 1 && <option>{Translator.trans('order.new.select_state')}</option> }
                { states.map(state => (
                  <option value={state.id} key={state.id}>{state.name}</option>
                ))}
              </select>
            </div>
            <div className="col-4">
              <select className="form-control" disabled={cities.length === 0}>
                { cities.length === 0 && <option>{Translator.trans('order.new.state_required')}</option> }
                { cities.length > 1 && <option>{Translator.trans('order.new.select_city')}</option> }
                { cities.map(city => (
                  <option value={city.id} key={city.id}>{city.name}</option>
                ))}
              </select>
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
                  placeholder="Consecutivo"
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
          <div className="col-sm-12">
            <button type="button" className="btn btn-success" onClick={() => console.log(order)}>
              {Translator.trans('submit')}
              { ' ' }
              <i className="fas fa-save" />
            </button>
          </div>
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
