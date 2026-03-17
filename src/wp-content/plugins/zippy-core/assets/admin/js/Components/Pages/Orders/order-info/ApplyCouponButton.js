import React, { useState } from "react";
import {
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  TextField,
} from "@mui/material";

export default function ApplyCouponButton({ onApply }) {
  const [open, setOpen] = useState(false);
  const [coupon, setCoupon] = useState("");

  const handleOpen = () => setOpen(true);
  const handleClose = () => setOpen(false);

  const handleApply = () => {
    if (coupon.trim()) {
      onApply(coupon);
      handleClose();
      setCoupon("");
    }
  };

  return (
    <>
      <Button
        variant="contained"
        color="error"
        size="small"
        onClick={handleOpen}
        className="button add_new_product"
      >
        Apply Coupon
      </Button>

      <Dialog open={open} onClose={handleClose}>
        <DialogTitle>Apply Coupon</DialogTitle>
        <DialogContent>
          <TextField
            autoFocus
            margin="dense"
            label="Coupon Code"
            type="text"
            fullWidth
            value={coupon}
            onChange={(e) => setCoupon(e.target.value)}
            multiline
            rows={2}
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={handleClose}>Cancel</Button>
          <Button variant="contained" color="primary" onClick={handleApply}>
            Apply
          </Button>
        </DialogActions>
      </Dialog>
    </>
  );
}
