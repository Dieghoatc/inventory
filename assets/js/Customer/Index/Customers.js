import 'react-table/react-table.css';
import React from 'react';
import ReactTable from 'react-table';
import PropTypes from 'prop-types';

const Customers = (props) => {
  const { customers } = props;
  const columns = [
    {
      Header: Translator.trans('customer.index.name'),
      Cell: row => `${row.original.firstName} ${row.original.lastName}`,
      filterMethod: (filter, row) => {
        const rowData = row._original;
        return (
          String(rowData.firstName.toLowerCase()).startsWith(filter.value.toLowerCase())
            || String(rowData.lastName.toLowerCase()).startsWith(filter.value.toLowerCase())
        );
      },
    },
    {
      Header: Translator.trans('customer.index.email'),
      accessor: 'email',
    },
    {
      Header: Translator.trans('customer.index.phone'),
      accessor: 'phone',
    },
    {
      Cell: row => (
        <div>
          <a href={Routing.generate('customer_edit', { customer: row.original.id })} className="btn btn-sm btn-success" title={Translator.trans('customer.index.edit_this')}>
            <i className="fas fa-pencil-alt" />
          </a>
        </div>
      ),
      Header: Translator.trans('customer.index.options'),
      filterable: false,
    },
  ];
  return (
    <div>
      <ReactTable
        filterable
        data={customers}
        columns={columns}
        className="-striped -highlight"
      />
    </div>
  );
};

export default Customers;

Customers.propTypes = {
  customers: PropTypes.instanceOf(Array).isRequired,
};
