import { Box, styled, Tab, Tabs } from '@mui/material';
import React, { useState } from 'react';
import UserList from './UserList/UserList';
import { Breadcrumb } from 'app/components';
import { useSelector } from 'react-redux';

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
  '& .breadcrumb': {
    marginBottom: '30px',
    [theme.breakpoints.down('sm')]: { marginBottom: '16px' },
  },
}));

const AppUserSplitList = () => {
  const [tabIndex, setTabIndex] = useState(0);
  const userApprovePermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.approval_pending_user
  );

  const userLists = userApprovePermission ? ['Active', 'Approval Pending'] : ['Active'];

  return (
    <>
      <Box className="breadcrumb" sx={{ m: 1 }}>
        <Breadcrumb routeSegments={[{ name: 'User List' }]} />
      </Box>
      <Container className="analytics">
        <Tabs
          value={tabIndex}
          textColor="secondary"
          indicatorColor="secondary"
          onChange={(e, value) => setTabIndex(value)}
          sx={{ mt: 2, mb: 3 }}
        >
          {userLists.map((item, ind) => (
            <Tab
              key={ind}
              value={ind}
              label={item}
              sx={{ px: '35px', textTransform: 'capitalize' }}
            />
          ))}
        </Tabs>

        {tabIndex === 0 && <UserList flag={0} />}
        {tabIndex === 1 && <UserList flag={1} />}
      </Container>
    </>
  );
};

export default AppUserSplitList;
