import Loadable from '../Loadable';
import { lazy } from 'react';
import commonRoutes from '../commonRoutes';

const AppReferalModule = Loadable(lazy(() => import('./index')));

const referralModuleRoutes = [
  {
    path: commonRoutes.referral,
    element: <AppReferalModule />,
  },
];

export default referralModuleRoutes;
