import React, { useState } from "react";
import { Button, Select, MenuItem, FormControl, Stack } from "@mui/material";
import { toast } from "react-toastify";
import { Api } from "../../../api/admin";

const BulkAction = ({ selectedOrders, setOrders }) => {
  const [action, setAction] = useState("");
  const [loading, setLoading] = useState(false);

  const statusOptions = {
    "wc-pending": "Pending payment",
    "wc-processing": "Processing",
    "wc-on-hold": "On hold",
    "wc-packed": "Packed",
    "wc-completed": "Completed",
    "wc-cancelled": "Cancelled",
  };

  const getStatusFromAction = (action) => {
    return action.replace(/^wc-/, "");
  };

  const handleSubmit = async () => {
    setLoading(true);

    try {
      if (action === "trash") {
        const { data } = await Api.moveToTrashOrder({
          order_ids: selectedOrders,
        });

        if (data.status === "success") {
          toast.success("Orders moved to trash!");
          data.trashed_orders.forEach((orderId) => {
            setOrders((prevOrders) =>
              prevOrders.filter((o) => o.id !== orderId)
            );
          });
        } else {
          toast.error(data.message || "Failed to move orders to trash.");
        }
      } else {
        const { data } = await Api.updateOrderStatus({
          order_ids: selectedOrders,
          status: action,
        });

        if (data.status === "success") {
          toast.success("Orders updated!");

          const updatedOrders = data.updated_orders || [];
          if (updatedOrders.length > 0) {
            const statusText = getStatusFromAction(action);
            setOrders((prevOrders) =>
              prevOrders.map((order) =>
                updatedOrders.includes(order.id)
                  ? { ...order, status: statusText }
                  : order
              )
            );
          }
        } else {
          toast.error(data.message || "Failed to update orders.");
        }
      }
    } catch (err) {
      toast.error("Error: " + err.message);
    } finally {
      setLoading(false);
    }
  };

  const selectedLabel =
    action === "trash"
      ? "Move to Trash"
      : statusOptions[action]
      ? `Change to ${statusOptions[action]}`
      : "Apply Action";

  return (
    <Stack direction="row" spacing={1} alignItems="center">
      <FormControl size="small">
        <Select
          value={action}
          onChange={(e) => setAction(e.target.value)}
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
          <MenuItem value="">Bulk Actions</MenuItem>
          {Object.entries(statusOptions).map(([key, label]) => (
            <MenuItem key={key} value={key}>
              Change status to {label}
            </MenuItem>
          ))}
          <MenuItem value="trash">Move to Trash</MenuItem>
        </Select>
      </FormControl>

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
        onClick={handleSubmit}
        disabled={loading}
      >
        {loading ? "Updating..." : selectedLabel}
      </Button>
    </Stack>
  );
};

export default BulkAction;
