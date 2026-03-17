import { Box, Typography } from "@mui/material";
import React from "react";
import ModulesTable from "../../Components/Table/ModulesTable";
import CustomLoader from "../../Components/Loaders/CustomLoader";
import { useSettingsProvider } from "../../../providers/SettingsProvider";

const ModuleSettings = () => {
  const {isApiLoading} = useSettingsProvider();


  return (
    <Box>
      <Typography variant="h4" py={4}>Modules Configurations</Typography>
      <Box bgcolor={"white"} p={3}>
        <ModulesTable />
        <CustomLoader loading={ isApiLoading }/>
      </Box>
    </Box>
  );
};

export default ModuleSettings;
