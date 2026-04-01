export const SET_OPEN_MODAL = 'SET_OPEN_MODAL';
export const SET_OPEN_INVITEUSERMODAL = 'SET_OPEN_INVITEUSERMODAL';

const initialState = {
  open: false,
  openInviteUser: false,
};

export default (state = initialState, action) => {
  switch (action.type) {
    case SET_OPEN_MODAL:
      const { open } = action;
      return {
        ...state,
        open,
      };
    case SET_OPEN_INVITEUSERMODAL:
      const { openInviteUser } = action;
      return {
        ...state,
        openInviteUser,
      };
    default:
      return state;
  }
};
