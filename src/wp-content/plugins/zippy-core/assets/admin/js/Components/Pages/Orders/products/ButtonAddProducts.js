// EditAddonButton.js
import React, { useState } from "react";
import { Button } from "@mui/material";
import AddProductsDialog from "./AddProductsDialog";

const ButtonAddProducts = ({ orderID }) => {
  const [open, setOpen] = useState(false);

  const handleOpen = () => setOpen(true);
  const handleClose = () => setOpen(false);

  return (
    <>
      <Button
        variant="contained"
        color="error"
        size="small"
        onClick={handleOpen}
        className="button add_new_product"
      >
        Add Products
      </Button>

      {open && (
        <AddProductsDialog
          open={open}
          orderID={orderID}
          onClose={handleClose}
        />
      )}
    </>
  );
};

export default ButtonAddProducts;
