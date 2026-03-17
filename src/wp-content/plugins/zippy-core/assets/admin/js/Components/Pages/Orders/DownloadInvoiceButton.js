import React, { useState } from "react";
import { Button } from "@mui/material";
import { Api } from "../../../api/admin";
import { downloadBase64File } from "../../../utils/FileHelper";
import { toast } from "react-toastify";

export default function DownloadInvoiceButton({ orderID }) {
  const handleApply = async () => {
    const { data } = await Api.downloadInvoice({ order_id: orderID });

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
        color="error"
        size="small"
        onClick={handleApply}
        className="button add_new_product"
      >
        Download Invoice
      </Button>
    </>
  );
}
