import React, { useEffect, useState } from "react";
import {
  Button,
  TextField,
  Stack,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
} from "@mui/material";
import FilterDateRange from "./FilterDateRange";
import { useOrderProvider } from "../../../context/OrderContext";

const getStatusOptions = () => [
  { value: "", label: "All statuses" },
  { value: "pending", label: "Pending" },
  { value: "processing", label: "Processing" },
  { value: "completed", label: "Completed" },
  { value: "on-hold", label: "On Hold" },
  { value: "cancelled", label: "Cancelled" },
  { value: "refunded", label: "Refunded" },
  { value: "failed", label: "Failed" },
];

const formatDate = (dateStr) => {
  if (!dateStr) return "";
  const [y, m, d] = dateStr.split("-");
  return `${y}-${m}-${d}`;
};

const FilterOrder = () => {
  const {
    handleFilterOrder,
    fromDate,
    toDate,
    setFromDate,
    setToDate,
    status,
    setStatus,
  } = useOrderProvider();

  const onFilter = () => {
    handleFilterOrder({
      date_from: formatDate(fromDate),
      date_to: formatDate(toDate),
      order_status: status,
    });
  };

  return (
    <Stack direction="row" spacing={1} alignItems="center">
      <FilterDateRange
        fromDate={fromDate}
        setFromDate={setFromDate}
        toDate={toDate}
        setToDate={setToDate}
      />
      <Stack direction="row" spacing={1} alignItems="center">
        <FormControl size="small">
          <Select
            value={status}
            onChange={(e) => setStatus(e.target.value)}
            displayEmpty
            sx={{ height: "32px", fontSize: "14px", minWidth: "180px" }}
            MenuProps={{
              PaperProps: {
                sx: {
                  mt: 1,
                  ml: 8,
                },
              },
            }}
          >
            {getStatusOptions().map((option) => (
              <MenuItem key={option.value} value={option.value}>
                {option.label}
              </MenuItem>
            ))}
          </Select>
        </FormControl>
      </Stack>
      <Button
        variant="outlined"
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
        onClick={onFilter}
      >
        Filter
      </Button>
    </Stack>
  );
};

export default FilterOrder;
