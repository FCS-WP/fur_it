import React, { useEffect, useState, useCallback, useMemo } from "react";
import TableView from "../../../Table/TableView";
import ProductFilterbyCategories from "./ProductFilterByCategories";

import {
  TextField,
  Box,
  Link,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  CircularProgress,
  Typography,
} from "@mui/material";

import { toast } from "react-toastify";
import { Api } from "../../../../api/admin";

const productListOrder = ["IMAGE", "ID", "NAME", "INVENTORY", "ACTIONS"];
const AddProductsDialog = ({ onClose, open, orderID }) => {
  const [data, setData] = useState([]);
  const [pagination, setPagination] = useState({
    page: 0,
    rowsPerPage: 50,
    total: 0,
  });
  const [loading, setLoading] = useState(false);

  const [params, setParams] = useState({
    page: 1,
    items: 5,
    category: "",
    userID: userSettings.uid,
    search: "",
  });

  const [simpleProduct, setSimpleProduct] = useState({});
  const [addedProducts, setAddedProducts] = useState([]);
  /**
   * Fetch products with API call
   */
  const fetchProducts = useCallback(async () => {
    setLoading(true);
    try {
      const { data } = await Api.products(params);
      console.log(data);

      if (data?.status === "success" && Array.isArray(data?.data)) {
        setData(convertRows(data.data));
        setPagination((prev) => ({
          ...prev,
          total: data.pagination?.total || 0,
        }));
      } else {
        setData([]);
        setPagination((prev) => ({ ...prev, total: 0 }));
      }
    } catch (error) {
      console.error("Error fetching products:", error);
      setData([]);
      setPagination((prev) => ({ ...prev, total: 0 }));
    } finally {
      setLoading(false);
    }
  }, [params]);

  const callAddProductsToOrder = async (payload) => {
    try {
      setLoading(true);
      const { data } = await Api.addProductsToOrder(
        orderID,
        "admin_edit_order",
        payload
      );
      if (data?.status === "success") {
        toast.success("Products added to order successfully");
        return data;
      } else {
        console.error("Failed to add products to order:", data?.message);
        toast.error(data?.message || "Failed to add products");
        return null;
      }
    } catch (error) {
      setLoading(false);
      console.error("Error adding products to order:", error);
      toast.error("Error adding products to order");
      return null;
    } finally {
      setLoading(false);
      window.location.reload();
    }
  };

  const addSimpleProduct = useCallback((row) => {
    const info = {
      parent_product_id: row.productID,
      quantity: row.quantity,
    };

    setAddedProducts((prev) => ({
      ...prev,
      [row.productID]: info,
    }));
  }, []);

  const addAddonProduct = useCallback(
    (productId, quantity, packingInstructions, addon) => {
      const info = {
        parent_product_id: productId,
        quantity: quantity,
        packing_instructions: packingInstructions || "",
        addons: addon,
      };

      setAddedProducts((prev) => ({
        ...prev,
        [productId]: info,
      }));
    },
    []
  );

  const submitProducts = useCallback(async () => {
    if (!orderID) {
      toast.error("Order ID is missing");
      return;
    }

    const payload = {
      order_id: orderID,
      products: Object.values(addedProducts),
      action: "admin_edit_order",
    };

    await callAddProductsToOrder(payload);
  }, [orderID, addedProducts]);

  const convertRows = (rows) =>
    rows.map((item) => ({
      ID: item.id,
      productID: item.id,
      orderID: orderID,
      SKU: item.sku,
      LINK: item.link,
      IMAGE: item.img_url,
      NAME: item.name,
      INVENTORY: item.stock,
      SHOW_HIDDEN: false,
      MinAddons: item.min_addons,
      MinOrder: item.min_order,
      ADDONS: item.addons || {},
      packingInstructions: "",
      quantity: item.min_order,
      type: item.type,
    }));

  /**
   * Pagination + Params sync
   */
  useEffect(() => {
    setParams((prev) => ({
      ...prev,
      page: pagination.page + 1,
      items: pagination.rowsPerPage,
    }));
  }, [pagination.page, pagination.rowsPerPage]);

  useEffect(() => {
    if (open) fetchProducts();
  }, [fetchProducts, open]);

  /**
   * Handlers
   */
  const handleFilter = (filter) => {
    setParams((prev) => ({
      ...prev,
      ...filter,
      page: 1,
    }));
    setPagination((prev) => ({ ...prev, page: 0 }));
  };

  const handleChangePage = (event, newPage) =>
    setPagination((prev) => ({ ...prev, page: newPage }));

  const handleChangeRowsPerPage = (event) =>
    setPagination((prev) => ({
      ...prev,
      rowsPerPage: parseInt(event.target.value, 10),
      page: 0,
    }));

  /**
   * Table rows with inputs (memoized)
   */
  const rowsWithInputs = useMemo(
    () =>
      data.map((row) => ({
        ...row,
        ID: `${row.ID} (${row.SKU})`,
        IMAGE: row.IMAGE ? (
          <img
            src={row.IMAGE}
            alt={row.NAME}
            style={{ width: "50px", height: "50px", objectFit: "cover" }}
          />
        ) : (
          "No Image"
        ),
      })),
    [data]
  );

  const handleSubTableChange = (row) => {
    // const params = {
    //   order_id: orderID.orderID,
    //   parent_product_id: row.productID,
    //   quantity: row.quantity,
    //   packing_instructions: row.packingInstructions || "",
    // };
    // setSimpleProduct(params);
  };

  const handleSubTableAddProduct = (row) => {
    addSimpleProduct(row);
    setSimpleProduct({});
  };

  const handleRemoveProduct = (productID) => {
    setAddedProducts((prev) => {
      const newProducts = { ...prev };
      delete newProducts[productID];
      return newProducts;
    });
  };

  /**
   * Table column widths
   */
  const columnWidths = {
    IMAGE: "10%",
    ID: "10%",
    NAME: "auto",
    QUANTITY: "20%",
    ACTION: "20%",
  };

  useEffect(() => {
    console.log("Added Products:", addedProducts);
  }, [addedProducts]);

  return (
    <Dialog
      open={open}
      onClose={onClose}
      className="add_products_model"
      maxWidth="md"
      fullWidth
    >
      <DialogTitle>Add Products to Order</DialogTitle>
      <DialogContent>
        <Box display="flex" flexDirection="column" gap={2}>
          <ProductFilterbyCategories onFilter={handleFilter} />

          {loading ? (
            <Box display="flex" justifyContent="center" py={3}>
              <CircularProgress />
            </Box>
          ) : data.length > 0 ? (
            <>
              <TableView
                hideCheckbox
                cols={productListOrder}
                columnWidths={columnWidths}
                rows={rowsWithInputs}
                className="table-products"
                handleSubTableAddProduct={handleSubTableAddProduct}
                handleSubTableChange={handleSubTableChange}
                addedProducts={addedProducts} // State save products in temp before submit
                onRemoveProduct={handleRemoveProduct} // Remove product from state before submit
                addAddonProduct={addAddonProduct} // Add addon product to state before submit
              />

              {/* <TablePaginationCustom
                count={pagination.total}
                rowsPerPage={pagination.rowsPerPage}
                page={pagination.page}
                onPageChange={handleChangePage}
                onRowsPerPageChange={handleChangeRowsPerPage}
              /> */}
            </>
          ) : (
            <Typography
              variant="body2"
              color="text.secondary"
              align="center"
              py={3}
            >
              No products found.
            </Typography>
          )}
        </Box>
      </DialogContent>
      <DialogActions>
        <Button
          onClick={submitProducts}
          color="primary"
          variant="contained"
          disabled={Object.keys(addedProducts).length === 0}
        >
          Save
        </Button>
        <Button onClick={onClose} color="inherit">
          Cancel
        </Button>
      </DialogActions>
    </Dialog>
  );
};

export default AddProductsDialog;
