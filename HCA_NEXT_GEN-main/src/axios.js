import axios from 'axios';
import SnackbarUtils from 'SnackbarUtils';

const axiosInstance = axios.create();

axiosInstance.interceptors.response.use(
  (response) => response,
  (error) => {
    // if (error.response?.status === 401) alert('Invalid Email or Password');
    if (error.response?.status === 401)
      SnackbarUtils.error(error.response?.data?.Message || 'Something went wrong!');
    return Promise.reject((error.response && error.response.data) || 'Something went wrong!');
  }
);

export default axiosInstance;
