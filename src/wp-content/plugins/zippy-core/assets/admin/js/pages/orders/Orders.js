import React, { useState } from "react";
import { CircularProgress, Typography } from "@mui/material";
import OrdersTable from "../../Components/Pages/Orders/OrdersTable";
import { useOrderProvider } from "../../context/OrderContext";

const Orders = () => {
  const { orders, loadingOrders } = useOrderProvider();

  const [orderBy, setOrderBy] = useState("date_created");
  const [orderDirection, setOrderDirection] = useState("desc");

  const handleSort = (property) => {
    const isAsc = orderBy === property && orderDirection === "asc";
    setOrderDirection(isAsc ? "desc" : "asc");
    setOrderBy(property);
  };

  if (loadingOrders) {
    return (
      <div style={{ textAlign: "center", padding: "2rem" }}>
        <CircularProgress />
        <Typography variant="body1" sx={{ mt: 2 }}>
          Loading orders...
        </Typography>
      </div>
    );
  }

  return (
    <OrdersTable
      orders={orders}
      orderBy={orderBy}
      orderDirection={orderDirection}
      handleSort={handleSort}
    />
  );
};

export default Orders;
