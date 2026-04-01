import AuthGuard from 'app/auth/AuthGuard';
import dashboardRoutes from 'app/views/dashboard/DashboardRoutes';
import NotFound from 'app/views/sessions/NotFound';
import sessionRoutes from 'app/views/sessions/SessionRoutes';
import { Navigate } from 'react-router-dom';
import Home from './components/Home';
import inviteUserRoute from './components/AppInviteUserModule/inviteUserRoute';
import lookerPageRoute from './components/AppLookerPage/lookerPageRoute';
import clientModuleRoutes from './components/ClientModule/clientModuleRoutes';
import generateReportsModuleRoutes from './components/GenereateReportsModule/generateReportsModuleRoutes';
import groupMasterModuleRoutes from './components/GroupMasterModule/groupMasterModuleRoutes';
import groupUserMappingModuleRoutes from './components/GroupUserMappingModule/groupUserMappingRoutes';
import localizationModuleRoutes from './components/LocalizationModule/localizationModuleRoutes';
import MatxLayout from './components/MatxLayout/MatxLayout';
import reportsModuleRoutes from './components/ReportsModule/reportsModuleRoutes';
import roleModuleRoutes from './components/Role Module/roleModuleRoutes';
import userModuleRoutes from './components/UserModule/usermoduleRoutes';
import userProfileRoutes from './components/UserProfileModule/UserProfileRoutes';
import referralModuleRoutes from './components/AppReferalModule/referralModuleRoutes';
import commonRoutes from './components/commonRoutes';
import QRCodePage from 'app/views/sessions/QRCodePage';
import OtpPage from 'app/views/sessions/OtpPage';
// modified by abhi
import automationUIRoutes from './components/AutomationUI/automationUIRoutes';

const routes = [
  {
    element: (
      <AuthGuard>
        <MatxLayout />
      </AuthGuard>
    ),
    children: [
      ...clientModuleRoutes,
      ...dashboardRoutes,
      ...generateReportsModuleRoutes,
      ...groupMasterModuleRoutes,
      ...groupUserMappingModuleRoutes,
      ...inviteUserRoute,
      ...localizationModuleRoutes,
      ...lookerPageRoute,
      ...referralModuleRoutes,
      ...reportsModuleRoutes,
      ...roleModuleRoutes,
      ...userProfileRoutes,
      ...userModuleRoutes,
      ...automationUIRoutes,
      { path: commonRoutes.home, element: <Home /> },
    ],
  },
  ...sessionRoutes,
  { path: '/signin', element: <Navigate to={commonRoutes.landingPage.defaultLandingPage} /> },
  { path: '/login', element: <Navigate to={commonRoutes.landingPage.defaultLandingPage} /> },
  { path: '/hca_landing', element: <Navigate to={commonRoutes.landingPage.defaultLandingPage} /> },
  { path: '/qr-code-page', element: <QRCodePage /> }, 
  { path: '/otp-page', element: <OtpPage /> }, 
  { path: '/', element: <Navigate to={commonRoutes.home} /> },
  { path: '*', element: <NotFound /> },
];

export default routes;
