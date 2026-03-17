import React, { useEffect } from "react";
import { CardBody, Col, Row } from "react-bootstrap";
const MainChartTitle = ({
  netSales,
  totalSale,
  onClearDate,
  dateSelected,
  ...props
}) => {
  return (
    <CardBody className="border-bottom">
      <Row>
        <Col sm="6">
          <h4>Total Sales</h4>
        </Col>
        <Col sm="3" xs="6">
          <label>Total Sales</label>

          <h5>${totalSale}</h5>
        </Col>
        <Col sm="3" xs="6">
          <label>Net Sales</label>

          <h5>${netSales}</h5>
        </Col>
      </Row>
    </CardBody>
  );
};
export default MainChartTitle;
