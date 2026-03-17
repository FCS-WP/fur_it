import React, { useEffect, useState } from "react";
import TableViewV2 from "./ TableViewV2";
import { Box, Button, Switch, Typography } from "@mui/material";
import { useSettingsProvider } from "../../../providers/SettingsProvider";
import { settingsHeadcells } from "../../helper/table-helper";
import { SettingApi } from "../../api/admin";
import { toast } from "react-toastify";
import Swal from "sweetalert2";
import { showAlert } from "../../helper/alert-helper";

const ModulesTable = () => {
  const { isApiLoading, modulesConfigs } = useSettingsProvider();
  const [isLoading, setIsLoading] = useState(false);
  const [dataRows, setDataRows] = useState([]);
  const [updatedValues, setUpdatedValues] = useState([]);

  const [tableConfig, setTableConfig] = useState({
    page: 1,
    itemsPerPage: 10,
    totalItems: 0,
    title: "Modules",
    headCells: settingsHeadcells,
  });

  const convertSlugToName = (slug) => {
    if (!slug || typeof slug !== "string") return "";
    const name = slug.replace(/[-_]/g, " ");
    return name.replace(/\b\w/g, (char) => char.toUpperCase());
  };

  const createData = (slug, value) => {
    const title = convertSlugToName(slug);
    const renderSwitch = <SwitchValue slug={slug} value={value} />;
    return { id: slug, name: title, value: renderSwitch };
  };

  const updateTableConfig = (newValue) => {
    const newData = { ...tableConfig, ...newValue };
    setTableConfig(newData);
  };

  const fetchTableData = () => {
    if (modulesConfigs) {
      const data = modulesConfigs.modules.map((item) => {
        return createData(item.key, item.data);
      });

      setDataRows(data);
      updateTableConfig({ totalItems: modulesConfigs.total_modules });
    }
  };

  useEffect(() => {
    fetchTableData();
  }, [modulesConfigs]);

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
      const { data: response } = await SettingApi.updateModulesConfigs(params);
      if (response && response.status == "success") {
        showAlert("success", "Successfully", "New changes have been updated!");
        setTimeout(() => {
          window.location.reload();
        }, 3000);
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

  return (
    <Box minHeight={"60vh"}>
      {modulesConfigs && dataRows.length > 0 ? (
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
          {modulesConfigs && (
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

export default ModulesTable;
