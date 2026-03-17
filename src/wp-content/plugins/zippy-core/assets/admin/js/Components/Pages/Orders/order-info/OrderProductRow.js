import {
  Box,
  TableCell,
  TableRow,
  Typography,
  IconButton,
} from "@mui/material";
import DeleteIcon from "@mui/icons-material/Delete";
import EditIcon from "@mui/icons-material/Edit";
import SaveIcon from "@mui/icons-material/Save";
import { Api } from "../../../../api/admin";
import { toast } from "react-toastify";

const OrderProductRow = ({
  item_id,
  item,
  editingItemId,
  tempQuantity,
  setTempQuantity,
  setEditingItemId,
  orderId,
  refreshOrderInfo,
  handleDeleteItem,
  enableEdit,
}) => {
  const roundUp2dp = (num) => {
    return (Math.round(num * 10) / 10).toFixed(2);
  };

  const unitPriceInclTax = roundUp2dp(
    parseFloat(item.price_per_item) + parseFloat(item.tax_per_item)
  );

  const saveQuantity = async () => {
    try {
      const oldItemQuantity = item.quantity;
      const addons = (item.addons || []).map((a) => ({
        item_id: a.addon_id,
        quantity: a.quantity / oldItemQuantity,
      }));
      const { data: res } = await Api.updateOrderItemMetaData(
        orderId,
        "admin_edit_order",
        {
          order_id: orderId,
          item_id,
          quantity: tempQuantity,
          addons,
        }
      );

      if (res.status === "success") {
        refreshOrderInfo();
        toast.success("Quantity updated successfully.");
      } else toast.error(res.message || "Failed to update quantity.");
    } catch (err) {
      console.error(err);
    } finally {
      setEditingItemId(null);
    }
  };

  return (
    <TableRow key={item_id}>
      <TableCell>
        <img src={item.img_url} alt={item.name} width={50} />
      </TableCell>
      <TableCell>
        <Typography
          component="a"
          href={`/wp-admin/post.php?post=${item.product_id}&action=edit`}
          target="_blank"
          sx={{
            textDecoration: "none",
            color: "primary.main",
            "&:hover": { textDecoration: "underline" },
          }}
        >
          {item.name}
        </Typography>
        <Box sx={{ mt: 0.5 }}>
          <Typography
            variant="body2"
            color="text.secondary"
            sx={{ fontSize: "0.65rem" }}
          >
            SKU: {item.sku || "N/A"}
          </Typography>
        </Box>
        {item.addons?.length > 0 && (
          <Box sx={{ ml: 1, mt: 0.5 }}>
            <Typography
              variant="body2"
              fontWeight="bold"
              sx={{ fontSize: "0.8rem" }}
            >
              Combo Products:
            </Typography>
            {item.addons.map((addon, i) => (
              <Typography
                key={i}
                component="a"
                href={`/wp-admin/post.php?post=${addon.addon_id}&action=edit`}
                target="_blank"
                variant="body2"
                color="text.secondary"
                sx={{
                  fontSize: "0.75rem",
                  ml: 2,
                  display: "block",
                  textDecoration: "none",
                  "&:hover": { textDecoration: "underline" },
                }}
              >
                {addon.name} × {addon.quantity}
              </Typography>
            ))}
          </Box>
        )}
        {item.packing_instructions && (
          <Box sx={{ mt: 0.5 }}>
            <Typography
              variant="body2"
              fontWeight="bold"
              sx={{ fontSize: "0.8rem" }}
            >
              Packing Instructions:{" "}
            </Typography>
            <Typography
              variant="body2"
              color="text.secondary"
              sx={{ fontSize: "0.75rem", whiteSpace: "pre-wrap" }}
            >
              {item.packing_instructions}
            </Typography>
          </Box>
        )}
      </TableCell>
      <TableCell>${unitPriceInclTax}</TableCell>
      <TableCell>
        {editingItemId === item_id ? (
          <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
            <span
              style={{
                fontSize: "20px",
                cursor: "pointer",
                userSelect: "none",
              }}
              onClick={() =>
                setTempQuantity((prev) =>
                  Math.max(item.min_order, Number(prev || item.min_order) - 1)
                )
              }
            >
              –
            </span>

            <input
              className="custom-input"
              min={item.min_order}
              value={tempQuantity}
              onChange={(e) => {
                const val = e.target.value;
                if (val === "") {
                  setTempQuantity("");
                } else {
                  setTempQuantity(parseInt(val, 10));
                }
              }}
              onBlur={() => {
                if (!tempQuantity || tempQuantity < item.min_order) {
                  setTempQuantity(item.min_order);
                }
              }}
              style={{
                width: "50px",
                textAlign: "center",
                fontSize: "16px",
                padding: "4px",
              }}
            />

            <span
              style={{
                fontSize: "20px",
                cursor: "pointer",
                userSelect: "none",
              }}
              onClick={() => setTempQuantity((prev) => Number(prev || 0) + 1)}
            >
              +
            </span>
          </div>
        ) : (
          item.quantity
        )}
      </TableCell>
      <TableCell>${(unitPriceInclTax * item.quantity).toFixed(2)}</TableCell>
      <TableCell>
        <>
          {editingItemId === item_id ? (
            <IconButton
              color={enableEdit ? "success" : "default"}
              onClick={enableEdit ? saveQuantity : undefined}
              disabled={!enableEdit}
            >
              <SaveIcon />
            </IconButton>
          ) : (
            <IconButton
              color={enableEdit ? "primary" : "default"}
              onClick={
                enableEdit
                  ? () => {
                      setEditingItemId(item_id);
                      setTempQuantity(item.quantity);
                    }
                  : undefined
              }
              disabled={!enableEdit}
            >
              <EditIcon />
            </IconButton>
          )}

          <IconButton
            color={enableEdit ? "error" : "default"}
            onClick={enableEdit ? () => handleDeleteItem(item_id) : undefined}
            disabled={!enableEdit}
          >
            <DeleteIcon />
          </IconButton>
        </>
      </TableCell>
    </TableRow>
  );
};

export default OrderProductRow;
