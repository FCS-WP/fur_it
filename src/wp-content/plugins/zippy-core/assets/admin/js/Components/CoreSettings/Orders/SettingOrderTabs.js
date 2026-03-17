import { Box, Tab, Tabs } from "@mui/material";
import React, { useEffect, useState } from "react";
import InvoiceSettings from "./InvoiceSettings";
import { SettingApi } from "../../../api/admin";
import { toast } from "react-toastify";
import GeneralInfo from "./GeneralInfo";
import OrderSettings from "./OrderSettings";

const CustomTabPanel = (props) => {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`simple-tabpanel-${index}`}
      aria-labelledby={`simple-tab-${index}`}
      {...other}
    >
      {value === index && <Box p={2}>{children}</Box>}
    </div>
  );
};

const SettingOrderTabs = () => {
  const [value, setValue] = useState(0);
  const [isLoading, setIsLoading] = useState(false);
  const [invoiceSettings, setInvoiceSetting] = useState([]);
  const [orderSettings, setOrderSettings] = useState([]);

  const init_data = async () => {
    await get_invoice_settings();
  } 

  const get_invoice_settings = async () => {
    const { data: response } = await SettingApi.getInvoiceOptions();
    if (!response) {
      toast.error("Failed to get invoice settings");
      return false;
    } 

    setInvoiceSetting(response.result);
  }

  const handleChange = (event, newValue) => {
    setValue(newValue);
  };

  useEffect(()=>{
    init_data();
  }, [])

  return (
    <Box sx={{ width: "100%", typography: "body1" }}>
      <Tabs
        value={value}
        onChange={handleChange}
        aria-label="basic tabs example"
      >
        <Tab label="General" />
        <Tab label="Invoices" />
        <Tab label="Details" />
      </Tabs>
      <Box>
        <CustomTabPanel value={value} index={0}>
          <GeneralInfo />
        </CustomTabPanel>
        <CustomTabPanel value={value} index={1}>
          <InvoiceSettings data={invoiceSettings}/>
        </CustomTabPanel>
         <CustomTabPanel value={value} index={2}>
          <OrderSettings/>
        </CustomTabPanel>
      </Box>
    </Box>
  );
};

export default SettingOrderTabs;
