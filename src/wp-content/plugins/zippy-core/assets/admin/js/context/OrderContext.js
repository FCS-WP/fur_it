import React, { createContext, useContext, useState, useEffect } from "react";
import { Api } from "../api/admin";
import { OrderContext } from "./CoreContext";

export const OrderProvider = ({ children }) => {
  const [orders, setOrders] = useState([]);
  const [page, setPage] = useState(0);
  const [totalOrders, setTotalOrders] = useState(0);
  const [loadingOrders, setLoadingOrders] = useState(true);
  const [status, setStatus] = useState("");
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const [filteredOrders, setFilteredOrders] = useState(null);

  //Filter
  const [fromDate, setFromDate] = useState("");
  const [toDate, setToDate] = useState("");

  const fetchOrders = async () => {
    try {
      setLoadingOrders(true);
      const params = {
        ...filteredOrders,
        page: page + 1,
        per_page: rowsPerPage,
      };
      const res = await Api.getOrders(params);
      if (res.data.status === "success") {
        setOrders(res.data.orders);
        setTotalOrders(res.data.total_orders);
      }
    } catch (err) {
      console.error("Error fetching orders:", err);
    } finally {
      setLoadingOrders(false);
    }
  };

  useEffect(() => {
    fetchOrders();
  }, [page, rowsPerPage, filteredOrders]);

  const handleFilterOrder = (filters) => {
    setPage(0);
    setFilteredOrders(filters);
  };

  const value = {
    orders,
    totalOrders,
    loadingOrders,
    fromDate,
    toDate,
    page,
    rowsPerPage,
    status,
    filteredOrders,
    setRowsPerPage,
    setStatus,
    setPage,
    setOrders,
    setFromDate,
    setToDate,
    handleFilterOrder,
    fetchOrders,
  };

  return (
    <OrderContext.Provider value={value}>{children}</OrderContext.Provider>
  );
};

export const useOrderProvider = () => useContext(OrderContext);
