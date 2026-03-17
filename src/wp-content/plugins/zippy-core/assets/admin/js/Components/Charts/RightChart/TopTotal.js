import React, { useEffect, useState, useCallback } from "react";
import { Card, CardBody, Col } from "react-bootstrap/esm";
import { Woocommerce } from "../../../Woocommerce/woocommerce";
const TopTotal = ({ params, ...props }) => {
  const [orderTotal, setOrderTotal] = useState(0);
  const [productSold, setProductSold] = useState(0);

  const fetchData = useCallback(async (params) => {
    const { data } = await Woocommerce.getOrderData(params);
    const dataTotal = data.totals;
    setOrderTotal(dataTotal.orders_count || 0);
    setProductSold(dataTotal.items_sold || 0);
  }, []);

  useEffect(() => {
    fetchData(params);
  }, [params]);

  return (
    <>
      <Col >
        <Card className="mt-0">
          <CardBody>
            <label>Orders</label>
            <h5>{orderTotal}</h5>
          </CardBody>
        </Card>
      </Col>
      <Col>
        <Card className="mt-0">
          <CardBody>
            <label>Products Sold</label>
            <h5>{productSold}</h5>
          </CardBody>
        </Card>
      </Col>
    </>
  );
};
export default TopTotal;
