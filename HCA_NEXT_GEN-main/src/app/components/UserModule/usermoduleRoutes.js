import Loadable from "../Loadable";
import { lazy } from "react";
import commonRoutes from "../commonRoutes";

const AppUserList = Loadable(
  lazy(() => import("../../components/UserModule/UserList/UserList"))
);
const AppUserOrderedList = Loadable(
  lazy(() => import("../../components/UserModule/UserList/UserOrderedList"))
);
const AppUserCreate = Loadable(
  lazy(() => import("../../components/UserModule/UserCreate/UserCreate"))
);
const AppUserEdit = Loadable(
  lazy(() => import("../../components/UserModule/UserEdit/AppUserEdit"))
);
const AppUserSplitList = Loadable(
  lazy(() => import("../../components/UserModule/AppUserSplitList"))
);

const userModuleRoutes = [
  {
    path: commonRoutes.users.userlist,
    element: <AppUserList />,
  },
  {
    path: commonRoutes.users.userAdd,
    element: <AppUserCreate />,
  },
  {
    path: commonRoutes.users.userEdit,
    element: <AppUserEdit />,
  },
  {
    path: commonRoutes.users.usertablist,
    element: <AppUserSplitList />,
  },
];

export default userModuleRoutes;
