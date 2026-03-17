import React, { useEffect, useState } from "react";
import {
  convertSlugToName,
  orderSettingCells,
} from "../../helper/table-helper";
import { Box, Button, Switch, Typography } from "@mui/material";
import TableViewV2 from "./ TableViewV2";
import { useSettingsProvider } from "../../../providers/SettingsProvider";
import { SettingApi } from "../../api/admin";
import { toast } from "react-toastify";
import { showAlert } from "../../helper/alert-helper";

const ordersConfigsTable = () => {
  const { isApiLoading, detailsConfigs, updateSettingsState } =
    useSettingsProvider();

  const [isLoading, setIsLoading] = useState(false);
  const [dataRows, setDataRows] = useState([]);
  const [updatedValues, setUpdatedValues] = useState([]);

  const [tableConfig, setTableConfig] = useState({
    page: 1,
    itemsPerPage: 10,
    totalItems: 0,
    title: "",
    headCells: orderSettingCells,
  });

  const createData = (slug, value) => {
    const title = convertSlugToName(slug);
    const renderSwitch = <SwitchValue slug={slug} value={value} />;
    return { id: slug, name: title, value: renderSwitch };
  };

  const fetchTableData = async () => {
    if (detailsConfigs) {
      const data = detailsConfigs.map((item) => {
        return createData(item.key, item.data);
      });

      setDataRows(data);
      updateTableConfig({ totalItems: detailsConfigs.length });
      return;
    }
    const { data: response } = await SettingApi.getOrderDetailSettings();

    if (!response || response.status !== "success") {
      toast.error("Can not get details configs");
      return false;
    }
    updateSettingsState({ detailsConfigs: response.result });
  };

  const updateTableConfig = (newValue) => {
    const newData = { ...tableConfig, ...newValue };
    setTableConfig(newData);
  };

  const onSwitchChangeValue = (newItem) => {
    setUpdatedValues((prev) => {
      const filtered = prev.filter((item) => item.key !== newItem.key);
      const newArray = [...filtered, newItem];
      return newArray;
    });
  };

  const SwitchValue = ({ slug, value }) => {
    const [isChecked, setIsChecked] = useState(value === "yes" ? true : false);

    const handleChange = (event) => {
      setIsChecked(event.target.checked);
      onSwitchChangeValue({
        key: slug,
        value: event.target.checked ? "yes" : "no",
      });
    };
    return (
      <Switch
        className="custom-switch"
        checked={isChecked}
        onChange={handleChange}
      />
    );
  };

  const triggerSaveConfigs = async () => {
    setIsLoading(true);
    try {
      const params = {
        new_values: updatedValues,
      };
      const { data: response } = await SettingApi.updateDetailSettings(params);
      if (response && response.status == "success") {
        showAlert("success", "Successfully", "New changes have been updated!");
      } else {
        showAlert(
          "error",
          "Failed",
          "Failed to save changes. Please reload and try again!"
        );
      }
      setIsLoading(false);
      return;
    } catch (error) {
      console.warn(error);
      showAlert(
        "error",
        "Failed",
        "Failed to save changes. Please reload and try again!"
      );
      setIsLoading(false);
      return;
    }
  };

  const handleSelectedRows = (selected) => {
    // Handle Selected ids here
  };

  useEffect(() => {
    fetchTableData();

    return () => {};
  }, [detailsConfigs]);

  return (
    <Box minHeight={"60vh"}>
      {detailsConfigs && dataRows.length > 0 ? (
        <>
          <TableViewV2
            tableConfig={tableConfig}
            dataRows={dataRows}
            onUpdateTable={updateTableConfig}
            onSelectedRows={handleSelectedRows}
          />

          <Button
            onClick={triggerSaveConfigs}
            disabled={updatedValues.length === 0 ? true : false}
            sx={{ mt: 2, textTransform: "capitalize" }}
            variant="contained"
            color="warning"
            loading={isLoading}
          >
            Save Changes
          </Button>
        </>
      ) : (
        <>
          {detailsConfigs && (
            <Typography
              variant="h5"
              fontWeight={600}
              color="black"
              textAlign={"center"}
            >
              Some thing went wrong!
            </Typography>
          )}
        </>
      )}
    </Box>
  );
};

export default ordersConfigsTable;
