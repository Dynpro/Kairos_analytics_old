import Loadable from '../Loadable';
import { lazy } from 'react';
import commonRoutes from '../commonRoutes';

const AppInviteUserModule = Loadable(lazy(() => import('./AppInviteUserModule')));

const inviteUserRoute = [
  {
    path: commonRoutes.inviteUser,
    element: <AppInviteUserModule />,
  },
];

export default inviteUserRoute;
