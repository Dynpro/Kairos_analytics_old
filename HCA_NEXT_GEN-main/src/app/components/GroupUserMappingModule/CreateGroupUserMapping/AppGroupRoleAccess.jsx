import commonConfig from 'app/components/commonConfig';
import useApi from 'app/hooks/useApi';
import useApiOnce from 'app/hooks/useApiOnce';
import axios from 'axios';
import { useFormikContext } from 'formik';
import { useEffect } from 'react';

import { MenuItem } from '@mui/material';
import AppThemeTextField from 'app/components/ReusableComponents/AppThemeTextField';
import { getAccessToken } from 'app/utils/utils';
import groupUserMappingUtils from '../util';
import SnackbarUtils from 'SnackbarUtils';

export default function AppGroupRoleAccess() {
  const { setFieldTouched, setFieldValue, values, handleBlur, errors, touched } =
    useFormikContext();
  const { data: users } = useApiOnce(commonConfig.urls.user);
  const { data: userGroups } = useApiOnce(commonConfig.urls.groupList);
  const { data: userGroupRoles } = useApi(
    commonConfig.urls.groupRoleMapping + '/' + values.group_id
  );

  const authToken = getAccessToken();
  const verifyErrors = (errors, touched, fieldName) => {
    if (Boolean(touched[fieldName] && errors[fieldName]))
      return <div style={{ color: 'red' }}>* {errors[fieldName]}</div>;
    return null;
  };

  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await axios(
          `${commonConfig.urls.getGroupRoleUser}?group_id=${values.group_id}&role_id=${values.role_id}`,
          {
            headers: {
              Authorization: `Bearer ${authToken}`,
              'Content-Type': 'application/json',
            },
          }
        );
        if (response && response['data'] && response['data']['Response']) {
          setFieldValue(
            'finalUserlist',
            groupUserMappingUtils
              .getCheckedUsers(users, response['data']['Response']['user_role_mapping'])
              .filter((item) => item.checked === true)
          );
          setFieldValue(
            'Userslist',
            groupUserMappingUtils.getCheckedUsers(
              users,
              response['data']['Response']['user_role_mapping']
            )
          );
          setFieldValue(
            'userAccess',
            groupUserMappingUtils.getRoleAccess(
              values.userAccess,
              response['data']['Response']['role_access'][0] || []
            )
          );
          setFieldValue(
            'checked',
            groupUserMappingUtils.retDashboardAccessObjsToCheckedString(
              response['data']['Response']['dashboard_mapping']
            )
          );
        }
      } catch (error) {
        SnackbarUtils.error(error?.message || 'Something went wrong!!');
      }
    };
    fetchData();
  }, [values.role_id]);
  return (
    <>
      <div>
        <AppThemeTextField
          defaultValue={''}
          id="group_id"
          name="group_id"
          value={values.group_id || ''}
          style={{ width: '100%' }}
          select
          label="Groups"
          placeholder="Select Group"
          onChange={(e) => {
            setFieldValue('finalUserlist', []);
            setFieldValue('Userslist', []);
            setFieldValue('group_id', e.target.value);
            setFieldValue('role_id', null);
            setTimeout(() => setFieldTouched('group_id', true));
          }}
          onBlur={handleBlur}
          error={Boolean(errors.group_id && touched.group_id)}
        >
          {userGroups.map((option, index) => (
            <MenuItem key={index} value={option.group_id}>
              {option.group_name}
            </MenuItem>
          ))}
        </AppThemeTextField>
        {verifyErrors(errors, touched, 'group_id')}
      </div>
      <div>
        <AppThemeTextField
          id="role_id"
          name="role_id"
          required
          defaultValue={''}
          value={values.role_id || ''}
          style={{ width: '100%' }}
          select
          label="Roles"
          placeholder="Select Roles"
          onChange={(e) => {
            setFieldValue('role_id', e.target.value);
            setFieldValue('Userslist', []);
            setFieldValue('finalUserlist', []);
          }}
          onBlur={handleBlur}
          error={Boolean(errors.role_id && touched.role_id && touched.role_id && errors.role_id)}
        >
          {userGroupRoles.map((option, index) => (
            <MenuItem key={index} value={option.role_id}>
              {option.role}
            </MenuItem>
          ))}
        </AppThemeTextField>
        {verifyErrors(errors, touched, 'role_id')}
      </div>
    </>
  );
}
