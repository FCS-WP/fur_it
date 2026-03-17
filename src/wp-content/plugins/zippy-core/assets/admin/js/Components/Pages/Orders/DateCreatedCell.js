import React from "react";
import { Typography, Tooltip } from "@mui/material";
import { format, formatDistanceToNowStrict, parse } from "date-fns";
import DateTimeHelper from "../../../utils/DateTimeHelper";

const DateCreatedCell = ({ dateString }) => {
  if (!dateString) return <Typography>N/A</Typography>;

  const parsed = parse(dateString, "yyyy-MM-dd HH:mm:ss", new Date());

  const isToday = DateTimeHelper.isToday(parsed);
  if (isToday) {
    const diff = formatDistanceToNowStrict(parsed, { addSuffix: true });

    const shortDiff = diff
      .replace("hours", "h")
      .replace("hour", "h")
      .replace("minutes", "m")
      .replace("minute", "m")
      .replace("seconds", "s")
      .replace("second", "s");

    return (
      <Tooltip title={format(parsed, "yyyy-MM-dd HH:mm:ss")}>
        <Typography sx={{ fontSize: 14 }} color="text.secondary">
          Created {shortDiff}
        </Typography>
      </Tooltip>
    );
  }

  return (
    <Typography sx={{ fontSize: 14 }}>
      {format(parsed, "yyyy-MM-dd HH:mm:ss")}
    </Typography>
  );
};

export default DateCreatedCell;
