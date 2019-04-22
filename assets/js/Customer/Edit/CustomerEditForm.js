import React, { Component } from 'react';
import PropTypes from 'prop-types';
import CreatableSelect from 'react-select/lib/Creatable';
import LocationManager from '../../Services/LocationManager';

const isValidNewOption = (inputValue, selectValue, selectOptions) => (
  !(inputValue.trim().length === 0 || selectOptions.find(option => option.name === inputValue))
);


class CustomerEditForm extends Component {
  constructor(props) {
    super(props);

    const { customer, locations } = props;
    this.LocationManager = new LocationManager(locations);
    this.state = {
      customer,
    };
  }

  render() {
    const { customer } = this.state;
    const addresses = customer.addresses.map((address, addressKey) => (
      <div key={`customer-${address.id}`}>
        <hr />
        <div className="form-row">
          <div className="col-md-1 text-center">
            <a className="btn btn-success" href="#" role="button">
              <i className="fas fa-plus" />
            </a>
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
              isClearable
              isValidNewOption={isValidNewOption}
              getOptionLabel={option => option.name}
              getOptionValue={option => option.id}
              placeholder={Translator.trans('customer.edit.country')}
              getNewOptionData={(inputValue, optionLabel) => ({
                id: null,
                name: optionLabel,
              })}
              value={this.LocationManager.getCountryById(address.city.state.country.id)}
              options={this.LocationManager.getCountries()}
              onChange={(e) => {
                const city = {};
                city.state = {};
                city.state.country = this.LocationManager.getCountryById(e.id);
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
              isClearable
              isValidNewOption={isValidNewOption}
              getOptionLabel={option => option.name}
              getOptionValue={option => option.id}
              placeholder={Translator.trans('customer.edit.state')}
              getNewOptionData={(inputValue, optionLabel) => ({
                id: null,
                name: optionLabel,
              })}
              value={this.LocationManager.getStateById(address.city.state.id)}
              options={this.LocationManager.getStatesByCountryId(address.city.state.country.id)}
              onChange={(e) => {
                const city = {};
                city.state = this.LocationManager.getStateById(e.id);
                city.state.country = this.LocationManager.getCountryByStateId(e.id);
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
              isClearable
              isValidNewOption={isValidNewOption}
              getOptionLabel={option => option.name}
              getOptionValue={option => option.id}
              placeholder={Translator.trans('customer.edit.city')}
              getNewOptionData={(inputValue, optionLabel) => ({
                id: null,
                name: optionLabel,
              })}
              value={this.LocationManager.getCityById(address.city.id)}
              options={this.LocationManager.getCitiesByState(address.city.state.id)}
              onChange={(e) => {
                const city = this.LocationManager.getCityById(e.id);
                city.state = this.LocationManager.getStateByCityId(e.id);
                city.state.country = this.LocationManager.getCountryByCityId(e.id);
                customer.addresses[addressKey].city = city;
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
              value={customer.firstName}
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
              value={customer.lastName}
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
              value={customer.email}
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
              value={customer.phone}
              onChange={(e) => {
                customer.phone = e.target.value;
                this.setState({ customer });
              }}
            />
          </div>
        </div>
        <div>
          {addresses}
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
            <a className="btn btn-success btn-block" href="#" role="button">{Translator.trans('save')}</a>
          </div>
        </div>
      </div>
    );
  }
}

export default CustomerEditForm;

CustomerEditForm.propTypes = {
  customer: PropTypes.shape({}).isRequired,
  locations: PropTypes.instanceOf(Array).isRequired,
};
