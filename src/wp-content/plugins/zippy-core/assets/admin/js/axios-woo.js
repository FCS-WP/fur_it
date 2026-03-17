import axios from "axios";

export const makeRequest = async (endpoint, params = {}, method = "GET") => {
  const baseURL = "/wp-json";
  const newEndpoint = "wp-json" + endpoint;
  const params_api = { endpoint: newEndpoint, ...params };
  const api = axios.create({
    baseURL: baseURL,
  });

  const config = {
    url: "/zippy-core/v1/call",
    params: params_api,
    method: method,
  };
  try {
    let res = null;

    res = await api.request(config);
    const data = res.data;
    return { data };
  } catch {
    (error) => {
      if (!error?.response) {
        console.error("❗Error", error.message);
        return { ...error, catchedError: error };
      }

      console.error(error.response.statusText);
      return error;
    };
  }
};

export const fetchCredentials = async () => {
  try {
    const response = await axios.get("/wp-json/zippy-core/v1/credentials");
    const { data } = response.data;
    // Use these credentials as needed
  } catch (error) {
    console.error("Error fetching credentials:", error);
  }
};

export const wooAuthentication = async (
  endpoint,
  params = {},
  method = "GET"
) => {
  const baseURL = "/wc-auth/v1/authorize";
  const api = axios.create({
    baseURL: baseURL,
  });

  const config = {
    url: endpoint,
    params: params,
    method: method,
  };
  try {
    let res = null;

    res = await api.request(config);
    const data = res.data;
    return { data };
  } catch {
    (error) => {
      if (!error?.response) {
        console.error("❗Error", error.message);
        return { ...error, catchedError: error };
      }

      console.error(error.response.statusText);
      return error;
    };
  }
};
