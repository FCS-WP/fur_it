import React, { useState, useEffect, useCallback } from "react";
import { Container, Spinner, Alert } from "react-bootstrap";
import AuthTitle from "../Title/AuthTitle";
import { Woocommerce } from "../../Woocommerce/woocommerce";
import { Api } from "../../api";
import WooAuthForm from "../Forms/WooAuthForm";
const AuthContent = () => {
  const [htmlAuth, setHtmlAuth] = useState();
  const [auth, setAuth] = useState("unauthorized");
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const return_url = window.location.href;
  const callback_url = `${window.location.origin}/wp-json/zippy-core/v1/credentials`;

  const fetchCredentials = async (params) => {
    try {
      const { data } = await Woocommerce.wooAuthentication(params);
      setHtmlAuth(data);
    } catch (err) {
      setError("Failed to fetch credentials");
      console.error(err);
    }
  };

  const fetchData = useCallback(async (params) => {
    try {
      setLoading(true);
      const { data } = await Api.checkKeyExits(params);
      setAuth(data.message);
    } catch (err) {
      setError("Failed to fetch authentication status");
      console.error(err);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    const params = { key: "_zippy_woocommerce_key" };
    fetchData(params);
  }, [fetchData]);

  const handleSubmit = (event) => {
    event.preventDefault();

    if (typeof admin_id === "undefined") return;

    const params = {
      app_name: "Zippy Core",
      scope: "read_write",
      user_id: admin_id,
      return_url,
      callback_url,
    };

    fetchCredentials(params);
  };

  if (loading) return <Spinner animation="border" variant="primary" />;

  return (
    <div id="zippy-content">
      {error && <Alert variant="danger">{error}</Alert>}

      {!htmlAuth ? (
        <div className="content-wrapper">
          <Container className="w-80">
            <AuthTitle loading={loading} status={auth} />
            {auth === "unauthorized" && !loading ? (
              <WooAuthForm
                handleSubmit={handleSubmit}
                callbackUrl={callback_url}
                returnUrl={return_url}
              />
            ) : (
              <div className="text-center mt-5">
                <a
                  className="btn btn-outline-success "
                  href="/wp-admin/admin.php?page=admin.php?page=wc-zippy-dashboard"
                >
                  Go to Dashboad
                </a>
              </div>
            )}
          </Container>
        </div>
      ) : (
        <div className="content-wrapper">
          <div
            className="container"
            dangerouslySetInnerHTML={{ __html: htmlAuth }}
          />
        </div>
      )}
    </div>
  );
};

export default AuthContent;
