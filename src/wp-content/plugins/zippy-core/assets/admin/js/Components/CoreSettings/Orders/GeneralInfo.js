import React from "react";
import { Box, Typography, List, ListItem, ListItemText } from "@mui/material";

const GeneralInfo = () => {
  return (
    <Box sx={{ p: 3 }}>
      <Typography variant="h5" gutterBottom fontWeight="bold">
        Orders Settings Overview
      </Typography>

      <Typography variant="body1" mb={2}>
        The <strong>Orders Settings</strong> section allows you to configure various order-related options to optimize your store’s workflow and customer experience. It includes the following modules:
      </Typography>

      {/* Invoices Setting */}
      <Box mb={3}>
        <Typography variant="h6" gutterBottom>
          1. Invoices Setting
        </Typography>
        <List dense>
          <ListItem>
            <ListItemText primary="Customize invoice fields (e.g., company name, address, tax number)." />
          </ListItem>
          <ListItem>
            <ListItemText primary="Enable or disable automatic invoice generation." />
          </ListItem>
          <ListItem>
            <ListItemText primary="Configure invoice templates and numbering formats." />
          </ListItem>
        </List>
      </Box>

      {/* Pre-Order Setting */}
      <Box mb={3}>
        <Typography variant="h6" gutterBottom>
          2. Pre-Order Setting
        </Typography>
        <List dense>
          <ListItem>
            <ListItemText primary="Enable pre-order products and set availability dates." />
          </ListItem>
          <ListItem>
            <ListItemText primary="Define payment rules (full or partial payment)." />
          </ListItem>
          <ListItem>
            <ListItemText primary="Display custom messages on pre-order product pages and checkout." />
          </ListItem>
        </List>
      </Box>

      {/* Addons Setting */}
      <Box mb={3}>
        <Typography variant="h6" gutterBottom>
          3. Addons Setting
        </Typography>
        <List dense>
          <ListItem>
            <ListItemText primary="Add or remove available add-ons." />
          </ListItem>
          <ListItem>
            <ListItemText primary="Set pricing and display rules." />
          </ListItem>
          <ListItem>
            <ListItemText primary="Manage add-on visibility during checkout and in the cart." />
          </ListItem>
        </List>
      </Box>

      {/* Custom Email Setting */}
      <Box mb={3}>
        <Typography variant="h6" gutterBottom>
          4. Custom Email Setting
        </Typography>
        <List dense>
          <ListItem>
            <ListItemText primary="Define custom email recipients (e.g., admin, client, production team)." />
          </ListItem>
          <ListItem>
            <ListItemText primary="Customize email subjects and content templates." />
          </ListItem>
          <ListItem>
            <ListItemText primary="Enable or disable specific order-related emails (e.g., order received, order completed, invoice sent)." />
          </ListItem>
        </List>
      </Box>
    </Box>
  );
};

export default GeneralInfo;
