export const SET_USERACCESS_PERMISSIONS = 'SET_USERACCESS_PERMISSIONS';
export const RESET_USERACCESS_PERMISSIONS = 'RESET_USERACCESS_PERMISSIONS';
const initialState = {
  userPermissions: null,
};

export default (state = initialState, action) => {
  switch (action.type) {
    case SET_USERACCESS_PERMISSIONS:
      const { userPermissions } = action;
      return {
        ...state,
        userPermissions,
      };
    case RESET_USERACCESS_PERMISSIONS:
      return {
        userPermissions: null,
      };
    default:
      return state;
  }
};
