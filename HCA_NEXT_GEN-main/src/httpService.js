import commonConfig from 'app/components/commonConfig';
import axios from 'axios';

// axios.create({
//   baseURL: commonConfig.urls.baseURL,
// });

// axios.interceptors.response.use((success, error) => {
//   console.log('success', success);
//   return Promise.resolve(success);
// });

// axios.interceptors.response.use(null, (error) => {
//   const expectedError =
//     error.response && error.response.status >= 400 && error.response.status < 500;
//   if (expectedError) return Promise.resolve(error);
// });

const httpService = {
  get: axios.get,
  post: axios.post,
  patch: axios.patch,
  put: axios.put,
  delete: axios.get,
};

export default httpService;
