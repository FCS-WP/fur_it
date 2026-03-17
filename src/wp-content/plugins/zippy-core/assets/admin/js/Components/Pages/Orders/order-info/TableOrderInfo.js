import React, { useEffect, useState } from "react";
import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  Typography,
  Box,
  IconButton,
  CircularProgress,
} from "@mui/material";
import ApplyCouponButton from "./ApplyCouponButton";
import OrderProductRow from "./OrderProductRow";
import OrderSummary from "./OrderSummary";
import { Api } from "../../../../api/admin";
import ButtonAddProducts from "../products/ButtonAddProducts";
import DownloadInvoiceButton from "../DownloadInvoiceButton";

const TableOrderInfo = ({ orderId, enableEdit }) => {
  const [orderInfo, setOrderInfo] = useState(null);
  const [editingItemId, setEditingItemId] = useState(null);
  const [tempQuantity, setTempQuantity] = useState(0);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (orderId) {
      getOrderInfo();
    }
  }, [orderId]);

  const getOrderInfo = async () => {
    try {
      setLoading(true);
      const { data: res } = await Api.getOrderInfo({ order_id: orderId });
      if (res.status === "success") setOrderInfo(res.data);
    } catch (error) {
      console.error("Error fetching order info:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleApplyCoupon = async (couponCode) => {
    try {
      await Api.applyCouponToOrder({
        order_id: orderId,
        coupon_code: couponCode,
      });
      getOrderInfo();
    } catch (error) {
      console.error("Error applying coupon:", error);
    }
  };

  const handleDeleteItem = async (itemId) => {
    if (!window.confirm("Are you sure you want to remove this product?"))
      return;
    try {
      const { data: res } = await Api.removeOrderItem({
        order_id: orderId,
        item_id: itemId,
      });
      if (res.status === "success") getOrderInfo();
      else console.error("Failed to delete item:", res?.message);
    } catch (err) {
      console.error("Error deleting item:", err);
    }
  };

  if (loading) {
    return (
      <Box display="flex" justifyContent="center" py={3}>
        <CircularProgress />
      </Box>
    );
  }

  const products = Object.entries(orderInfo?.products || {});
  const shipping = orderInfo?.shipping || [];
  const fees = orderInfo?.fees || [];
  const coupons = orderInfo?.coupons || [];
  const priceOrderInfo = orderInfo?.order_info || {};

  // Calculate totals per product
  const subtotal = priceOrderInfo?.subtotal;

  const gst = priceOrderInfo?.tax_total;

  // Calculate for shipping
  const shippingTotal = shipping.reduce(
    (sum, s) => sum + parseFloat(s.total || 0),
    0
  );

  // Calculate for fees
  const feesTotal = fees.reduce(
    (sum, fee) => sum + parseFloat(fee.total || 0),
    0
  );

  // Calculate for coupons
  const couponsTotal = coupons.reduce(
    (sum, coupon) => sum + parseFloat(coupon.total || 0),
    0
  );

  // Calculate total
  const total = priceOrderInfo?.total;

  return (
    <Box sx={{ mt: 4 }}>
      <TableContainer elevation={0}>
        <Table>
          <TableHead>
            <TableRow>
              <TableCell
                sx={{
                  fontWeight: "bold",
                  color: "#333",
                }}
              >
                Image
              </TableCell>
              <TableCell
                sx={{
                  fontWeight: "bold",
                  color: "#333",
                }}
              >
                Product Name
              </TableCell>
              <TableCell
                sx={{
                  fontWeight: "bold",
                  color: "#333",
                }}
              >
                Price
              </TableCell>
              <TableCell
                sx={{
                  fontWeight: "bold",
                  color: "#333",
                }}
              >
                Quantity
              </TableCell>
              <TableCell
                sx={{
                  fontWeight: "bold",
                  color: "#333",
                }}
              >
                Total
              </TableCell>
              <TableCell
                sx={{
                  fontWeight: "bold",
                  color: "#333",
                }}
              >
                Actions
              </TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {!products || products.length === 0 ? (
              <TableRow>
                <TableCell colSpan={6} align="center" sx={{ py: 3 }}>
                  <Typography>No order data available.</Typography>
                </TableCell>
              </TableRow>
            ) : (
              products.map(([item_id, item]) => (
                <OrderProductRow
                  key={item_id}
                  item_id={item_id}
                  item={item}
                  editingItemId={editingItemId}
                  tempQuantity={tempQuantity}
                  setTempQuantity={setTempQuantity}
                  setEditingItemId={setEditingItemId}
                  orderId={orderId}
                  refreshOrderInfo={getOrderInfo}
                  handleDeleteItem={handleDeleteItem}
                  enableEdit={enableEdit}
                />
              ))
            )}
          </TableBody>
        </Table>
      </TableContainer>

      <OrderSummary
        subtotal={subtotal}
        gst={gst}
        shippingTotal={shippingTotal}
        feesTotal={feesTotal}
        couponsTotal={couponsTotal}
        total={total}
      />

      {enableEdit ? (
        <Box sx={{ p: 2, display: "flex", justifyContent: "flex-end", gap: 2 }}>
          <ApplyCouponButton onApply={handleApplyCoupon} />
          <ButtonAddProducts orderID={orderId} />
          <DownloadInvoiceButton orderID={orderId} />
        </Box>
      ) : (
        <Box
          sx={{
            p: 2,
            textAlign: "center",
            color: "text.secondary",
            fontStyle: "italic",
            borderTop: "1px solid #eee",
          }}
        >
          This order cannot be edited because its status does not allow
          modifications.
        </Box>
      )}
    </Box>
  );
};

export default TableOrderInfo;
