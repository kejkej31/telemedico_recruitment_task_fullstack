import React, { Component } from 'react';
import ExchangeRatesTable from "../components/ExchangeRates/ExchangeRatesTable"

class ExchangeRates extends Component {
    constructor() {
        super();
    }

    render() {
        return (
            <div>
                <section className="row-section">
                    <div className="container">
                        <div className="row mt-5">
                            <div className="col-md-8 offset-md-2">
                                <h2 className="text-center">Exchange rates</h2>
                                <ExchangeRatesTable />
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        )
    }
}
export default ExchangeRates;
