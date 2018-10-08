import 'react-table/react-table.css';
import checkboxHOC from 'react-table/lib/hoc/selectTable';
import React, { Component } from 'react';
import ReactTable from 'react-table';
import axios from 'axios';

const CheckboxTable = checkboxHOC(ReactTable);

class Products extends Component {
  constructor(props) {
    super(props);

    this.state = {
      data: [],
      loading: true,
      selection: [],
      selectAll: false,
    };
    this.toggleAll = this.toggleAll.bind(this);
    this.toggleSelection = this.toggleSelection.bind(this);
    this.isSelected = this.isSelected.bind(this);
    this.warehouseChange = this.warehouseChange.bind(this);
  }

  componentDidMount() {
    axios.get(Routing.generate('product_all', { warehouse: 9 })).then(res => res.data).then(
      (result) => {
        this.setState({
          loading: false,
          data: result,
        });
      },
    );
  }

  warehouseChange() {
    console.log('Change Warehouse');
  }

  toggleAll() {
    const selectAll = !this.state.selectAll;
    const selection = [];
    if (selectAll) {
      const wrappedInstance = this.checkboxTable.getWrappedInstance();
      const currentRecords = wrappedInstance.getResolvedState().sortedData;
      currentRecords.forEach((item) => {
        selection.push(item._original.uuid);
      });
    }
    this.setState({ selectAll, selection });
  }

  toggleSelection(key) {
    // start off with the existing state
    let selection = [...this.state.selection];
    const keyIndex = selection.indexOf(key);
    // check to see if the key exists
    if (keyIndex >= 0) {
    // it does exist so we will remove it using destructing
      selection = [
        ...selection.slice(0, keyIndex),
        ...selection.slice(keyIndex + 1),
      ];
    } else {
    // it does not exist so add it
      selection.push(key);
    }
    // update the state
    this.setState({ selection });
  }

  isSelected(key) {
    const { selection } = this.state;
    return selection.includes(key);
  }

  render() {
    const { loading, data, selectAll } = this.state;
    const { toggleSelection, toggleAll, isSelected } = this;
    const columns = [{
      Header: 'Code',
      accessor: 'code',
    }, {
      Header: 'Description',
      accessor: 'title',
    }, {
      Header: 'Quantity',
      accessor: 'quantity',
    }, {
      Header: 'Warehouse',
      accessor: 'warehouse.name',
    }];

    const checkboxProps = {
      selectAll,
      isSelected,
      toggleSelection,
      toggleAll,
      selectType: 'checkbox',
    };

    return (
      <div>
        <div className="row">
          <div className="col-md-6">
            <select className="form-control" onChange={this.warehouseChange}>
              <option value="volvo">Volvo</option>
              <option value="saab">Saab</option>
              <option value="mercedes">Mercedes</option>
              <option value="audi">Audi</option>
            </select>
          </div>
        </div>
        <hr />
        <div className="row">
          <div className="col-md-6">
            <a href="/" className="btn btn-sm btn-success">{Translator.trans('product.index.move_between_warehouses')}</a>
          </div>
        </div>
        <hr />

        <CheckboxTable
          ref={r => (this.checkboxTable = r)}
          data={data}
          columns={columns}
          loading={loading}
          defaultPageSize={10}
          filterable
          className="-striped -highlight"
          {...checkboxProps}
          keyField="uuid"
        />
      </div>
    );
  }
}

export default Products;
