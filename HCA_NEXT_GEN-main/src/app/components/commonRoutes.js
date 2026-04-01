const commonRoutes = {
  dashboard: '/dashboard',
  home: '/home',
  referral: '/referral',
  QRCodePage: '/qr-code-page',
  Otppage: '/otp-page',
  session: {
    signup: '/signup',
    signin: 'kairos/analytics',
    login: '/login',
    forgot_password: '/forgot-password',
    validateotp: '/validateotp',
    change_password: '/change-password',
    notFound: '/404',
  },
  users: {
    usertablist: '/splitUsers',
    userlist: '/users',
    userAdd: '/users/add',
    userDelete: '/users/delete',
    userEdit: '/users/edit',
  },
  roles: {
    roleList: '/role',
    roleAdd: '/role/add',
    roleDelete: '/role/delete',
    roleEdit: '/role/edit',
  },

  groupMaster: {
    groupMasterlist: '/group-master',
    groupMasterAdd: '/group-master/add',
    groupMasterDelete: '/group-master/delete',
    groupMasterEdit: '/group-master/edit',
  },
  groupUserMapping: {
    groupUserMappinglist: '/groups',
    groupUserMappingAdd: '/groups/add',
    groupUserMappingDelete: '/groups/delete',
    groupUserMappingEdit: '/groups/edit',
  },
  lookerDashboards: {},
  navigations: {},
  landingPage: {
    defaultLandingPage: 'kairos/analytics',
  },
  lookerPage: {
    defaultLookerList: '/lookerList',
    defaultLookerPage: '/lookerPage',
  },
  reports: {
    reportsList: '/reports',
    reportsAdd: '/reports/add',
    reportsDelete: '/reports/delete',
    reportsEdit: '/reports/edit',
  },
  generate_reports: {
    generate_reportsTabList: '/splitGenerateReports',
    generate_reportsList: '/generate_reports',
    generate_reportsAdd: '/generate_reports/add',
    generate_reportsDelete: '/generate_reports/delete',
    generate_reportsEdit: '/generate_reports/edit',
    generate_sum_reportsAdd: '/generate_summary_reports/add',
  },
  clients: {
    clientList: '/client',
    clientAdd: '/client/add',
    clientDelete: '/client/delete',
    clientEdit: '/client/edit',
  },
  inviteUser: '/inviteUser',
  userProfile: {
    viewProfile: '/profile',
    editProfile: '/profile/edit',
  },
  localizaion: {
    localizationSplitList: '/localization',
  },
};

export default commonRoutes;
