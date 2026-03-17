import React from "react";
import DatePicker from "react-datepicker";

export default function FilterDateRange({
  fromDate,
  setFromDate,
  toDate,
  setToDate,
}) {
  const handleSetFromDate = (date) => {
    if (date) {
      setFromDate(
        `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(
          2,
          "0"
        )}-${String(date.getDate()).padStart(2, "0")}`
      );
    } else {
      setFromDate("");
    }
  };

  const handleSetToDate = (date) => {
    if (date) {
      setToDate(
        `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(
          2,
          "0"
        )}-${String(date.getDate()).padStart(2, "0")}`
      );
    } else {
      setToDate("");
    }
  };

  return (
    <>
      <DatePicker
        selected={fromDate ? new Date(fromDate) : null}
        onChange={(date) => handleSetFromDate(date)}
        isClearable
        placeholderText="From"
      />
      <DatePicker
        selected={toDate ? new Date(toDate) : null}
        onChange={(date) => handleSetToDate(date)}
        isClearable
        placeholderText="To"
      />
    </>

    // <DatePicker
    //   startDate={fromDate ? new Date(fromDate) : null}
    //   endDate={toDate ? new Date(toDate) : null}
    //   onChange={(update) => {
    //     setDateRange(update);
    //   }}
    //   selectsRange
    //   isClearable
    // />
  );
}
