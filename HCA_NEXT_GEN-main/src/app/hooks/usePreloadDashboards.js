import commonConfig from 'app/components/commonConfig';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import { useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';

const CONCURRENCY = 2;
const PRELOAD_DELAY_MS = 8000; // wait after login before starting preload

export default function usePreloadDashboards() {
  const dispatch = useDispatch();
  const uaClients = useSelector((state) => state.userAccess.uaClients);
  const userPermission = useSelector((state) => state.userType.userIsA);

  useEffect(() => {
    if (!Array.isArray(uaClients) || uaClients.length === 0) return;

    const authToken = getAccessToken();
    if (!authToken) return;

    // Build a queue of client IDs to pre-fetch
    const queue = uaClients
      .map((c) => c?.client_id ?? c?.id)
      .filter(Boolean);

    if (queue.length === 0) return;

    let index = 0;
    let active = 0;
    let cancelled = false;

    const fetchClient = async (clientId) => {
      try {
        const response = await axios.post(
          commonConfig.urls.getLookerDashboard,
          { client_id: clientId, dashboard_id: null, flag: 1, permission: userPermission },
          { headers: { Authorization: `Bearer ${authToken}`, 'Content-Type': 'application/json' } }
        );

        if (cancelled) return;

        if (response?.data?.Response) {
          Object.values(response.data.Response).forEach((folderDashboards) => {
            if (!Array.isArray(folderDashboards)) return;
            folderDashboards.forEach((dash) => {
              const url = dash?.embed_url || dash?.url;
              const dashId = dash?.dash_id;
              if (url && dashId) {
                dispatch({
                  type: 'SET_PRELOADED_URL',
                  key: `${clientId}_${dashId}`,
                  url,
                });
              }
            });
          });
        }
      } catch {
        // Silently skip — pre-fetch failures don't block navigation
      }
    };

    // Round-robin: keep CONCURRENCY slots busy, pulling next client when one finishes
    const next = () => {
      while (!cancelled && active < CONCURRENCY && index < queue.length) {
        const clientId = queue[index++];
        active++;
        fetchClient(clientId).finally(() => {
          active--;
          next();
        });
      }
    };

    const delayTimer = setTimeout(() => {
      if (!cancelled) next();
    }, PRELOAD_DELAY_MS);

    return () => {
      cancelled = true;
      clearTimeout(delayTimer);
    };
  }, [uaClients, userPermission, dispatch]);
}
