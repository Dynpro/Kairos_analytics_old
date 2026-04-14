export const SET_USERACCESS_CLIENTS = 'SET_USERACCESS_CLIENTS';
export const SET_USERACCESS_CATEGORIES = 'SET_USERACCESS_CATEGORIES';
export const SET_USERACCESS_SUBCATEGORIES = 'SET_USERACCESS_SUBCATEGORIES';
export const SET_USERACCESS_FOLDERS = 'SET_USERACCESS_FOLDERS';
export const SET_USERACCESS_DASHBOARDS = 'SET_USERACCESS_DASHBOARDS';
export const SET_ALL_CLIENTS = 'SET_ALL_CLIENTS';
export const SET_ALL_SUBCATEGORIES = 'SET_ALL_SUBCATEGORIES';
export const SET_ALL_CATEGORIES = 'SET_ALL_CATEGORIES';
export const SET_ALL_FOLDERS = 'SET_ALL_FOLDERS';
export const SET_ALL_DASHBOARDS = 'SET_ALL_DASHBOARDS';
export const RESET_ALL_LOOKER_DATA = 'RESET_ALL_LOOKER_DATA';

const initialState = {
  //  uaCategories: {},
  uaSubCategories: {},
  uaClients: [],
  uaFolders: {},
  uaDashboards: {},
  allClients: [],
  // allCategories: {},
  allSubCategories: {},
  allFolders: {},
  allDashboards: {},
};

export default (state = initialState, action) => {
  const {
    // uaCategories,
    uaSubCategories,
    uaClients,
    uaFolders,
    uaDashboards,
    allClients,
    allSubCategories,
    // allCategories,
    allFolders,
    allDashboards,
  } = action;

  switch (action.type) {
    case SET_USERACCESS_CLIENTS:
      return {
        ...state,
        uaClients,
      };
    // case SET_USERACCESS_CATEGORIES:
    //   return {
    //     ...state,
    //     uaCategories,
    //   };
    case SET_USERACCESS_SUBCATEGORIES:
      return {
        ...state,
        uaSubCategories,
      };
    case SET_USERACCESS_FOLDERS:
      return {
        ...state,
        uaFolders,
      };
    case SET_USERACCESS_DASHBOARDS:
      return {
        ...state,
        uaDashboards,
      };
    // case SET_ALL_CATEGORIES:
    //   return {
    //     ...state,
    //     allCategories,
    //   };
    case SET_ALL_SUBCATEGORIES:
      return {
        ...state,
        allSubCategories,
      };
    case SET_ALL_CLIENTS:
      return {
        ...state,
        allClients,
      };
    case SET_ALL_FOLDERS:
      return {
        ...state,
        allFolders,
      };
    case SET_ALL_DASHBOARDS:
      return {
        ...state,
        allDashboards,
      };
    case RESET_ALL_LOOKER_DATA:
      return {
        ...state,
        ...initialState,
      };
    default:
      return state;
  }
};
