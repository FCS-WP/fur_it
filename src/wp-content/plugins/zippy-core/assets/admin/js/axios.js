import axios from "axios";

export const makeRequest = async (endpoint, params = {}, method = "GET") => {
  const baseURL = "/wp-json";
  const api = axios.create({
    baseURL: baseURL,
  });

  const config = {
    url: "/zippy-core/v1" + endpoint,
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
