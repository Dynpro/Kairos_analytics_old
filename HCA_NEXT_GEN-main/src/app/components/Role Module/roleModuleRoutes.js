import Loadable from '../Loadable';
import { lazy } from 'react';
import commonRoutes from '../commonRoutes';

const AppRoleList = Loadable(lazy(() => import('./RoleList/RoleList')));
const AppRoleCreate = Loadable(lazy(() => import('./Role Create/RoleCreate')));

const AppRoleEdit = Loadable(lazy(() => import('./RoleEdit/AppRoleEdit')));

const roleModuleRoutes = [
  {
    path: commonRoutes.roles.roleList,
    element: <AppRoleList />,
  },
  {
    path: commonRoutes.roles.roleAdd,
    element: <AppRoleCreate />,
  },
  {
    path: commonRoutes.roles.roleEdit,
    element: <AppRoleEdit />,
  },
];

export default roleModuleRoutes;
