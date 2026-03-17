import React, { useEffect, useState } from "react";
import PropTypes from "prop-types";
import {
  Button,
  TableCell,
  TableRow,
  TextField,
  Box,
  Stack,
  IconButton,
  Collapse,
  InputLabel,
  FormControl,
} from "@mui/material";
import { KeyboardArrowDown, KeyboardArrowUp } from "@mui/icons-material";
import theme from "../../../theme/theme";
import ProductDetails from "../Pages/Orders/products/ProductDetails";
import PackingInstruction from "../Pages/Orders/products/PackingInstruction";

const CustomTableRow = ({
  hideCheckbox = false,
  hover,
  row,
  rowIndex,
  selectedRows,
  cols,
  columnWidths,
  onChangeCheckbox,
  isSubtableRow = false,
  showCollapseProp = false,
  onAddProduct, // callback for adding product
  onSubTableChange, // callback for updating sub row table [quantity, packing instructions]
  addedProducts,
  onRemoveProduct,
  addAddonProduct,
}) => {
  const minOrder = row.MinOrder ?? 0;
  const [disabled, setDisabled] = useState(true && minOrder <= 0);
  const [showCollapse, setShowCollapse] = useState(showCollapseProp);
  const [showCollapseAddToOrder, setShowCollapseAddToOrder] =
    useState(false);
  const handleToggleCollapse = () => {
    setShowCollapse((prev) => !prev);
  };
  const [quantity, setQuantity] = useState(minOrder);
  const [packingInstructions, setPackingInstructions] = useState("");
  const [error, setError] = useState(false);
  const [disabledRemove, setDisabledRemove] = useState(true);

  useEffect(() => {
    if (minOrder > 0) {
      setQuantity(minOrder);
      if (onSubTableChange) {
        row.quantity = minOrder;
        onSubTableChange(row);
      }
    }
  }, [minOrder]);

  const handleSubTableChange = (e) => {
    const { name, value } = e.target;

    if (name === "quantity") {
      setDisabled(false);
      const inputValue = parseInt(value, 10) || 0;
      if (inputValue < 0) inputValue = 0;
      setQuantity(inputValue);
      setDisabled(inputValue < minOrder);
      setError(inputValue < minOrder);
      if (onSubTableChange) {
        row.quantity = inputValue;
        onSubTableChange(row);
      }
    }
  };

  const handleAddProduct = () => {
    if (onAddProduct) {
      onAddProduct(row);
      setDisabled(true);
      setDisabledRemove(false);
    }
  };

  const handleRemoveProduct = () => {
    onRemoveProduct(row.productID);
    setDisabled(false);
    setDisabledRemove(true);
  };

  useEffect(() => {
    if (!addedProducts) return;

    if (addedProducts[row.productID]) {
      const q = Number(addedProducts[row.productID].quantity) || minOrder;
      setQuantity(q);
      row.quantity = q;

      setDisabledRemove(false);

      if (Object.keys(row.ADDONS || {}).length == 0) {
        setShowCollapseAddToOrder(true);
        setDisabled(true);
      }

      if (Object.keys(row.ADDONS || {}).length > 0) {
        setShowCollapse(true);
      }
    }
  }, [row.productID, minOrder]);

  const ActionGroup = () => (
    <Stack
      direction="row"
      gap={1}
      alignItems="center"
      justifyContent={"flex-end"}
    >
      {<InputQuantity />}

      {Object.keys(row.ADDONS || {}).length == 0 && (
        <Button
          variant="contained"
          color="primary"
          size="small"
          // onClick={handleAddProduct}
          onClick={() => setShowCollapseAddToOrder((prev) => !prev)}
          disabled={disabled}
        >
          {showCollapseAddToOrder ? "Hide" : "Add to Order"}
        </Button>
      )}
      {Object.keys(row.ADDONS || {}).length > 0 && (
        <Button
          variant="outlined"
          color="primary"
          size="small"
          onClick={handleToggleCollapse}
          disabled={disabled}
        >
          {showCollapse ? "Hide Add-ons" : "Show Add-ons"}
        </Button>
      )}
    </Stack>
  );

  const InputQuantity = () => (
    <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
      <span
        style={{
          fontSize: "20px",
          cursor: "pointer",
          userSelect: "none",
        }}
        onClick={() =>
          handleSubTableChange({
            target: { name: "quantity", value: Number(quantity) - 1 },
          })
        }
      >
        –
      </span>
      <input
        name="quantity"
        min={minOrder}
        value={quantity}
        onChange={handleSubTableChange}
        className={`custom-input ${error ? "error" : ""}`}
        style={{ width: "60px" }}
      />
      <span
        style={{
          fontSize: "20px",
          cursor: "pointer",
          userSelect: "none",
        }}
        onClick={() =>
          handleSubTableChange({
            target: { name: "quantity", value: Number(quantity) + 1 },
          })
        }
      >
        +
      </span>
    </div>
  );

  const renderSimpleProduct = () => (
    <TableCell
      colSpan={cols.length}
      style={{ paddingBottom: 0, paddingTop: 0 }}
    >
      <Collapse
        in={showCollapseAddToOrder}
        timeout="auto"
        unmountOnExit
      >
        <Stack
          direction="row"
          justifyContent="flex-end"
          spacing={1}
          mt={1}
          mb={2}
        >
          {!!addedProducts?.[row.productID] && (
            <Button
              variant="outlined"
              size="small"
              onClick={handleRemoveProduct}
              disabled={disabledRemove}
              sx={{ borderColor: "red", color: "red" }}
            >
              Remove
            </Button>
          )}
          <Button
            variant="contained"
            color="primary"
            size="small"
            onClick={handleAddProduct}
            disabled={disabled}
          >
            Add to Order
          </Button>
        </Stack>
      </Collapse>
    </TableCell>
  );

  const renderProductHasAddons = () => (
    <TableCell
      colSpan={cols.length}
      style={{ paddingBottom: 0, paddingTop: 0 }}
    >
      <Collapse in={showCollapse} timeout="auto" unmountOnExit>
        <PackingInstruction
          value={packingInstructions}
          onChange={handleSubTableChange}
        />

        <ProductDetails
          productID={row.productID}
          orderID={row.orderID}
          quantity={quantity}
          addonMinOrder={row.MinAddons}
          packingInstructions={row.packingInstructions}
          addedProducts={addedProducts}
          addAddonProduct={addAddonProduct}
          handleRemoveProduct={handleRemoveProduct}
          disabledRemove={disabledRemove}
          setDisabledRemove={setDisabledRemove}
          type={row.type}
        />
      </Collapse>
    </TableCell>
  );

  return (
    <>
      <TableRow
        hover={hover}
        key={rowIndex}
        sx={{ borderColor: theme.palette.primary.main }}
      >
        {cols.map((col, colIndex) => (
          <TableCell
            key={colIndex}
            style={{ width: columnWidths[col] || "auto" }}
          >
            {col === "ACTIONS" ? <ActionGroup /> : row[col]}
          </TableCell>
        ))}
      </TableRow>

      {/* Case ADDONS = 0 */}
      {Object.keys(row.ADDONS || {}).length === 0 && (
        <TableRow>{renderSimpleProduct()}</TableRow>
      )}

      {/* Case ADDONS > 0 */}
      {Object.keys(row.ADDONS || {}).length > 0 && (
        <TableRow>{renderProductHasAddons()}</TableRow>
      )}
    </>
  );
};

export default CustomTableRow;
