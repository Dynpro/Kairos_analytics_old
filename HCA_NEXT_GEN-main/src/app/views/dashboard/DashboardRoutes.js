import Loadable from 'app/components/Loadable';
import { lazy } from 'react';
import { authRoles } from '../../auth/authRoles';
import commonRoutes from 'app/components/commonRoutes';

const Analytics = Loadable(lazy(() => import('./Analytics')));

const dashboardRoutes = [
  { path: `${commonRoutes.dashboard}/:id`, element: <Analytics />, auth: authRoles.admin },
  { path: commonRoutes.dashboard, element: <Analytics />, auth: authRoles.admin },
];

export default dashboardRoutes;
