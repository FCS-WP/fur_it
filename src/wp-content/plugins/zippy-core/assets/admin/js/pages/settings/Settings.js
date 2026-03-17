import React, { useEffect, useCallback } from "react";
import { Button, Tab, Tabs, Form } from "react-bootstrap";
import { useState } from "react";
import Authentication from "../../Components/Pages/Settings/Authentication";
import PostalCode from "../../Components/Pages/Settings/PostalCode";
import { Api } from "../../api";

const OrderSettings = () => {
  const [key, setKey] = useState("dashboad");

  const [postalCode, setPostalCode] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const handlePostcodeChange = (e) => {
    setPostalCode(e.target.value);
    console.log(e.target.value);
  };

  const handleOnSubmit = async (e) => {
    e.preventDefault();
    let params = {
      key: "_zippy_postal_code",
      value: postalCode,
    };
    const { data } = await Api.updateSettings(params);
    setLoading(true);
    window.location.reload();
  };

  const fetchData = useCallback(async (params) => {
    try {
      const { data } = await Api.checkKeyExits(params);
      if (data.message === "unauthorized") {
        setPostalCode(0);
      } else {
        setPostalCode(1);
      }
    } catch (err) {
      setError("Failed to fetch authentication status");
      console.error(err);
    } finally {
      // setLoading(false);
    }
  }, []);

  useEffect(() => {
    const params = { key: "_zippy_postal_code" };
    fetchData(params);
  }, [fetchData, loading]);
  return (
    <Form>
      <Tabs
        id="controlled-tab-example"
        activeKey={key}
        onSelect={(k) => setKey(k)}
      >
        <Tab eventKey="dashboad" title="Analytics Woocommerce">
          <Authentication />
        </Tab>
        <Tab eventKey="postal_code" title="Postal Code">
          <PostalCode
            postalCodeChange={handlePostcodeChange}
            postalCode={postalCode}
          />
        </Tab>
      </Tabs>
      <button
        onClick={handleOnSubmit}
        className="button button-primary zippy-submit-button"
        type="submit"
      >
        Save Changes
      </button>
    </Form>
  );
};
export default OrderSettings;
