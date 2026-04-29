import Loadable from '../Loadable';
import { lazy } from 'react';
import commonRoutes from '../commonRoutes';

const AppUserProfile = Loadable(
  lazy(() => import('../../components/UserProfileModule/UserProfile'))
);
const AppEditUserProfile = Loadable(
  lazy(() => import('../../components/UserProfileModule/EditUserProfile'))
);

const userProfileRoutes = [
  {
    path: commonRoutes.userProfile.viewProfile,
    element: <AppUserProfile />,
  },
  {
    path: commonRoutes.userProfile.editProfile,
    element: <AppEditUserProfile />,
  },
];

export default userProfileRoutes;
