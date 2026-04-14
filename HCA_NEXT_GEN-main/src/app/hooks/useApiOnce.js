import { useEffect, useState } from 'react';
import axios from 'axios';
import { getAccessToken } from 'app/utils/utils';
import SnackbarUtils from 'SnackbarUtils';

export default function useApiOnce(url) {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(false);
  const authToken = getAccessToken();

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        const response = await axios(url, {
          headers: {
            Authorization: `Bearer ${authToken}`,
            'Content-Type': 'application/json',
          },
        });
        setLoading(false);
        if (response && response['data'] && response['data']['Response'])
          setData(response['data']['Response']);
      } catch (error) {
        setLoading(false);
        SnackbarUtils.error(error?.message || 'Something went wrong');
      }
    };
    fetchData();
  }, []);
  return { data, loading };
}
