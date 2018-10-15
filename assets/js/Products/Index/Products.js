import 'react-table/react-table.css';
import checkboxHOC from 'react-table/lib/hoc/selectTable';
import React, { Component } from 'react';
import ReactTable from 'react-table';
import axios from 'axios';
import ConfirmSelectedProducts from './ConfirmSelectedProducts';

const CheckboxTable = checkboxHOC(ReactTable);

class Products extends Component {
  constructor(props) {
    super(props);

    this.state = {
      data: [],
      loading: true,
      selection: [],
      selectAll: false,
      confirm: [],
      warehouseSelected: 1,
      warehouses: [],
      modals: {
        confirmModal: false,
      },
    };
    this.toggleAll = this.toggleAll.bind(this);
    this.toggleSelection = this.toggleSelection.bind(this);
    this.isSelected = this.isSelected.bind(this);
    this.isModalOpen = this.isModalOpen.bind(this);
    this.selected = this.selected.bind(this);
  }

  componentDidMount() {
    axios.get(Routing.generate('warehouse_all')).then(res => res.data).then(
      (result) => {
        if (result.length <= 0) {
          throw new Error('The number of warehouses is 0, please add another Warehouse');
        }
        const warehouse = result[0].id;
        this.setState({
          warehouseSelected: warehouse,
          warehouses: result,
        });
        this.loadProducts(warehouse);
      },
    );
  }

  loadProducts(warehouse) {
    this.setState({
      loading: true,
    });
    axios.get(Routing.generate('product_all', { warehouse })).then(res => res.data).then(
      (result2) => {
        this.setState({
          loading: false,
          data: result2,
          warehouseSelected: warehouse,
        });
      },
    );
  }

  isModalOpen(modalName, status) {
    const { modals } = this.state;
    if (typeof modals[modalName] === 'undefined') {
      throw new Error('Modal name not defined');
    }
    modals[modalName] = status;
    this.setState({
      modals,
    });
  }

  selected(e) {
    e.preventDefault();
    const { selection, data } = this.state;
    const confirm = [];
    selection.forEach((item) => {
      data.forEach((item2) => {
        if (item === item2.uuid) {
          confirm.push(item2);
        }
      });
    });
    const modals = {
      confirmModal: true,
    };
    this.setState({
      confirm,
      modals,
    });
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
    const {
      loading, data, selectAll, confirm, selection, warehouses, modals, warehouseSelected,
    } = this.state;
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
            <select className="form-control" onChange={e => this.loadProducts(e.target.value)}>
              {warehouses.map(item => (
                <option
                  value={item.id}
                  key={item.id}
                  defaultValue={item.id === warehouseSelected}
                >
                  {item.name}
                </option>
              ))}
            </select>
          </div>
        </div>
        <hr />
        <div className="row">
          <div className="col-md-6">
            <button
              className={selection.length > 0 ? 'btn btn-sm btn-success' : 'btn btn-sm btn-success disabled'}
              onClick={e => this.selected(e)}
              type="button"
            >
              {Translator.trans('product.index.move_between_warehouses')}
            </button>
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
        {(modals.confirmModal && confirm.length > 0) && (
        <ConfirmSelectedProducts
          data={confirm}
          closeModal={this.isModalOpen}
          warehouseSelected={warehouseSelected}
          warehouses={warehouses}
        />)}
      </div>
    );
  }
}

export default Products;
