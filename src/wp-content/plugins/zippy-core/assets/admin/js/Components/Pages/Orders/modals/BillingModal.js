// BillingModal.jsx
import React from "react";
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  Typography,
  Box,
} from "@mui/material";

const BillingModal = ({ open, onClose, billing }) => {
  if (!billing) return null;

  const fields = [
    [
      "Name",
      `${billing.first_name || ""} ${billing.last_name || ""}`.trim() || "N/A",
    ],
    ["Company", billing.company || "N/A"],
    ["Email", billing.email || "N/A"],
    ["Phone", billing.phone || "N/A"],
    ["Address 1", billing.address_1 || "N/A"],
    ["Address 2", billing.address_2 || "N/A"],
    ["City", billing.city || "N/A"],
    ["Country", billing.country || "N/A"],
  ];

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <DialogTitle>Billing Information</DialogTitle>
      <DialogContent dividers>
        {fields.map(([label, value]) => (
          <Box key={label} sx={{ mb: 1.5 }}>
            <Typography variant="subtitle2" sx={{ fontWeight: "bold" }}>
              {label}:
            </Typography>
            <Typography variant="body2">{value}</Typography>
          </Box>
        ))}
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose} variant="contained">
          Close
        </Button>
      </DialogActions>
    </Dialog>
  );
};

export default BillingModal;
