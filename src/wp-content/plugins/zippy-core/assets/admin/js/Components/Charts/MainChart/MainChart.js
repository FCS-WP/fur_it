import React, {
  useEffect,
  useMemo,
  useState,
  useRef,
  useCallback,
} from "react";
import { Card, CardBody, Spinner, Alert } from "react-bootstrap";
import { Chart, registerables } from "chart.js";
import { Woocommerce } from "../../../Woocommerce/woocommerce";
import { Line, getElementAtEvent } from "react-chartjs-2";
import { DateHelper } from "../../../helper/date-helper";
Chart.register(...registerables);

const MainChart = ({
  mainChartParams,
  onClickChart,
  onClearDate,
  ...props
}) => {
  const options = useMemo(
    () => ({
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
        },
      },
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          callbacks: {
            label: (tooltipItem) => {
              return `Sales: $${tooltipItem.raw.toFixed(2)}`;
            },
          },
        },
      },
    }),
    []
  );

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [dataTotal, setdataTotal] = useState(null);

  const [chartData, setChartData] = useState({
    labels: [],
    datasets: [
      {
        label: "Monthly Revenue",
        data: [],
        fill: false,
        borderColor: "rgba(34, 113, 177, 1)",
        tension: 0.1,
        aspectRatio: 16 / 9,
      },
    ],
  });
  const fetchData = useCallback(async (params) => {
    setLoading(true);
    setError(null);
    try {
      const { data } = await Woocommerce.getOrderData(params);
      const dataTotal = data?.intervals.map((interval) => ({
        labels: DateHelper.convertDateOutputChart(
          interval?.interval,
          params?.interval
        ),
        dates: {
          date_start: interval.date_start,
          date_end: interval.date_end,
        },
      }));
      const dataIntervals = dataTotal.map((interval) => interval.labels);
      const dataNetRevenue = data.intervals.map(
        (interval) => interval.subtotals.net_revenue 
      );
      setdataTotal(dataTotal);
      setChartData({
        labels: dataIntervals,
        datasets: [
          {
            label: "Monthly Revenue",
            data: dataNetRevenue,
            borderWidth: 2,
            backgroundColor: "rgba(34, 113, 177, 1)",
            borderColor: "rgba(34, 113, 177, 1)",
          },
        ],
      });
    } catch (err) {
      setError("Failed to fetch data");
      console.error(err);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchData(mainChartParams);
  }, [mainChartParams, fetchData]);
  const chartRef = useRef(null);

  const printElementAtEvent = (element) => {
    if (!element.length) return;
    const { index } = element[0];
    onClickChart(dataTotal[index].dates);
  };

  const handleOnClickChart = (event) => {
    const { current: chart } = chartRef;
    if (!chart) return;
    printElementAtEvent(getElementAtEvent(chart, event));
  };

  return (
    <CardBody className="">
      {loading && <Spinner animation="border" variant="primary" />}
      {error && <Alert variant="danger">{error}</Alert>}
      <Line
        height={430}
        width={780}
        ref={chartRef}
        data={chartData}
        onClick={handleOnClickChart}
        options={options}
      />
    </CardBody>
  );
};

export default MainChart;
