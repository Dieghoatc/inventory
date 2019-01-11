import React, { Component } from 'react';
import PropTypes from 'prop-types';

class CreateOrder extends Component {
  constructor(props) {
    super(props);

    const { locations } = props;
    this.state = {
      countries: locations,
      states: [],
      cities: [],
      order: {
        customer: {},
        products: [],
      },
    };
  }

  filterStates(el) {
    const countryId = el.target.value;
    const { countries } = this.state;
    const country = countries.find(countryItem => (Number(countryItem.id) === Number(countryId)));
    this.setState({
      states: country.states,
    });
  }

  filterCities(el) {
    const stateId = el.target.value;
    const { states } = this.state;
    const state = states.find(cityItem => (Number(cityItem.id) === Number(stateId)));
    this.setState({
      cities: state.cities,
    });
  }

  addProduct() {
    const { products } = this.state;
    products.push()
  }

  render() {
    const { countries, states, cities } = this.state;
    console.log(cities.length === 0);
    return (
      <div className="row">
        <div className="col-sm-6">
          <h4>Informacion del cliente</h4>

          <div className="form-group">
            <div className="form-row">
              <div className="col-auto">
                <input
                  type="text"
                  className="form-control"
                  placeholder={Translator.trans('order.new.first_name')}
                />
              </div>
              <div className="col-auto">
                <input
                  type="text"
                  className="form-control"
                  placeholder={Translator.trans('order.new.last_name')}
                />
              </div>
            </div>
          </div>

          <div className="form-group">
            <input
              type="email"
              className="form-control"
              aria-describedby="emailHelp"
              placeholder={Translator.trans('order.new.email')}
            />
          </div>

          <div className="form-group">
            <input
              type="text"
              className="form-control"
              aria-describedby="emailHelp"
              placeholder={Translator.trans('order.new.phone')}
            />
          </div>

          <div className="form-group">
            <div className="form-row">
              <div className="col-md-auto">
                <input
                  type="text"
                  className="form-control"
                  placeholder={Translator.trans('order.new.address')}
                />
              </div>
              <div className="col-md-auto">
                <input
                  type="text"
                  className="form-control"
                  placeholder={Translator.trans('order.new.zip_code')}
                />
              </div>
            </div>
          </div>

          <div className="form-row">
            <div className="col-4">
              <select className="form-control" onChange={e => (this.filterStates(e))}>
                <option>{Translator.trans('order.new.select_country')}</option>
                { countries.map(country => (
                  <option value={country.id} key={country.id}>{country.name}</option>
                ))}
              </select>
            </div>
            <div className="col-4">
              <select className="form-control" onChange={e => (this.filterCities(e))} disabled={states.length === 0}>
                { states.length === 0 && <option>{Translator.trans('order.new.country_required')}</option> }
                { states.length !== 0 && <option>{Translator.trans('order.new.select_state')}</option> }
                { states.map(state => (
                  <option value={state.id} key={state.id}>{state.name}</option>
                ))}
              </select>
            </div>
            <div className="col-4">
              <select className="form-control" disabled={cities.length === 0}>
                { cities.length === 0 && <option>{Translator.trans('order.new.state_required')}</option> }
                { cities.length !== 0 && <option>{Translator.trans('order.new.select_city')}</option> }
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
            <div className="form-row">
              <div className="col-4">
                <input
                  type="text"
                  className="form-control"
                  placeholder="Consecutivo"
                />
              </div>
              <div className="col-4">
                <select className="form-control">
                  <option>Origen</option>
                </select>
              </div>
              <div className="col-4">
                <select className="form-control">
                  <option>Estado</option>
                </select>
              </div>
            </div>
          </div>

          <div className="col-sm-12 p-1">
            <div className="form-group">
              <div className="form-row">
                <div className="col-10">
                  <input
                    type="text"
                    className="form-control"
                    placeholder="Codigo del producto"
                  />
                </div>
                <div className="col-2">
                  <button type="button" className="btn btn-sm btn-success">
                    <i className="fas fa-plus-circle" />
                  </button>
                  { ' ' }
                  <button type="button" className="btn btn-sm btn-danger">
                    <i className="fas fa-times-circle" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default CreateOrder;

CreateOrder.propTypes = {
  locations: PropTypes.array.isRequired,
};
