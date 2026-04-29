import commonRoutes from 'app/components/commonRoutes';
import Loadable from 'app/components/Loadable';
import { lazy } from 'react';

const NotFound = Loadable(lazy(() => import('./NotFound')));
const ForgotPassword = Loadable(lazy(() => import('./ForgotPassword')));
const JwtLogin = Loadable(lazy(() => import('./JwtLogin')));
const JwtRegister = Loadable(lazy(() => import('./JwtRegister')));
const ValidateOtp = Loadable(lazy(() => import('./ValidateOtp')));
const ChangePassword = Loadable(lazy(() => import('./ChangePassword')));
const AppLandingPage = Loadable(lazy(() => import('../../components/AppLandingPage')));

const sessionRoutes = [
  { path: commonRoutes.session.signup, element: <JwtRegister /> },
  { path: commonRoutes.session.signin, element: <JwtLogin /> },
  { path: commonRoutes.session.forgot_password, element: <ForgotPassword /> },
  { path: commonRoutes.session.validateotp, element: <ValidateOtp /> },
  { path: commonRoutes.session.change_password, element: <ChangePassword /> },
  {
    path: commonRoutes.landingPage.defaultLandingPage,
    element: <JwtLogin />,
  },
  { path: commonRoutes.session.notFound, element: <NotFound /> },
];

export default sessionRoutes;
