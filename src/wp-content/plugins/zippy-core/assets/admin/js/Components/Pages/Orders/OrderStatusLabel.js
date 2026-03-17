import React from "react";
import { Chip } from "@mui/material";

const statusColors = {
  processing: { label: "Processing", color: "success" },
  completed: { label: "Completed", color: "success" },
  pending: { label: "Pending Payment", color: "error" },
  cancelled: { label: "Cancelled", color: "default" },
  refunded: { label: "Refunded", color: "default" },
  "on-hold": { label: "On Hold", color: "default" },
};

export default function OrderStatusLabel({ status }) {
  const normalized = status?.toLowerCase() || "";
  const info = statusColors[normalized] || { label: status, color: "default" };

  return (
    <Chip
      label={info.label}
      color={info.color}
      variant="outlined"
      size="small"
      sx={{ fontWeight: "bold", textTransform: "capitalize" }}
    />
  );
}
