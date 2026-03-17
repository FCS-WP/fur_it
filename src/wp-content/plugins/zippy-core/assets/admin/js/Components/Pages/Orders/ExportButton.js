import React, { useState } from "react";
import { Box, Button, Menu, MenuItem } from "@mui/material";
import DownloadIcon from "@mui/icons-material/Download";
import { Api } from "../../../api/admin";
import { useOrderProvider } from "../../../context/OrderContext";
import { downloadBase64File } from "../../../utils/FileHelper";
import { toast } from "react-toastify";

const ExportButton = () => {
  const { fromDate, toDate } = useOrderProvider();

  const [anchorEl, setAnchorEl] = useState(null);
  const open = Boolean(anchorEl);

  const handleClick = (event) => {
    setAnchorEl(event.currentTarget);
  };

  const handleClose = () => {
    setAnchorEl(null);
  };

  const handleExport = async (type) => {
    handleClose();
    const params = { date_from: fromDate, date_to: toDate };
    const { data } = await Api.exportOrders({ format: type, ...params });

    if (data?.status === "success") {
      const { file_base64, file_name, file_type } = data;
      if (!file_base64) {
        toast.warning("The file is empty. Nothing to download.");
        return;
      }

      downloadBase64File(file_base64, file_name, file_type);
      toast.success("File downloaded successfully!");
    } else {
      toast.error(data.error.message || "Failed to export orders.");
    }
  };

  return (
    <>
      <Button
        variant="contained"
        color="primary"
        startIcon={<DownloadIcon />}
        onClick={handleClick}
      >
        Export
      </Button>

      <Menu
        anchorEl={anchorEl}
        open={open}
        onClose={handleClose}
        anchorOrigin={{ vertical: "bottom", horizontal: "left" }}
        transformOrigin={{ vertical: "top", horizontal: "left" }}
      >
        <MenuItem onClick={() => handleExport("pdf")}>Export as PDF</MenuItem>
        <MenuItem onClick={() => handleExport("csv")}>Export as CSV</MenuItem>
      </Menu>
    </>
  );
};

export default ExportButton;
