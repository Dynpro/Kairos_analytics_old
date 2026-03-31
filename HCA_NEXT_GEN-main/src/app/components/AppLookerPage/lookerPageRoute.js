import Loadable from '../Loadable';
import { lazy } from 'react';
import commonRoutes from '../commonRoutes';

const AppLookerList = Loadable(lazy(() => import('./AppLookerList')));
const AppLookerPage = Loadable(lazy(() => import('./AppLookerPage')));

const lookerPageRoute = [
  {
    path: commonRoutes.lookerPage.defaultLookerList,
    element: <AppLookerList />,
  },
  {
    path: commonRoutes.lookerPage.defaultLookerPage,
    element: <AppLookerPage />,
  },
];

export default lookerPageRoute;
