import React from "react";
import { Button, Col, Row, ButtonGroup } from "react-bootstrap";
const FilterButtons = ({
  onClick,
  activeFilter,
  buttonVariant = "light",
  ...props
}) => {
  const filters = ['week', 'month', 'year'];
  return (
    <Row>
      <Col>
        <ButtonGroup className="mb-2 float-right date-filter-button" {...props}>
          {filters.map((filter) => (
            <Button
              key={filter}
              onClick={() => onClick(filter)}
              variant={activeFilter === filter ? "light active" : buttonVariant}
              aria-pressed={activeFilter === filter}
            >
              {filter.charAt(0).toUpperCase() + filter.slice(1)}ly
            </Button>
          ))}
        </ButtonGroup>
      </Col>
    </Row>
  );
};
export default FilterButtons;
