import { Box, Typography } from "@mui/material";
import React from "react";
import CustomLoader from "../../Components/Loaders/CustomLoader";
import { useSettingsProvider } from "../../../providers/SettingsProvider";
import SettingOrderTabs from "../../Components/CoreSettings/Orders/SettingOrderTabs";

const CoreSettingOrders = () => {
  const {isApiLoading} = useSettingsProvider();

  return (
    <Box>
      <Typography variant="h4" py={4}>Orders Configurations</Typography>
      <Box bgcolor={"white"} p={3}>
        <SettingOrderTabs />
        <CustomLoader loading={ isApiLoading }/>
      </Box>
    </Box>
  );
};

export default CoreSettingOrders;
