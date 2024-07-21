import React, { useEffect, useState } from 'react';
import { MAX_DATE, MIN_DATE } from './const';

const ExchangeRatesFilters = ({ date: initialDate, onSubmit }) => {
    const [localDate, setLocalDate] = useState(initialDate ?? MAX_DATE);

    useEffect(() => {
        setLocalDate(initialDate ?? MAX_DATE);
    }, [initialDate]);

    return (
        <div>
            <label>
                Exchange rates for date:
                <input className="form-control" onChange={(e) => { setLocalDate(e.target.value); }}
                    type="date" name="date" value={localDate} min={MIN_DATE} max={MAX_DATE} />
            </label>
            <br />
            <button className="btn btn-primary" onClick={() => onSubmit({ date: localDate })} type="submit">
                Filter
            </button>
        </div>
    );
}

export default ExchangeRatesFilters;