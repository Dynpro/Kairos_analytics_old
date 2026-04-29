import commonConfig from 'app/components/commonConfig';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import { useDispatch, useSelector } from 'react-redux';

export default function useRefreshData(topbarUpdate = true) {
  const dispatch = useDispatch();
  const user = useSelector((state) => state.userDetails?.user);

  const handleRefreshData = async () => {
    const accessToken = getAccessToken();
    const uaClients = await axios(commonConfig.urls.getUserAccessClients, {
      headers: { Authorization: `Bearer ${accessToken}` },
    });
    dispatch({ type: 'SET_USERACCESS_CLIENTS', uaClients: uaClients['data']['data'] });
    // if (topbarUpdate)
    //   if (Array.isArray(uaClients?.data?.data) && uaClients?.data?.data?.length > 0) {
    //     const { client_id, client_name, category, subcategory } = uaClients?.data?.data[0];
    //     dispatch({
    //       type: 'SET_CLIENT',
    //       client: {
    //         category_name: category,
    //         subCategory_name: subcategory,
    //         client_name,
    //         client_id,
    //         dashboard_name: `Default ${client_name} Dashboard`,
    //       },
    //     });
    //   }
    const [uaSubCategories, uaFolders, uaDashboards, userDetails] = await Promise.all([
      axios(commonConfig.urls.getClientCateWiseUserAccesAllsNew, {
        headers: { Authorization: `Bearer ${accessToken}` },
      }),
      await axios(commonConfig.urls.getUserAccessFolders, {
        headers: { Authorization: `Bearer ${accessToken}` },
      }),
      axios(commonConfig.urls.getUserAccessdashboards, {
        headers: { Authorization: `Bearer ${accessToken}` },
      }),
      axios(commonConfig.urls.user + '/' + user?.id, {
        headers: { Authorization: `Bearer ${accessToken}` },
      }),
    ]);
    //    ]).catch((error) => SnackbarUtils.error(error?.message || 'Something went wrong!!'));
    dispatch({
      type: 'SET_USERACCESS_SUBCATEGORIES',
      uaSubCategories: uaSubCategories['data']['data'],
    });

    dispatch({ type: 'SET_USERACCESS_FOLDERS', uaFolders: uaFolders['data']['data'] });
    dispatch({
      type: 'SET_USERACCESS_DASHBOARDS',
      uaDashboards: uaDashboards['data']['data'],
    });
    dispatch({
      type: 'SET_USERACCESS_PERMISSIONS',
      userPermissions: userDetails.data?.Response?.role_access[0],
    });
    dispatch({
      type: 'SET_USER_PROFILE',
      profile_pic: userDetails.data?.Response?.user_details[0]?.profile_pic,
    });
    dispatch({
      type: 'SET_USER_NAME',
      name: userDetails.data?.Response?.user_details[0]?.name,
      last_name: userDetails.data?.Response?.user_details[0]?.last_name,
    });
  };
  return { handleRefreshData };
}
