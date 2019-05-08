import React, { Component } from 'react';
import PropTypes from 'prop-types';
import axios from 'axios';
import CreatableSelect from 'react-select/lib/Creatable';
import LocationManager from '../../Services/LocationManager';

const isValidNewOption = (inputValue, selectValue, selectOptions) => (
  !(inputValue.trim().length === 0 || selectOptions.find(option => option.name === inputValue))
);

class CustomerHandler extends Component {
  constructor(props) {
    super(props);

    const { customer, locations } = props;
    this.LocationManager = new LocationManager(locations);
    this.state = {
      customer,
      loading: false,
    };

    this.addNewAddressHandler = this.addNewAddressHandler.bind(this);
    this.submitFormHandler = this.submitFormHandler.bind(this);
  }

  addNewAddressHandler() {
    const { customer } = this.state;
    customer.addresses.push(
      LocationManager.createEmptyAddress(),
    );

    this.setState({ customer });
  }

  removeAddressHandler(addressKeyToRemove) {
    const { customer } = this.state;
    customer.addresses = customer.addresses
      .filter((address, addressKey) => (addressKey !== addressKeyToRemove));

    this.setState({ customer });
  }

  toggleLoading() {
    const { loading } = this.state;
    this.setState({ loading: !loading });
  }

  submitFormHandler() {
    const { customer } = this.state;
    this.toggleLoading();
    axios.post(Routing.generate('customer_update', null), customer).then(() => {
      window.location.href = Routing.generate('customer_index', null);
    });
  }

  render() {
    const { customer, loading } = this.state;
    const addresses = customer.addresses.map((address, addressKey) => (
      // eslint-disable-next-line react/no-array-index-key
      <div key={`addresses-${addressKey}`}>
        <hr />
        <div className="form-row">
          <div className="col-md-1 text-center">
            <button type="button" className="btn btn-success" onClick={this.addNewAddressHandler}>
              <i className="fas fa-plus" />
            </button>
            {' '}
            {
              addressKey !== 0
              && (
                <button type="button" className="btn btn-danger" onClick={() => (this.removeAddressHandler(addressKey))}>
                  <i className="fas fa-minus-circle" />
                </button>
              )
            }
          </div>
          <div className="form-group col-md-5">
            <label htmlFor="name" className="required">
              {Translator.trans('customer.edit.address')}
            </label>
            <input
              type="text"
              required="required"
              className="form-control"
              placeholder={Translator.trans('customer.edit.address')}
              value={address.address}
              onChange={(e) => {
                customer.addresses[addressKey].address = e.target.value;
                this.setState({ customer });
              }}
            />
          </div>
          <div className="form-group col-md-6">
            <label htmlFor="name" className="required">
              {Translator.trans('customer.edit.zip_code')}
            </label>
            <input
              type="text"
              required="required"
              className="form-control"
              placeholder={Translator.trans('customer.edit.zip_code')}
              value={address.zipCode}
              onChange={(e) => {
                customer.addresses[addressKey].zipCode = e.target.value;
                this.setState({ customer });
              }}
            />
          </div>
        </div>
        <div className="form-row">
          <div className="offset-md-1" />
          <div className="form-group col-md-5">
            <label htmlFor="name" className="required">
              {Translator.trans('customer.edit.country')}
            </label>
            <CreatableSelect
              isValidNewOption={isValidNewOption}
              getOptionLabel={option => option.name}
              getOptionValue={option => option.id}
              placeholder={Translator.trans('customer.edit.country')}
              getNewOptionData={(inputValue, optionLabel) => ({
                id: null,
                name: optionLabel,
              })}
              defaultValue={this.LocationManager.getCountryById(
                address.city.state.country.id,
                address.city.state.country.name,
              )}
              value={this.LocationManager.getCountryById(
                address.city.state.country.id,
                address.city.state.country.name,
              )}
              options={this.LocationManager.getCountries()}
              onChange={(e) => {
                const city = {};
                city.state = {};
                city.state.country = {};
                if (e) {
                  city.state.country = this.LocationManager.getCountryById(e.id, e.name);
                }
                customer.addresses[addressKey].city = city;
                this.setState({ customer });
              }}
            />
          </div>
          <div className="form-group col-md-6">
            <label htmlFor="name" className="required">
              {Translator.trans('customer.edit.state')}
            </label>
            <CreatableSelect
              isValidNewOption={isValidNewOption}
              getOptionLabel={option => option.name}
              getOptionValue={option => option.id}
              placeholder={Translator.trans('customer.edit.state')}
              getNewOptionData={(inputValue, optionLabel) => ({
                id: null,
                name: optionLabel,
              })}
              defaultValue={this.LocationManager.getStateById(
                address.city.state.id,
                address.city.state.name,
              )}
              value={this.LocationManager.getStateById(
                address.city.state.id,
                address.city.state.name,
              )}
              options={this.LocationManager.getStatesByCountryId(address.city.state.country.id)}
              onChange={(e) => {
                const city = {};
                if (e) {
                  city.state = this.LocationManager.getStateById(e.id, e.name);
                } else {
                  city.state = {};
                }
                city.state.country = address.city.state.country;
                customer.addresses[addressKey].city = city;
                this.setState({ customer });
              }}
            />
          </div>
        </div>
        <div className="form-row">
          <div className="offset-md-1" />
          <div className="form-group col-md-5">
            <label htmlFor="name" className="required">
              {Translator.trans('customer.edit.city')}
            </label>

            <CreatableSelect
              isValidNewOption={isValidNewOption}
              getOptionLabel={option => option.name}
              getOptionValue={option => option.id}
              placeholder={Translator.trans('customer.edit.city')}
              getNewOptionData={(inputValue, optionLabel) => ({
                id: null,
                name: optionLabel,
              })}
              value={this.LocationManager.getCityById(
                address.city.id,
                address.city.name,
              )}
              options={this.LocationManager.getCitiesByState(address.city.state.id)}
              onChange={(e) => {
                if (e) {
                  const city = this.LocationManager.getCityById(e.id, e.name);
                  city.state = address.city.state;
                  city.state.country = address.city.state.country;
                  customer.addresses[addressKey].city = city;
                } else {
                  customer.addresses[addressKey].city = {
                    state: { country: { } },
                  };
                }
                this.setState({ customer });
              }}
            />
          </div>
        </div>
      </div>
    ));

    return (
      <div>
        <div className="form-row">
          <div className="form-group col-md-6">
            <label htmlFor="name" className="required">
              {Translator.trans('customer.edit.name')}
            </label>
            <input
              type="text"
              required="required"
              className="form-control"
              placeholder={Translator.trans('customer.edit.name')}
              value={customer.firstName || ''}
              onChange={(e) => {
                customer.firstName = e.target.value;
                this.setState({ customer });
              }}
            />
          </div>
          <div className="form-group col-md-6">
            <label htmlFor="lastName" className="required">
              {Translator.trans('customer.edit.last_name')}
            </label>
            <input
              type="text"
              required="required"
              className="form-control"
              placeholder={Translator.trans('customer.edit.last_name')}
              value={customer.lastName || ''}
              onChange={(e) => {
                customer.lastName = e.target.value;
                this.setState({ customer });
              }}
            />
          </div>
        </div>
        <div className="form-row">
          <div className="form-group col-md-6">
            <label htmlFor="email" className="required">
              {Translator.trans('customer.edit.email')}
            </label>
            <input
              type="email"
              required="required"
              className="form-control"
              placeholder={Translator.trans('customer.edit.email')}
              value={customer.email || ''}
              onChange={(e) => {
                customer.email = e.target.value;
                this.setState({ customer });
              }}
            />
          </div>
          <div className="form-group col-md-6">
            <label htmlFor="phone" className="required">
              {Translator.trans('customer.edit.phone')}
            </label>
            <input
              type="text"
              required="required"
              className="form-control"
              placeholder={Translator.trans('customer.edit.phone')}
              value={customer.phone || ''}
              onChange={(e) => {
                customer.phone = e.target.value;
                this.setState({ customer });
              }}
            />
          </div>
        </div>
        <div>
          {addresses}
          {addresses.length === 0
            && (
              <button type="button" className="btn btn-success" onClick={this.addNewAddressHandler}>
                { Translator.trans('customer.new.add_address') }
                { ' ' }
                <i className="fas fa-plus" />
              </button>
            )
          }
        </div>
        <br />
        <div className="form-row">
          <div className="form-group col-md-6">
            <a
              className="btn btn-danger btn-block"
              href={Routing.generate('customer_index', null)}
              role="button"
            >
              {Translator.trans('cancel')}
            </a>
          </div>
          <div className="form-group col-md-6">
            <button
              className={loading ? 'btn btn-success btn-block disabled' : 'btn btn-success btn-block'}
              type="button"
              onClick={this.submitFormHandler}
            >
              {Translator.trans('save')}
            </button>
          </div>
        </div>
      </div>
    );
  }
}

export default CustomerHandler;

CustomerHandler.propTypes = {
  customer: PropTypes.shape({}).isRequired,
  locations: PropTypes.instanceOf(Array).isRequired,
};
