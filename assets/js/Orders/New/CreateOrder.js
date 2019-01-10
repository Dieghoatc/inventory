import ReactDOM from 'react-dom';
import React, { Component } from 'react';
import PropTypes from 'prop-types';
import DetailOrder from '../Index/DetailOrder';

class CreateOrder extends Component {
  constructor(props) {
    super(props);

    const { locations } = props;
    this.state = {
      loading: false,
      countries: locations,
      states: [],
      cities: [],
    };
  }

  setStates(countryId) {
  }

  render() {
    const { countries } = this.state;
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
                  placeholder="First Name"
                />
              </div>
              <div className="col-auto">
                <input
                  type="text"
                  className="form-control"
                  placeholder="Last Name"
                />
              </div>
            </div>
          </div>

          <div className="form-group">
            <input
              type="email"
              className="form-control"
              aria-describedby="emailHelp"
              placeholder="Correo Electronico"
            />
          </div>

          <div className="form-group">
            <input
              type="text"
              className="form-control"
              aria-describedby="emailHelp"
              placeholder="Telefono"
            />
          </div>

          <div className="form-group">
            <div className="form-row">
              <div className="col-md-auto">
                <input
                  type="text"
                  className="form-control"
                  placeholder="Direccion"
                />
              </div>
              <div className="col-md-auto">
                <input
                  type="text"
                  className="form-control"
                  placeholder="Zip codigo"
                />
              </div>
            </div>
          </div>

          <div className="form-row">
            <div className="col-4">
              <select className="form-control">
                <option>Seleccione Pais</option>
                { countries.map(country => (<option value={country.id} key={country.id}>{country.name}</option>))}
              </select>
            </div>
            <div className="col-4">
              <select className="form-control">
                <option>Seleccione Estado</option>
              </select>
            </div>
            <div className="col-4">
              <select className="form-control">
                <option>Seleccione Ciudad</option>
              </select>
            </div>
          </div>

        </div>
        <div className="col-sm-6">
          <h4>Datos de la orden</h4>

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
