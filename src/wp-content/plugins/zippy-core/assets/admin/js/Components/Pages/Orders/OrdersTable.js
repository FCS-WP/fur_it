import React, { useState, useEffect } from "react";
import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  TableSortLabel,
  TablePagination,
  Paper,
  Typography,
  Link,
  Checkbox,
  Box,
  Button,
} from "@mui/material";
import OrderStatusLabel from "./OrderStatusLabel";
import BillingCell from "./BillingCell";
import BulkAction from "./BulkAction";
import FilterOrder from "./FilterOrder";
import { useOrderProvider } from "../../../context/OrderContext";
import DateCreatedCell from "./DateCreatedCell";
import ExportButton from "./ExportButton";

const OrdersTable = ({ orders, orderBy, orderDirection, handleSort }) => {
  const [selectedOrders, setSelectedOrders] = useState([]);
  const [paginatedOrders, setPaginatedOrders] = useState([]);

  const { totalOrders, rowsPerPage, setRowsPerPage, page, setPage } =
    useOrderProvider();

  useEffect(() => {
    const sorted = [...orders].sort((a, b) => {
      let valA = a[orderBy];
      let valB = b[orderBy];
      if (orderBy === "total") {
        valA = parseFloat(valA);
        valB = parseFloat(valB);
      }
      if (valA < valB) return orderDirection === "asc" ? -1 : 1;
      if (valA > valB) return orderDirection === "asc" ? 1 : -1;
      return 0;
    });

    setPaginatedOrders(sorted);
  }, [orders, orderBy, orderDirection, page, rowsPerPage]);

  const handleChangeRowsPerPage = (event) => {
    setRowsPerPage(parseInt(event.target.value, 10));
    setPage(0);
  };

  const handleChangePage = (event, newPage) => {
    setPage(newPage);
  };

  const isAllChecked = () =>
    paginatedOrders.length > 0 &&
    paginatedOrders.every((o) => selectedOrders.includes(String(o.id)));

  const handleChange = (orderId) => {
    setSelectedOrders((prev) =>
      prev.includes(String(orderId))
        ? prev.filter((x) => x !== String(orderId))
        : [...prev, String(orderId)]
    );
  };

  const handleSelectAll = (e) => {
    e.stopPropagation();
    const allIds = paginatedOrders.map((o) => String(o.id));
    const isAllSelected = allIds.every((id) => selectedOrders.includes(id));

    if (isAllSelected) {
      setSelectedOrders((prev) => prev.filter((id) => !allIds.includes(id)));
    } else {
      setSelectedOrders((prev) => [...new Set([...prev, ...allIds])]);
    }
  };

  return (
    <Paper sx={{ p: 2 }}>
      <Box
        sx={{
          display: "flex",
          alignItems: "center",
          justifyContent: "flex-start",
          gap: 2,
          flexWrap: "wrap",
          mb: 2,
        }}
      >
        <Box
          sx={{
            display: "flex",
            alignItems: "center",
          }}
        >
          <Typography variant="h5">Orders</Typography>
        </Box>

        {/* Add new order button */}
        <Button
          variant="contained"
          onClick={() => {
            window.location.href =
              "/wp-admin/admin.php?page=wc-orders&action=new";
          }}
          sx={{
            height: "32px",
            fontSize: "12px",
            borderRadius: "2px",
            background: "#f6f7f7",
            color: "#2271b1",
            border: "1px solid #2271b1",
            boxShadow: "none",
            "&:hover": { background: "#e1e4e6", boxShadow: "none" },
            "@media (max-width: 600px)": {
              height: "40px",
              fontSize: "10px",
            },
          }}
        >
          Add Order
        </Button>
      </Box>
      <Box
        sx={{
          display: "flex",
          alignItems: "center",
          justifyContent: "flex-start",
          gap: 2,
          flexWrap: "wrap",
          mb: 2,
        }}
      >
        <BulkAction
          selectedOrders={selectedOrders}
          setOrders={setPaginatedOrders}
        />
        <FilterOrder />
        <ExportButton />
      </Box>

      <TableContainer sx={{ my: "20px" }}>
        <Table>
          <TableHead>
            <TableRow>
              <TableCell
                padding="checkbox"
                sx={{
                  color: "#333",
                  backgroundColor: "#f5f5f5",
                }}
              >
                <Checkbox
                  checked={isAllChecked()}
                  indeterminate={selectedOrders.length > 0 && !isAllChecked()}
                  onClick={handleSelectAll}
                />
              </TableCell>
              {[
                { id: "order_number", label: "Order #" },
                { id: "phone_number", label: "Phone Number" },
                { id: "status", label: "Status" },
                { id: "total", label: "Total (VND)" },
                { id: "payment_method", label: "Payment Method" },
                { id: "shipping_info", label: "Shipping Info" },
                { id: "date_created", label: "Date Created" },
              ].map((col) => (
                <TableCell
                  key={col.id}
                  sortDirection={orderBy === col.id ? orderDirection : false}
                  sx={{
                    fontWeight: "bold",
                    color: "#333",
                    backgroundColor: "#f5f5f5",
                  }}
                >
                  <TableSortLabel
                    active={orderBy === col.id}
                    direction={orderBy === col.id ? orderDirection : "asc"}
                    onClick={() => handleSort(col.id)}
                  >
                    {col.label}
                  </TableSortLabel>
                </TableCell>
              ))}
            </TableRow>
          </TableHead>

          <TableBody>
            {paginatedOrders.map((order) => (
              <TableRow key={order.id}>
                <TableCell padding="checkbox">
                  <Checkbox
                    name="id[]"
                    value={order.id}
                    checked={selectedOrders.includes(String(order.id))}
                    onClick={() => handleChange(order.id)}
                  />
                </TableCell>

                <TableCell>
                  <Link
                    href={`/wp-admin/admin.php?page=wc-orders&action=edit&id=${order.id}`}
                    underline="hover"
                    color="primary"
                    sx={{ fontWeight: "bold" }}
                  >
                    #{order.order_number} - {order.billing?.first_name}{" "}
                    {order.billing?.last_name}
                  </Link>
                </TableCell>

                <BillingCell billing={order.billing} />

                <TableCell>
                  <OrderStatusLabel status={order.status} />
                </TableCell>

                <TableCell>
                  {parseInt(order.total).toLocaleString()} {order.currency}
                </TableCell>

                <TableCell>{order.payment_method?.title || "N/A"}</TableCell>

                <TableCell>{order.shipping?.city}</TableCell>

                <TableCell>
                  <DateCreatedCell dateString={order.date_created} />
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </TableContainer>

      <TablePagination
        component="div"
        count={totalOrders}
        page={page}
        onPageChange={handleChangePage}
        rowsPerPage={rowsPerPage}
        onRowsPerPageChange={handleChangeRowsPerPage}
        rowsPerPageOptions={[5, 10, 20]}
      />
    </Paper>
  );
};

export default OrdersTable;
