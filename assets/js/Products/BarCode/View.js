import React, { Component } from 'react';
class View extends Component {
  constructor(props) {
    super(props);

  }

  render() {
    return (
      <div>
        <div className="row">
          <div className="col-md-12">
            <p>
              {Translator.trans('product.update.bar-code.description')}
            </p>
          </div>
          <div className="col-md-10">
            <input type="text" className="form-control" placeholder="Jane Doe" />
          </div>
          <div className="col-md-2">
            <button type="submit" className="btn btn-primary">
              {Translator.trans('product.update.bar-code.add_action')}
            </button>
          </div>

          <div className="col-md-12">
            <hr />
            <div className="col-md-6">
              <label>{Translator.trans('product.update.bar-code.destination')}</label>
            </div>
            <div className="col-md-6">
              <select className="form-control">
                <option>Colombia</option>
                <option>Usa</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

export default View;
