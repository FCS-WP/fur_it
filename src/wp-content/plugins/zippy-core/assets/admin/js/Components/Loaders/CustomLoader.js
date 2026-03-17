import React from "react";
import Backdrop from '@mui/material/Backdrop';

const CustomLoader = ({ loading = true }) => {
  if (!loading) return null;

  return (
    <Backdrop open={loading}>
      <div className="loader-wrapper">
        <div className="terminal-loader">
          <div className="terminal-header">
            <div className="terminal-title">Status</div>
            <div className="terminal-controls">
              <div className="control close"></div>
              <div className="control minimize"></div>
              <div className="control maximize"></div>
            </div>
          </div>
          <div className="text">Loading...</div>
        </div>
      </div>
    </Backdrop>
  );
};
export default CustomLoader;
