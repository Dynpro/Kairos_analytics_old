import SnackbarUtils from 'SnackbarUtils';
import commonConfig from 'app/components/commonConfig';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import { useEffect } from 'react';
import { useDispatch } from 'react-redux';

export default function useApiLooker() {
  const authToken = getAccessToken();
  const dispatchX = useDispatch();
  
  // Check if Looker Studio is enabled
  const useLookerStudio = commonConfig.features?.useLookerStudio ?? false;
  
  useEffect(() => {
    const fetchData = async () => {
      try {
        let allClients, allFolders, allDashboards, allSubCategories;
        
        if (useLookerStudio) {
          // Fire all 4 requests simultaneously
          let studioDashboardsResp;
          [studioDashboardsResp, allClients, allFolders, allSubCategories] = await Promise.all([
            axios.post(
              commonConfig.urls.getLookerDashboard,
              {},
              { headers: { Authorization: `Bearer ${authToken}`, 'Content-Type': 'application/json' } }
            ),
            axios(commonConfig.urls.getStudioClient, {
              headers: { Authorization: `Bearer ${authToken}` },
            }),
            axios(commonConfig.urls.getStudioClientFolder, {
              headers: { Authorization: `Bearer ${authToken}` },
            }),
            axios(commonConfig.urls.getStudioClientCategoryWise, {
              headers: { Authorization: `Bearer ${authToken}` },
            }),
          ]);

          // Use the new studio dashboards response
          allDashboards = { data: { data: studioDashboardsResp?.data?.Response || {} } };
        } else {
          // Use legacy Looker APIs
          [allClients, allFolders, allDashboards, allSubCategories] = await Promise.all([
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
          ]);
        }
        
        dispatchX({ type: 'SET_ALL_CLIENTS', allClients: allClients?.data?.data || {} });
        dispatchX({ type: 'SET_ALL_FOLDERS', allFolders: allFolders?.data?.data || {} });
        dispatchX({ type: 'SET_ALL_DASHBOARDS', allDashboards: allDashboards?.data?.data || {} });
        dispatchX({
          type: 'SET_ALL_SUBCATEGORIES',
          allSubCategories: allSubCategories?.data?.data || {},
        });
      } catch (err) {
        SnackbarUtils.error(err?.message || 'Something went wrong loading dashboard data');
        console.error('useApiLooker error:', err);
      }
    };
    
    if (authToken) {
      fetchData();
    }
  }, [authToken, useLookerStudio, dispatchX]);
}
