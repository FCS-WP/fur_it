import React, { useState } from "react";
import {
  Box,
  Button,
  Grid,
  IconButton,
  TextField,
  Typography,
  Paper,
  Select,
  MenuItem,
  FormControl,
  InputLabel,
} from "@mui/material";
import { Add, Delete } from "@mui/icons-material";
import {
  convertNameToSlug,
  convertSlugToName,
} from "../../../helper/table-helper";
import { showAlert } from "../../../helper/alert-helper";
import { SettingApi } from "../../../api/admin";

const InvoiceSettings = ({ data }) => {
  const [rows, setRows] = useState(data);
  const [isLoading, setIsLoading] = useState(false);
  const requiredField = ['invoice-logo', 'store-name'];
  const handleAddRow = () => {
    setRows((prev) => [
      ...prev,
      { key: "", data: { type: "text", value: "", position: "header" } },
    ]);
  };

  const handleChange = (index, field, value) => {
    setRows((prev) => {
      const updated = [...prev];
      if (field == "key") {
        updated[index]["key"] = value;
      } else {
        updated[index]["data"][field] = value;
      }
      return updated;
    });
  };

  const handleRemoveRow = (index) => {
    setRows((prev) => prev.filter((_, i) => i !== index));
  };

  const triggerSaveConfigs = async () => {
    const newData = checkAndRefactorData();
    if (newData.message) {
      showAlert("error", "Failed", newData.message);
      return;
    }
    const params = {
      new_invoices_options: newData,
    };
    const { data: response } = await SettingApi.updateInvoiceOptions(params);
    if (response && response.status == "success") {
      await showAlert(
        "success",
        "Successfully",
        "New changes have been updated!"
      );
    } else {
      await showAlert(
        "error",
        "Failed",
        "Failed to save changes. Please reload and try again!"
      );
    }

    setIsLoading(false);
    return;
  };

  const checkAndRefactorData = () => {
    let existed_keys = [];
    let error = null;
    const refactoredData = rows.map((row) => {
      const new_key = convertNameToSlug(row.key);
      if (!new_key) {
        let message = `Field name is required!`;
        error = {
          message,
        };
      }
      if (existed_keys.includes(new_key)) {
        let message = `Field name ${row.key} has been duplicated!`;
        error = {
          message,
        };
      }

      existed_keys.push(new_key);

      return { ...row, key: new_key };
    });
    if (error) {
      return error;
    }
    return refactoredData;
  };

  return (
    <Grid container spacing={3}>
      <Grid size={12}>
        {rows.map((row, index) => (
          <Box
            key={index}
            elevation={1}
            sx={{
              width: "100%",
              alignItems: "center",
              mb: 2,
              gap: 2,
            }}
          >
            <Grid container spacing={2}>
              <Grid size={3}>
                <TextField
                  size="small"
                  label="Field Name"
                  variant="outlined"
                  required
                  fullWidth
                  disabled={requiredField.includes(row?.key) ? true : false}
                  value={convertSlugToName(row?.key)}
                  onChange={(e) => handleChange(index, "key", e.target.value)}
                />
              </Grid>
              <Grid size={4}>
                <TextField
                  size="small"
                  label={
                    requiredField.includes(row?.key) ? "Logo url" : "Field value"
                  }
                  variant="outlined"
                  required
                  fullWidth
                  value={row?.data.value ?? ""}
                  onChange={(e) => handleChange(index, "value", e.target.value)}
                />
              </Grid>
              <Grid size={2}>
                <FormControl fullWidth size="small">
                  <InputLabel id="invoice-type">Type</InputLabel>
                  <Select
                    labelId="invoice-type"
                    value={row?.data.type ?? "text"}
                    disabled={requiredField.includes(row?.key) ? true : false}
                    label="Type"
                    onChange={(e) =>
                      handleChange(index, "type", e.target.value)
                    }
                  >
                    <MenuItem value={"text"}>Text</MenuItem>
                    <MenuItem value={"link"}>Link</MenuItem>
                  </Select>
                </FormControl>
              </Grid>
              <Grid size={2}>
                <FormControl fullWidth size="small">
                  <InputLabel id="field-position">Position</InputLabel>
                  <Select
                    labelId="field-position"
                    value={row?.data.position ?? "header"}
                    disabled={requiredField.includes(row?.key) ? true : false}
                    label="Position"
                    onChange={(e) =>
                      handleChange(index, "position", e.target.value)
                    }
                  >
                    <MenuItem value={"header"}>Header</MenuItem>
                    <MenuItem value={"footer"}>Footer</MenuItem>
                    <MenuItem value={"logo"} disabled>
                      Logo
                    </MenuItem>
                  </Select>
                </FormControl>
              </Grid>
              <Grid size={1} textAlign="center">
                {rows.length > 1 && (
                  <IconButton
                    color="error"
                    onClick={() => handleRemoveRow(index)}
                    aria-label="delete"
                    disabled={requiredField.includes(row?.key) ? true : false}
                  >
                    <Delete />
                  </IconButton>
                )}
              </Grid>
            </Grid>
          </Box>
        ))}

        <Button
          variant="contained"
          color="info"
          startIcon={<Add />}
          onClick={handleAddRow}
          sx={{ mt: 2, textTransform: "capitalize" }}
        >
          Add Row
        </Button>
      </Grid>
      {/* Preview */}
      {/* <Grid size={12}>
        <Typography variant="subtitle1" sx={{ fontWeight: 500, mb: 1 }}>
          Current Values:
        </Typography>
        <Paper
          sx={{
            p: 2,
            backgroundColor: "#f9f9f9",
            fontFamily: "monospace",
            fontSize: "0.9rem",
          }}
        >
          {JSON.stringify(rows, null, 2)}
        </Paper>
      </Grid> */}
      <Button
        onClick={triggerSaveConfigs}
        disabled={rows.length === 0 ? true : false}
        sx={{ mt: 2, textTransform: "capitalize" }}
        variant="contained"
        color="warning"
        loading={isLoading}
      >
        Save Changes
      </Button>
    </Grid>
  );
};

export default InvoiceSettings;
