import Loadable from '../Loadable';
import { lazy } from 'react';
import commonRoutes from '../commonRoutes';

const AppClientList = Loadable(lazy(() => import('./ClientList')));
const AppCreateClient = Loadable(lazy(() => import('./CreateClient')));
const AppEditClient = Loadable(lazy(() => import('./EditClient')));

const clientModuleRoutes = [
  {
    path: commonRoutes.clients.clientList,
    element: <AppClientList />,
  },
  {
    path: commonRoutes.clients.clientAdd,
    element: <AppCreateClient />,
  },
  {
    path: commonRoutes.clients.clientEdit,
    element: <AppEditClient />,
  },
];

export default clientModuleRoutes;
