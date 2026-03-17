import React, { useEffect, useCallback, useState } from "react";
import { Row, Col, Card, CardBody } from "react-bootstrap";
import MainChart from "../Charts/MainChart/MainChart";
import RightChart from "../Charts/RightChart/RightChart";
import ReportFilter from "../Filter/ReportFilter";
import TopTotal from "../Charts/RightChart/TopTotal";
import { DateHelper } from "../../helper/date-helper";
import MainChartTitle from "../Charts/MainChart/MainChartTitle";
import { Woocommerce } from "../../Woocommerce/woocommerce";
import ViewType from "../Charts/MainChart/ViewType";
const Content = () => {
  const [periodFilter, setPeriodFilter] = useState("week");

  const [activeFilter, setActiveFilter] = useState("last_week");
  const [currentViewBy, setCurrentViewBy] = useState();
  const [viewTypeSelected, setViewTypeSelected] = useState("day");
  const [currentView, setcurrentView] = useState("");
  const [netSales, setNetSales] = useState(0);
  const [totalSale, setTotalSale] = useState(0);

  const last_week = DateHelper.getDate();

  const [dateParams, setDateParams] = useState({
    after: last_week.date_start,
    before: last_week.date_end,
  });

  const [orderParams, setOrderParams] = useState({
    interval: "day",
    after: dateParams.after,
    before: dateParams.before,
    order: "asc",
    per_page: 100,
  });

  const [categoriesParams, setCategoriesParams] = useState({
    after: dateParams.after,
    before: dateParams.before,
    extended_info: true,
    orderby: "net_revenue",
  });

  const [mainChartParams, setMainChartParams] = useState({
    interval: "day",
    after: dateParams.after,
    before: dateParams.before,
    fields: ["net_revenue", "items_sold"],
    order: "asc",
    per_page: 100,
  });

  const clickFilter = (key) => {
    setcurrentView("");

    setPeriodFilter(key);
    const date = DateHelper.getDate(key);
    setDateParams(date);
    setOrderParams({
      interval: "day",
      after: date.date_start,
      before: date.date_end,
      order: "asc",
      per_page: 100,
    });
    setActiveFilter(key);

    setMainChartParams((prev) => ({
      ...prev,
      after: date.date_start,
      before: date.date_end,
    }));
    setCategoriesParams((prev) => ({
      ...prev,
      after: date.date_start,
      before: date.date_end,
    }));
    setCurrentViewBy({
      name: "",
      type: "",
    });
  };

  const onClickChart = (date) => {
    const { date_start, date_end } = date;

    if (currentView == "category") return;
    setCategoriesParams({
      after: date_start,
      before: date_end,
      extended_info: true,
      orderby: "net_revenue",
      
    });
    setOrderParams({
      after: date_start,
      before: date_end,
      interval: "day",
      order: "asc",
      per_page: 100,
    });
    const dataSelected = DateHelper.getDateOutputSelect(date, viewTypeSelected);
    setCurrentViewBy({
      name: dataSelected,
      type: "day",
    });
    setcurrentView("day");
  };

  const onClearDate = (type) => {
    if (type === "day") {
      setOrderParams({
        interval: "day",
        after: dateParams.date_start,
        before: dateParams.date_end,
        order: "asc",
        per_page: 100,
      });
      setCategoriesParams({
        after: dateParams.date_start,
        before: dateParams.date_end,
        extended_info: true,
        orderby: "net_revenue",
      });
    } else {
      setMainChartParams({
        interval: viewTypeSelected,
        after: dateParams.date_start,
        before: dateParams.date_end,
        fields: ["net_revenue", "items_sold"],
        order: "asc",
        per_page: 100,
      });
    }
    setOrderParams({
      interval: "day",
      after: dateParams.date_start,
      before: dateParams.date_end,
      order: "asc",
      per_page: 100,
    });
    setCurrentViewBy({
      name: "",
      type: "",
    });
    setcurrentView("");
  };

  const onClickViewType = (type) => {
    if (currentView != "category") {
      setcurrentView("");
      setCurrentViewBy({
        name: "",
        type: "",
      });
      setOrderParams({
        interval: "day",
        after: dateParams.date_start,
        before: dateParams.date_end,
        order: "asc",
        per_page: 100,
      });
    } else {
      setOrderParams(orderParams);
    }

    setCategoriesParams({
      after: dateParams.date_start,
      before: dateParams.date_end,
      extended_info: true,
      orderby: "net_revenue",
    });
    setMainChartParams((prev) => ({ ...prev, interval: type }));
    setViewTypeSelected(type);
  };
  const onClickBarCallback = (cate_id, name, index) => {
    if (currentView == "day") {
      return;
    }

    setOrderParams((prev) => ({ ...prev, categories: cate_id }));
    setMainChartParams((prev) => ({ ...prev, categories: cate_id }));
    setCurrentViewBy({
      name: name,
      type: "category",
      index: index,
    });

    setcurrentView("category");
  };

  const handleCustomDate = (date) => {
    setDateParams((prev) => ({
      ...prev,
      date_start: DateHelper.startOfDateToString(date.date_start),
      date_end: DateHelper.endOfDateToString(date.date_end),
    }));
  };

  useEffect(() => {
    setMainChartParams((prev) => ({
      ...prev,
      after: dateParams.date_start,
      before: dateParams.date_end,
    }));
    setCategoriesParams((prev) => ({
      ...prev,
      after: dateParams.date_start,
      before: dateParams.date_end,
    }));
  }, [dateParams]);

  const fetchData = useCallback(async (params) => {
    const { data } = await Woocommerce.getOrderData(params);
    const dataTotal = data.totals;
    setNetSales(dataTotal.net_revenue || 0);
    setTotalSale(dataTotal.gross_sales || 0);
  }, []);

  useEffect(() => {
    fetchData(orderParams);
  }, [orderParams]);
  return (
    <div id="zippy-content">
      <Row>
        <Col sm="12">
          <ReportFilter
            activeFilter={activeFilter}
            onClick={clickFilter}
            handleCustomDate={handleCustomDate}
          />
        </Col>
      </Row>
      <Row>
        <Col sm="6">
          <Card className="mt-0">
            <MainChartTitle netSales={netSales} totalSale={totalSale} />
            <ViewType
              onClearDate={onClearDate}
              viewTypeSelected={viewTypeSelected}
              onClickViewType={onClickViewType}
              currentViewBy={currentViewBy}
            />
            <MainChart
              onClearDate={onClearDate}
              mainChartParams={mainChartParams}
              onClickChart={onClickChart}
            />
          </Card>
        </Col>
        <Col sm="6">
          <Row className="right-chart">
            <TopTotal params={orderParams} />
            <Col sm="12">
              <RightChart
                currentViewBy={currentViewBy}
                onClickCallback={onClickBarCallback}
                categoriesParams={categoriesParams}
              />
            </Col>
          </Row>
        </Col>
      </Row>
    </div>
  );
};
export default Content;
