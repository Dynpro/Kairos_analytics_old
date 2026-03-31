import Loadable from '../Loadable';
import { lazy } from 'react';
import commonRoutes from '../commonRoutes';

const AppAllReportsList = Loadable(lazy(() => import('./AppAllReportsList')));
const AppAllReportsCreate = Loadable(lazy(() => import('./CreateAllReports/CreateAllReports')));
const AppAllReportsEdit = Loadable(lazy(() => import('./EditAllReports/EditAllReports')));

const reportsModuleRoutes = [
  {
    path: commonRoutes.reports.reportsList,
    element: <AppAllReportsList />,
  },
  {
    path: commonRoutes.reports.reportsAdd,
    element: <AppAllReportsCreate />,
  },
  {
    path: commonRoutes.reports.reportsEdit,
    element: <AppAllReportsEdit />,
  },
];

export default reportsModuleRoutes;
