import React, { useContext, useEffect, useState } from "react";
import { SettingsContext } from "../js/context/CoreContext";
import { SettingApi } from "../js/api/admin";
import { toast } from "react-toastify";

export const SettingsProvider = ({ children }) => {
  const [settingsState, setSettingsState] = useState({
    modulesConfigs: null,
    isApiLoading: true,
    selectedModules: null,
    ordersConfigs: null
  });

  const { modulesConfigs, isApiLoading, selectedModules, detailsConfigs } = settingsState;

  const updateSettingsState = (updates) =>
    setSettingsState((prev) => ({ ...prev, ...updates }));

  const value = {
    modulesConfigs,
    isApiLoading,
    selectedModules,
    detailsConfigs,
    updateSettingsState,
  };

  const initDataModules = async () => {
    const { data: response } = await SettingApi.getModulesConfigs();

    if (!response) {
      console.error("Can not get modules configs");
      toast.error("Can not get modules configs");
      return false;
    }

    updateSettingsState({ modulesConfigs: response.result });
    return true;
  };

  useEffect(() => {
    const initData = async () => {
      updateSettingsState({ isApiLoading: true });

      await initDataModules();
      setTimeout(() => {
        updateSettingsState({ isApiLoading: false });
      }, 1500);
    };
    initData();

    return () => {};
  }, []);

  return (
    <SettingsContext.Provider value={value}>
      {children}
    </SettingsContext.Provider>
  );
};

export const useSettingsProvider = () => useContext(SettingsContext);
