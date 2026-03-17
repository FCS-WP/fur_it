import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  Typography,
  Divider,
} from "@mui/material";
import React from "react";

const ShippingModal = ({ open, onClose, shipping }) => {
  if (!shipping) return null;

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <DialogTitle>Shipping Information</DialogTitle>
      <DialogContent dividers>
        <Typography>
          <strong>Name:</strong> {shipping.first_name} {shipping.last_name}
        </Typography>
        <Typography>
          <strong>Company:</strong> {shipping.company || "-"}
        </Typography>
        <Typography>
          <strong>Address 1:</strong> {shipping.address_1}
        </Typography>
        <Typography>
          <strong>Address 2:</strong> {shipping.address_2}
        </Typography>
        <Typography>
          <strong>City:</strong> {shipping.city}
        </Typography>
        <Typography>
          <strong>Country:</strong> {shipping.country}
        </Typography>
      </DialogContent>
      <Divider />
      <DialogActions>
        <Button onClick={onClose} variant="contained">
          Close
        </Button>
      </DialogActions>
    </Dialog>
  );
};

export default ShippingModal;
