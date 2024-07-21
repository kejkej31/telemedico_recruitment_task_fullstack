import React, { useEffect, useState } from 'react';
import ExchangeRatesFilters from './ExchangeRatesFilters';
import Loader from '~/components/Loader';
import ExchangeRateService from '~/services/ExchangeRatesService';
import { useLocation, useHistory } from "react-router-dom";
import { MAX_DATE, MIN_DATE } from './const';

const today = new Date().toISOString().slice(0, 10);
const defaultDate = new Date().toISOString().slice(0, 10);

const getDateFromQuery = () => {
    const params = new URLSearchParams(location.search);
    const dateString = params.get('date');
    const date = new Date(dateString);
    return !isNaN(date) && date <= new Date(MAX_DATE) && date >= new Date(MIN_DATE) ? dateString : null;
}

const renderPrice = (price) => {
    return price ? price.toFixed(4) : "";
}

const ExchangeRatesTable = () => {
    const location = useLocation();
    const history = useHistory()
    const [todayRates, setTodayRates] = useState([]);
    const [rates, setRates] = useState([]);

    // Info:  common mistake would be to have isLoading and isError states seperately
    // But since request can't both load and be errored, we should use one state
    // Ultimately it be a custom hook for fetching data
    const [requestState, setRequestState] = useState("loading");

    const currentDate = getDateFromQuery() ?? defaultDate;

    const fetchExchangeRates = async (date) => {
        setRequestState("loading");
        try {
            const rates = await ExchangeRateService.getCurrencyRates(date);
            setRates(rates);
            if (date === today) {
                setTodayRates(rates);
            }
            setRequestState("completed");
        } catch (error) {
            console.error(error);
            setRequestState("error");
            return;
        }
    }

    useEffect(() => {
        if (currentDate !== today) {
            const fetchTodaysRates = async () => {
                setTodayRates(await ExchangeRateService.getCurrencyRates(today));
            }
            fetchTodaysRates();
        }

        return () => {
            // Info:  abort the request
        }
    }, []);

    useEffect(() => {
        fetchExchangeRates(currentDate);
    }, [currentDate]);

    const onFilter = ({ date }) => {
        // Info:  We only have one filter so we're using simplest approach,
        // but if we had more we'd set it up differently
        const params = new URLSearchParams(location.search);
        params.set('date', date);
        history.push(location.pathname + `?` + params.toString());
    }

    const renderRates = (rates, todayRates) => {
        return rates.map((rate, index) => (
            <tr key={rate.code}>
                <td>{rate.currency}</td>
                <td>{rate.code}</td>
                <td><strong>{renderPrice(rate.buyPrice)}</strong></td>
                <td><strong>{renderPrice(rate.sellPrice)}</strong></td>
                <td>{index in todayRates ? renderPrice(todayRates[index].buyPrice) : ""}</td>
                <td>{index in todayRates ? renderPrice(todayRates[index].sellPrice) : ""}</td>
            </tr>
        ))
    }

    const columns = [
        { name: 'currency', label: 'Currency' },
        { name: 'code', label: 'Code' },
        { name: 'buyPrice', label: `Buy price ${currentDate}` },
        { name: 'sellPrice', label: `Sell price ${currentDate}` },
        { name: 'todaysBuyPrice', label: `Buy price today` },
        { name: 'todaysSellPrice', label: `Sell price today` }
    ];

    return (
        <div>
            <ExchangeRatesFilters onSubmit={onFilter} date={currentDate} />
            <table id='exchange-rates' className='table table-striped table-bordered table-hover'>
                <thead>
                    <tr>
                        {columns.map(column => (
                            <th key={column.name}>{column.label}</th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {
                        requestState === "loading" && <tr id="loading"><td colSpan={columns.length}><Loader /></td></tr>
                    }
                    {
                        requestState === "completed" && rates.length === 0 && <tr id="no-data">
                            <td colSpan={columns.length}>{`No data available for ${currentDate}`}</td>
                        </tr>
                    }
                    {
                        requestState === "error" && <tr id="error"><td colSpan={columns.length}>Error fetching data</td></tr>
                    }
                    {
                        requestState === "completed" && renderRates(rates, todayRates)
                    }
                </tbody>
            </table>
        </div>
    )
}

export default ExchangeRatesTable;