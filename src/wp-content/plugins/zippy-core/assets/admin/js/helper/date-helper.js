import {
  formatISO,
  startOfDay,
  endOfDay,
  startOfMonth,
  setDay,
  endOfMonth,
  format,
  parseISO,
  startOfYear,
  addWeeks,
  subMonths,
  isAfter,
} from "date-fns";

export const DateHelper = {
  getDateOutputSelect(dateData, type) {
    const { date_start, date_end } = dateData;

    const removeTimeFromDate = (dateString) => {
      return dateString.split(" ")[0];
    };

    switch (type) {
      case "week":
        return `${removeTimeFromDate(date_start)} - ${removeTimeFromDate(
          date_end
        )}`;
      case "month":
        return `${removeTimeFromDate(date_start)} - ${removeTimeFromDate(
          date_end
        )}`;
      default:
        return removeTimeFromDate(date_start);
    }
  },
  convertDateOutputChart(dateData, type) {
    switch (type) {
      case "week":
        const [yearDataOfWeek, dateDataOfWeek] = dateData
          .split("-")
          .map(Number);

        const start = startOfYear(new Date(yearDataOfWeek, 0, 1));

        const date = addWeeks(start, dateDataOfWeek - 1);

        return format(date, "yyyy-MMM-dd");

      case "month":
        return format(parseISO(dateData), "yyyy-MMM");
      default:
        return format(parseISO(dateData), "yyyy-MMM-dd");
    }
  },

  // Get Date

  getWeekStartAndEnd(dateData) {
    const date = new Date(dateData);
    const dayOfWeek = date.getDay(); // 0 (Sunday) to 6 (Saturday)

    const startOfWeek = new Date(date);
    startOfWeek.setDate(date.getDate() - (dayOfWeek === 0 ? 6 : dayOfWeek - 1));

    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(startOfWeek.getDate() + 6);

    const formatStartDate = (d) =>
      `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(
        d.getDate()
      ).padStart(2, "0")} 00:00:00`;
    const formatEndDate = (d) =>
      `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(
        d.getDate()
      ).padStart(2, "0")} 23:59:59`;
    return {
      date_start: formatStartDate(startOfWeek),
      date_end: formatEndDate(endOfWeek),
    };
  },
  getMonthStartEnd(monthYear) {
    const [year, month] = monthYear.split("-").map(Number);

    // Start date is the first day of the month at 00:00:00
    const startDate = new Date(Date.UTC(year, month - 1, 1, 0, 0, 0));

    // End date is the last day of the month at 23:59:59
    const endDate = new Date(Date.UTC(year, month, 0, 23, 59, 59));

    // Format the dates
    const formatDate = (date) => {
      const yyyy = date.getUTCFullYear();
      const mm = String(date.getUTCMonth() + 1).padStart(2, "0");
      const dd = String(date.getUTCDate()).padStart(2, "0");
      const hh = String(date.getUTCHours()).padStart(2, "0");
      const min = String(date.getUTCMinutes()).padStart(2, "0");
      const sec = String(date.getUTCSeconds()).padStart(2, "0");
      return `${yyyy}-${mm}-${dd} ${hh}:${min}:${sec}`;
    };

    return {
      start: formatDate(startDate),
      end: formatDate(endDate),
    };
  },
  getDatesOfCurrentYear() {
    const currentYear = new Date().getFullYear();
    const dates = [];

    for (let month = 0; month < 12; month++) {
      for (let day = 1; day <= 31; day++) {
        const date = new Date(currentYear, month, day);

        // Check if the date is valid
        if (date.getFullYear() === currentYear && date.getMonth() === month) {
          // Format the date to DD-MM-YYYY
          const formattedDate = `${String(date.getDate()).padStart(
            2,
            "0"
          )}-${String(date.getMonth() + 1).padStart(2, "0")}-${currentYear}`;
          dates.push(formattedDate);
        }
      }
    }

    return dates;
  },
  getDate(type) {
    switch (type) {
      case "custom":
        return { date_start: this.getDayStartMonth(), date_end: this.getNow() };
      case "last_month":
        const { date_start, date_end } = this.getLastMonthStartAndEnd();
        return { date_start: date_start, date_end: date_end };
      case "this_month":
        return {
          date_start: this.getDayStartMonth(),
          date_end: this.getDayEndMonth(),
        };
      default:
        return {
          date_start: this.getLast7Days(),
          date_end: this.getNow(),
        };
    }
  },
  getLast7Days() {
    const result = formatISO(startOfDay(setDay(new Date(), -6)));
    return result;
  },
  getDayStartMonth() {
    const result = formatISO(startOfMonth(new Date()));
    return result;
  },

  getDayEndMonth() {
    // const result = formatISO(endOfDay(endOfMonth(new Date())));
    const endDate = isAfter(endOfMonth(new Date()), new Date())
      ? new Date()
      : endOfMonth(new Date());
    return formatISO(endOfDay(endDate));
  },

  getNow() {
    const result = formatISO(endOfDay(new Date()));
    return result;
  },
  startOfDateToString(date) {
    return formatISO(startOfDay(new Date(date)));
  },
  endOfDateToString(date) {
    return formatISO(endOfDay(new Date(date)));
  },

  getLastMonthStartAndEnd() {
    const lastMonth = subMonths(new Date(), 1);
    const date_start = formatISO(startOfMonth(lastMonth));
    const date_end = formatISO(endOfMonth(lastMonth));
    return { date_start, date_end };
  },
};
