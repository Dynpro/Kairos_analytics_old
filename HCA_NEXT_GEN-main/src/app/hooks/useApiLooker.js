import SnackbarUtils from 'SnackbarUtils';
import commonConfig from 'app/components/commonConfig';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import { useEffect } from 'react';
import { useDispatch } from 'react-redux';

export default function useApiLooker() {
  const authToken = getAccessToken();
  const dispatchX = useDispatch();
  useEffect(() => {
    const fetchData = async () => {
      const [allClients, allFolders, allDashboards, allSubCategories] = await Promise.all([
        axios(commonConfig.urls.getClientsUrl, {
          headers: { Authorization: `Bearer ${authToken}` },
        }),
        axios(commonConfig.urls.getFoldersUrl, {
          headers: { Authorization: `Bearer ${authToken}` },
        }),
        axios(commonConfig.urls.getDashboardsUrl, {
          headers: { Authorization: `Bearer ${authToken}` },
        }),
        axios(commonConfig.urls.getLookerClientCategoryWiseAllNew, {
          headers: { Authorization: `Bearer ${authToken}` },
        }),
      ]).catch((err) => SnackbarUtils.error(err?.message || 'Something went wrong'));
      dispatchX({ type: 'SET_ALL_CLIENTS', allClients: allClients['data']['data'] });
      dispatchX({ type: 'SET_ALL_FOLDERS', allFolders: allFolders['data']['data'] });
      dispatchX({ type: 'SET_ALL_DASHBOARDS', allDashboards: allDashboards['data']['data'] });
      dispatchX({
        type: 'SET_ALL_SUBCATEGORIES',
        allSubCategories: allSubCategories['data']['data'],
      });
    };
    fetchData();
  }, []);
}
