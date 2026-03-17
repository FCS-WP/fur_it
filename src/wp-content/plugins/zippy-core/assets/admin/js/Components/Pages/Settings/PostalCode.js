import React from "react";
import {
  Col,
  Container,
  Form,
  FormControl,
  FormGroup,
  Row,
} from "react-bootstrap";
const PostalCode = ({ postalCodeChange, postalCode, ...props }) => {
  return (
    <div className="zippy-settings-content">
      <Container>
        <FormGroup controlId="enable_postalcode">
          <Row>
            <Col sm="2" className="d-flex align-items-center">
              <Form.Label className="m-0">
                <strong>Enable</strong>
              </Form.Label>
            </Col>
            <Col sm="4">
              <FormControl
                as="select"
                value={postalCode}
                onChange={(e) => postalCodeChange(e)}
              >
                <option value={1}>Yes</option>
                <option value={0}>No</option>
              </FormControl>
            </Col>
          </Row>
        </FormGroup>
      </Container>
    </div>
  );
};
export default PostalCode;
