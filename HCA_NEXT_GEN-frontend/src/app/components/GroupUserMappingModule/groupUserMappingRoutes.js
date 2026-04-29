import Loadable from '../Loadable';
import { lazy } from 'react';
import commonRoutes from '../commonRoutes';

const AppGroupUserMappingList = Loadable(
  lazy(() => import('../../components/GroupUserMappingModule/GroupUserMappingList'))
);
const AppGroupUserMappingCreate = Loadable(
  lazy(() =>
    import('../../components/GroupUserMappingModule/CreateGroupUserMapping/CreateGroupUserMapping')
  )
);
const AppGroupUserMappingEdit = Loadable(
  lazy(() =>
    import('../../components/GroupUserMappingModule/EditGroupUserMapping/EditGroupUserMapping')
  )
);

const groupUserMappingModuleRoutes = [
  {
    path: commonRoutes.groupUserMapping.groupUserMappinglist,
    element: <AppGroupUserMappingList />,
  },
  {
    path: commonRoutes.groupUserMapping.groupUserMappingAdd,
    element: <AppGroupUserMappingCreate />,
  },
  {
    path: commonRoutes.groupUserMapping.groupUserMappingEdit,
    element: <AppGroupUserMappingEdit />,
  },
];

export default groupUserMappingModuleRoutes;
