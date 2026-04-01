import Loadable from '../Loadable';
import { lazy } from 'react';
import commonRoutes from '../commonRoutes';

const AppLocalizationSplitList = Loadable(lazy(() => import('./AppLocalizationSplitList')));
const localizationModuleRoutes = [
  //   {
  //     path: commonRoutes.users.userlist,
  //     element: <AppUserList />,
  //   },
  //   {
  //     path: commonRoutes.users.userAdd,
  //     element: <AppUserCreate />,
  //   },
  //   {
  //     path: commonRoutes.users.userEdit,
  //     element: <AppUserEdit />,
  //   },
  {
    path: commonRoutes.localizaion.localizationSplitList,
    element: <AppLocalizationSplitList />,
  },
];

export default localizationModuleRoutes;
