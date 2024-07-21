import axios from 'axios';
import { API_BASE_URL } from "~/global"

const ExchangeRateService = {
    getCurrencyRates: async (date) => {
        let url = `${API_BASE_URL}/exchange-rates`;
        if (date) {
            url += `?date=${date}`;
        }
        try {
            const response = await axios.get(url);
            return response.data;
        } catch (error) {
            throw error;
        }
    },
}

export default ExchangeRateService;
