import { IconButton, TableCell } from "@mui/material";
import VisibilityIcon from "@mui/icons-material/Visibility";
import React, { useState } from "react";
import BillingModal from "./modals/BillingModal";

const BillingCell = ({ billing }) => {
  const [open, setOpen] = useState(false);

  return (
    <>
      <TableCell>
        <IconButton color="primary" onClick={() => setOpen(true)}>
          <VisibilityIcon />
        </IconButton>
        {billing?.phone || "N/A"}
      </TableCell>

      <BillingModal
        open={open}
        onClose={() => setOpen(false)}
        billing={billing}
      />
    </>
  );
};

export default BillingCell;
