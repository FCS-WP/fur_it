import React from "react";
import Arrows from "../../../icons/back.svg";
import { Col, Row } from "react-bootstrap";

const AuthTitle = ({ status, loading, ...props }) => {
  return (
    <>
      <Row className="mb-4">
        <Col>
          <div className="authen-thumnail">
            <img src="/wp-content/plugins/zippy-core/assets/images/logo-zippy.png"></img>
            <Arrows />
            <img src="/wp-content/plugins/zippy-core/assets/images/woocommerce.png"></img>
          </div>
        </Col>
      </Row>
      <Row>
        <Col>
          <h4 className="text-center my-2">
            Authentication with Woocommerce to see Order Analytics
          </h4>
          {!loading && (
            <div className="text-center my-2">
              <strong>Status: </strong>
              <span
                className={
                  status === "unauthorized"
                    ? "text-danger text-capitalize "
                    : "text-success text-capitalize"
                }
              >
                {status}
              </span>
            </div>
          )}
        </Col>
      </Row>
    </>
  );
};

export default AuthTitle;
