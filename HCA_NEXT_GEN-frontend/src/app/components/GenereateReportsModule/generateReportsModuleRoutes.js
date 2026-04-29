import Loadable from '../Loadable';
import { lazy } from 'react';
import commonRoutes from '../commonRoutes';

const AppGenerateReportsList = Loadable(lazy(() => import('./AppGenerateReportsList')));
const AppGenerateReportsCreate = Loadable(lazy(() => import('./CreateGenerateReports')));
const AppGenerateSUMReportsCreate = Loadable(lazy(() => import('./CreateSUMGenerateReports')));
const AppGenerateReportsEdit = Loadable(
  lazy(() => import('./EditGenerateReports/EditGenerateReports'))
);
const AppGenerateReportSplitList = Loadable(lazy(() => import('./AppGenerateReportsSplitList')));

const generateReportsModuleRoutes = [
  {
    path: commonRoutes.generate_reports.generate_reportsList,
    element: <AppGenerateReportsList />,
  },
  {
    path: commonRoutes.generate_reports.generate_reportsAdd,
    element: <AppGenerateReportsCreate />,
  },
  {
    path: commonRoutes.generate_reports.generate_reportsEdit,
    element: <AppGenerateReportsEdit />,
  },
  {
    path: commonRoutes.generate_reports.generate_reportsTabList,
    element: <AppGenerateReportSplitList />,
  },
  {
    path: commonRoutes.generate_reports.generate_sum_reportsAdd,
    element: <AppGenerateSUMReportsCreate />,
  },
];

export default generateReportsModuleRoutes;
