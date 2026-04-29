import Loadable from '../Loadable';
import { lazy } from 'react';
import commonRoutes from '../commonRoutes';

const AppGroupMasterList = Loadable(lazy(() => import('./GroupList')));
const AppGroupMasterCreate = Loadable(lazy(() => import('./CreateGroupMaster/CreateGroupMaster')));
const AppGroupMasterEdit = Loadable(lazy(() => import('./EditGroupMaster/EditGroupMaster')));

const GroupMasterModuleRoutes = [
  {
    path: commonRoutes.groupMaster.groupMasterlist,
    element: <AppGroupMasterList />,
  },
  {
    path: commonRoutes.groupMaster.groupMasterAdd,
    element: <AppGroupMasterCreate />,
  },
  {
    path: commonRoutes.groupMaster.groupMasterEdit,
    element: <AppGroupMasterEdit />,
  },
];

export default GroupMasterModuleRoutes;
