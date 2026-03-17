import { makeRequest, wooAuthentication } from "../axios-woo";
import { fetchCredentials } from "../axios-woo";

export const Woocommerce = {
  async getTotalSales(params) {
    return await makeRequest("/wc/v3/reports/sales", params);
  },
  async getCategoriesSale(params) {
    return await makeRequest("/wc-analytics/reports/categories", params);
  },
  async getCategories(params) {
    return await makeRequest("/wc-analytics/products/categories", params);
  },
  async getOrderData(params) {
    return await makeRequest("/wc-analytics/reports/products/stats", params);
  },

  async getCredentials(params) {
    return await fetchCredentials(
      "/wc-analytics/reports/products/stats",
      params
    );
  },
  async wooAuthentication(params) {
    return await wooAuthentication("/", params);
  },
};
