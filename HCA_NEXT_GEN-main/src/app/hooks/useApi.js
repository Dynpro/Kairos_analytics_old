import { useEffect, useState } from 'react';
import axios from 'axios';
import { getAccessToken } from 'app/utils/utils';
import SnackbarUtils from 'SnackbarUtils';

export default function useApi(url) {
  const [data, setData] = useState([]);
  const authToken = getAccessToken();

  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await axios(url, {
          headers: {
            Authorization: `Bearer ${authToken}`,
            'Content-Type': 'application/json',
          },
        });
        if (response && response['data'] && response['data']['Response'])
          setData(response['data']['Response']);
      } catch (error) {
        SnackbarUtils.error(error?.message || 'Something went wrong!!');
      }
    };
    fetchData();
  }, [url, authToken]);
  return { data };
}
