import { Box, Button, Paper, Typography } from '@mui/material';
import axios from 'axios';
import commonConfig from 'app/components/commonConfig';
import commonRoutes from 'app/components/commonRoutes';
import SnackbarUtils from 'SnackbarUtils';
import { getAccessToken } from 'app/utils/utils';
import { useEffect, useMemo, useState } from 'react';
import { useDispatch } from 'react-redux';
import { useNavigate } from 'react-router-dom';

export default function LookerStudioList() {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const [dashboards, setDashboards] = useState([]);
  const authToken = getAccessToken();

  useEffect(() => {
    const fetchDashboards = async () => {
      try {
        console.log('Fetching dashboards...');
        const resp = await axios.post(commonConfig.urls.getLookerDashboard, {}, {
          headers: { Authorization: `Bearer ${authToken}`, 'Content-Type': 'application/json' },
        });
        
        console.log('API Response received:', resp?.data);
        
        // Check if we have data
        if (!resp?.data?.Response) {
          console.log('No Response data found');
          console.log('Full response:', resp?.data);
          setDashboards([]);
          return;
        }
        
        // Check if Response is an object with data
        if (typeof resp.data.Response !== 'object' || Object.keys(resp.data.Response).length === 0) {
          console.log('Response is empty object');
          setDashboards([]);
          return;
        }
        
        // Flatten the new API structure for display
        const flatList = [];
        const data = resp.data.Response;
        
        console.log('Processing data:', data);
        
        try {
          Object.entries(data).forEach(([folderName, dashboards]) => {
            console.log(`Processing folder: ${folderName}, dashboard count:`, dashboards?.length || 0);
            if (Array.isArray(dashboards)) {
              dashboards.forEach((dashboard) => {
                if (dashboard && dashboard.dash_id) {
                  flatList.push({
                    ...dashboard,
                    client_id: 'default',
                    folder_id: folderName,
                    folder: folderName,
                    embed_url: dashboard.embed_url || dashboard.url || '',
                  });
                }
              });
            }
          });
        } catch (err) {
          console.error('Error processing dashboard data:', err);
        }
        
        console.log('Final flat list:', flatList);
        setDashboards(flatList);
        
      } catch (e) {
        console.error('Error fetching dashboards:', e);
        SnackbarUtils.error(e?.message || 'Failed to load Looker Studio dashboards');
        setDashboards([]);
      }
    };
    fetchDashboards();
  }, [authToken]);

  const grouped = useMemo(() => {
    console.log('Computing grouped from dashboards:', dashboards);
    const map = new Map();
    
    if (!dashboards || dashboards.length === 0) {
      console.log('No dashboards to group');
      return [];
    }
    
    (dashboards || []).forEach((d) => {
      const key = d.folder_name || d.folder || 'Looker Studio';
      if (!map.has(key)) {
        map.set(key, []);
      }
      map.get(key).push(d);
    });
    
    const result = Array.from(map.entries());
    console.log('Grouped result:', result);
    console.log('Grouped length:', result.length);
    return result;
  }, [dashboards]);

  const openDashboard = (d) => {
    dispatch({
      type: 'SET_CLIENT',
      client: {
        subCategory_name: '',
        client_name: d.client_name || '',
        folder_name: d.folder_name || d.folder || 'Looker Studio',
        dashboard_name: d.title || '',
        client_id: d.client_id || '',
      },
    });

    // Always use dash_id (string like "D001") for Looker Studio
    const dashboardId = d.dash_id || d.id;

    console.log('Opening dashboard:', {
      dash_id: dashboardId,
      title: d.title,
      client_id: d.client_id
    });

    navigate(commonRoutes.dashboard, {
      state: {
        iframe_url: d.embed_url || d.iframe_url,
        dash_id: dashboardId,
        client_id: d.client_id,
      },
    });
  };

  return (
    <Box sx={{ p: 2 }}>
      <Typography variant="h5" sx={{ mb: 2 }}>
        Looker Studio Dashboards
      </Typography>

      {console.log('Rendering - grouped.length:', grouped.length, 'dashboards.length:', dashboards.length)}
      {grouped.length === 0 ? (
        <>
          <Typography>Nothing configured yet.</Typography>
          <Box sx={{ mt: 2, p: 2, bgcolor: '#f5f5f5', borderRadius: 1 }}>
            <Typography variant="caption" sx={{ color: 'text.secondary', display: 'block' }}>
              Debug Info:
            </Typography>
            <Typography variant="caption" sx={{ color: 'text.secondary', display: 'block' }}>
              - dashboards.length: {dashboards.length}
            </Typography>
            <Typography variant="caption" sx={{ color: 'text.secondary', display: 'block' }}>
              - grouped.length: {grouped.length}
            </Typography>
            <Typography variant="caption" sx={{ color: 'text.secondary', display: 'block' }}>
              - Check browser console for API response
            </Typography>
          </Box>
        </>
      ) : (
        <>
          <Typography sx={{ mb: 2, color: 'text.secondary' }}>
            Showing {dashboards.length} dashboards in {grouped.length} folders
          </Typography>
          {grouped.map(([folder, items]) => (
            <Paper key={folder} sx={{ p: 2, mb: 2 }}>
              <Typography variant="h6" sx={{ mb: 1 }}>
                {folder} ({items.length} dashboards)
              </Typography>
              {items.map((d) => (
                <Box
                  key={`${folder}-${d.title}-${d.dash_id}`}
                  sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 2, py: 1 }}
                >
                  <Typography>{d.title} (ID: {d.dash_id})</Typography>
                  <Button variant="outlined" size="small" onClick={() => openDashboard(d)}>
                    Open
                  </Button>
                </Box>
              ))}
            </Paper>
          ))}
        </>
      )}
    </Box>
  );
}

