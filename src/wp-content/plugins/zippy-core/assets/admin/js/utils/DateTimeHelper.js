const DateTimeHelper = {
  getToday: (offsetHours = 8) => {
    const now = new Date();

    const targetOffsetInMinutes = -offsetHours * 60;
    const localOffsetInMinutes = now.getTimezoneOffset();

    const diffToGMT8 = localOffsetInMinutes - targetOffsetInMinutes;
    const offsetDate = new Date(now.getTime() + diffToGMT8 * 60 * 1000);

    const year = offsetDate.getFullYear();
    const month = String(offsetDate.getMonth() + 1).padStart(2, "0");
    const day = String(offsetDate.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
  },
  getDateWithOffset: (date, offsetHours = 8) => {
    if (!date) return null;

    const dt = new Date(date);
    const targetOffsetInMinutes = -offsetHours * 60;
    const localOffsetInMinutes = dt.getTimezoneOffset();
    const diffToTarget = localOffsetInMinutes - targetOffsetInMinutes;
    const offsetDate = new Date(dt.getTime() + diffToTarget * 60 * 1000);

    const year = offsetDate.getFullYear();
    const month = String(offsetDate.getMonth() + 1).padStart(2, "0");
    const day = String(offsetDate.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
  },
  /**
   * Get current time in Singapore (HH:mm)
   * @returns {string} Time string in format "HH:mm"
   */
  getSingaporeTime() {
    const now = new Date();
    const options = {
      hour: "2-digit",
      minute: "2-digit",
      hour12: false,
      timeZone: "Asia/Singapore",
    };
    return new Intl.DateTimeFormat("en-GB", options).format(now);
  },

  /**
   * Convert time string "HH:mm" to total minutes
   * @param {*} timeStr
   * @returns {number}
   */
  timeToMinutes(timeStr) {
    const [h, m] = timeStr.split(":").map(Number);
    return h * 60 + m;
  },

  isToday(date, offsetHours = 8) {
    const dateStr = DateTimeHelper.getDateWithOffset(date, offsetHours);
    const todayStr = DateTimeHelper.getToday(offsetHours);

    return dateStr == todayStr;
  },
};

export default DateTimeHelper;
