import { Switch } from "@mui/material";
import React from "react";

const Woo = () => {
  const label = { inputProps: { "aria-label": "Switch demo" } };
  return (
    <div id="woocommerce_customize">
      <Switch {...label} defaultChecked />
    </div>
  );
};

export default Woo;
