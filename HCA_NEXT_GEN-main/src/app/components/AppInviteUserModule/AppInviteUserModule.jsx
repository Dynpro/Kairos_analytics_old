import { Box, Icon, Modal, Typography, useTheme } from '@mui/material';
import SnackbarUtils from 'SnackbarUtils';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import { useReducer } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import AppthemeLoadingBtn from '../ReusableComponents/AppThemeLoadingBtn';
import commonConfig from '../commonConfig';
import './inviteStyles.css';

const style = {
  position: 'absolute',
  top: '35%',
  left: '50%',
  transform: 'translate(-50%, -50%)',
  maxWidth: 700,
  bgcolor: 'background.paper',
  boxShadow: 24,
  p: 4,
};

const defaultGroupCode = '0000';
const defaultState = {
  loading: false,
  open: false,
  responseError: '',
  hasError: false,
  groupCode: defaultGroupCode,
  checked: false,
  formValues: [{ email_id: '' }],
};

function validateEmail(emailIds) {
  const validRegex = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/g;
  return emailIds.every((item) => item['email_id'].match(validRegex));
}

const reducer = (state, action) => {
  switch (action.type) {
    case 'LOADING':
      return { ...state, loading: action.bool };
    case 'OPEN':
      return { ...state, open: action.bool };
    case 'RESPONSE_ERROR':
      return {
        ...state,
        hasError: action.bool,
        responseError: action.error_name,
      };
    case 'SET_GROUPCODE':
      return { ...state, groupCode: action.grp_code };
    case 'SET_CHECKED':
      return { ...state, checked: !state.checked };
    case 'SET_FORMVALUES':
      return { ...state, formValues: action.newFormValues };
    case 'RESET':
      return { ...state, ...action.resetVals, formValues: [{ email_id: '' }] };
    default:
      return state;
  }
};

const AppInviteUserModule = () => {
  const [state, dispatch] = useReducer(reducer, defaultState);
  const { checked, formValues, groupCode } = state;
  const theme = useTheme();

  let handleChange = (i, e) => {
    let newFormValues = [...formValues];
    newFormValues[i][e.target.name] = e.target.value;
    dispatch({ type: 'SET_FORMVALUES', newFormValues });
  };

  let addFormFields = () => {
    dispatch({ type: 'SET_FORMVALUES', newFormValues: [...state.formValues, { email_id: '' }] });
  };

  let removeFormFields = (i) => {
    let newFormValues = [...state.formValues];
    newFormValues.splice(i, 1);
    dispatch({ type: 'SET_FORMVALUES', newFormValues });
  };

  let handleSubmit = (event) => {
    event.preventDefault();
    const finalObj = {
      user_list: formValues.map((val) => ({ ...val, group_code: groupCode })),
    };
    if (validateEmail(finalObj?.user_list)) sendDataToServer(finalObj);
    else SnackbarUtils.error('Recheck Form Fields');
  };
  async function sendDataToServer(data) {
    const authToken = getAccessToken();
    try {
      dispatch({ type: 'LOADING', bool: true });
      const response = await axios.post(commonConfig.urls.inviteUser, data, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      dispatch({ type: 'LOADING', bool: false });
      if (response && response.data.Status === 'Failed') {
        SnackbarUtils.error(response.data.Message);
      }
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data.Message);
        handleClose();
      }
    } catch (error) {
      dispatch({ type: 'LOADING', bool: false });
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
  }
  const dispatchS = useDispatch();
  const open = useSelector((state) => state.modal.openInviteUser);
  const handleClose = () => {
    dispatch({ type: 'RESET', resetVals: { ...defaultState } });
    dispatchS({ type: 'SET_OPEN_INVITEUSERMODAL', openInviteUser: false });
  };
  return (
    <Modal open={open} onClose={handleClose}>
      <Box sx={{ ...style, color: theme.palette.text.primary }}>
        <div style={{ position: 'absolute', right: 20, top: 20 }}>
          <Icon onClick={handleClose} style={{ cursor: 'pointer' }}>
            close
          </Icon>
        </div>
        <Typography
          id="modal-header"
          variant="h6"
          component="h2"
          style={{ display: 'flex', justifyContent: 'center' }}
        >
          Invite User
        </Typography>
        <hr />
        <p>Invite users to create account (max 5 emails allowed at a time)</p>
        <hr />
        <form onSubmit={handleSubmit}>
          <div>
            {formValues.map((element, index) => {
              return (
                <div className="form-inline-inv" key={index}>
                  <label>Email</label>
                  <input
                    type="text"
                    name="email_id"
                    value={element.email_id || ''}
                    onChange={(e) => handleChange(index, e)}
                    placeholder="Enter an email address"
                  />

                  <button
                    disabled={formValues.length > 4}
                    type="button"
                    style={{ backgroundColor: theme.palette.success.main }}
                    className="button-inv remove-inv"
                    onClick={() => addFormFields()}
                  >
                    +
                  </button>
                  {index ? (
                    <button
                      type="button"
                      style={{ backgroundColor: theme.palette.error.main }}
                      className="button-inv remove-inv"
                      onClick={() => removeFormFields(index)}
                    >
                      -
                    </button>
                  ) : null}
                </div>
              );
            })}
            <hr />
            <input
              type="checkbox"
              id="groupCode"
              name="groupCode"
              value={checked}
              onChange={() => {
                dispatch({ type: 'SET_CHECKED' });
                dispatch({ type: 'SET_GROUPCODE', grp_code: defaultGroupCode });
              }}
              style={{ marginRight: '10px' }}
            />
            <label htmlFor="groupCode">Group Code</label>
            {checked && (
              <input
                type="text"
                id="name"
                name="name"
                min={4}
                max={4}
                minLength={4}
                maxLength={4}
                size={4}
                value={groupCode}
                style={{ marginLeft: '10px' }}
                onChange={(e) => {
                  dispatch({ type: 'SET_GROUPCODE', grp_code: e.currentTarget.value });
                }}
              />
            )}
          </div>
          <div className="button-section-inv">
            <AppthemeLoadingBtn
              type="submit"
              loading={state.loading}
              variant="contained"
              sx={{ my: 2 }}
              onClick={handleSubmit}
            >
              Submit
            </AppthemeLoadingBtn>
          </div>
        </form>
      </Box>
    </Modal>
  );
};

export default AppInviteUserModule;
