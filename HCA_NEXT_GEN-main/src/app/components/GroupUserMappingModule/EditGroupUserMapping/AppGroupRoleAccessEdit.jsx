import { MenuItem } from '@mui/material';
import commonConfig from 'app/components/commonConfig';
import useApi from 'app/hooks/useApi';
import useApiOnce from 'app/hooks/useApiOnce';
import axios from 'axios';
import { useFormikContext } from 'formik';
import React, { useEffect, useState } from 'react';

import AppThemeTextField from 'app/components/ReusableComponents/AppThemeTextField';
import { getAccessToken } from 'app/utils/utils';
import groupUserMappingUtils from '../util';
import SnackbarUtils from 'SnackbarUtils';

export default function AppGroupRoleAccessEdit({ userMappingId }) {
  const [isMounted, setIsMounted] = useState(false);
  const { setFieldValue, values, handleBlur, handleChange, errors, touched } = useFormikContext();
  const { data: userGroups } = useApiOnce(commonConfig.urls.groupList);
  const { data: userGroupRoles } = useApi(
    commonConfig.urls.groupRoleMapping + '/' + values.group_id
  );
  const verifyErrors = (errors, touched, fieldName) => {
    if (Boolean(touched[fieldName] && errors[fieldName]))
      return <div style={{ color: 'red' }}>* {errors[fieldName]}</div>;
    return null;
  };

  const authToken = getAccessToken();
  React.useEffect(() => {
    setIsMounted(true);
  }, []);

  useEffect(() => {
    if (isMounted) {
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
    } else {
      const fetchData = async () => {
        try {
          const response = await axios(
            `${commonConfig.urls.getUserAccessControl}/${userMappingId}`,
            {
              headers: {
                Authorization: `Bearer ${authToken}`,
                'Content-Type': 'application/json',
              },
            }
          );
          if (response && response['data'] && response['data']['Response']) {
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
                response['data']['Response']['dashboard_access']
              )
            );
          }
        } catch (error) {
          SnackbarUtils.error(error?.message || 'Something went wrong!!');
        }
      };
      fetchData();
    }
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
            setFieldValue('group_id', e.target.value);
            setFieldValue('role_id', null);
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
          onChange={handleChange('role_id')}
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
