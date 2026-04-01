import { Box } from '@mui/material';
import Button from '@mui/material/Button';
import Step from '@mui/material/Step';
import StepLabel from '@mui/material/StepLabel';
import Stepper from '@mui/material/Stepper';
import Typography from '@mui/material/Typography';
import React from 'react';
import PersonalInfo from './PersonalInfo';
import UserAccess from './UserAccess';
import AppDashboardAccess from 'app/components/ReusableComponents/AppDashboardAccess';
import commonConfig from '../../commonConfig';
import userModuleUtils from '../util';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import { useReducer } from 'react';
import { getAccessToken } from 'app/utils/utils';
import commonRoutes from 'app/components/commonRoutes';
import { Formik } from 'formik';

import SnackbarUtils from 'SnackbarUtils';

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
    default:
      return state;
  }
};

function getSteps() {
  return ['Pesonal Information', 'User Access', 'Dashboard Access'];
}

export default function UserStepperForm() {
  const [state, dispatch] = useReducer(reducer, {
    loading: false,
    open: false,
    responseError: '',
    hasError: false,
  });
  const navigate = useNavigate();
  async function sendDataToServer(data) {
    const authToken = getAccessToken();
    try {
      dispatch({ type: 'LOADING', bool: true });
      const response = await axios.post(commonConfig.urls.user, data, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      dispatch({ type: 'LOADING', bool: false });
      if (response && response.data.Status === 'Failed') {
        SnackbarUtils.error(
          Object.values(response.data.Errors)
            .map((item) => item.toString())
            .toString()
        );
      }
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success('User Created Successfully');
        navigate(commonRoutes.users.usertablist);
      }
    } catch (error) {
      dispatch({ type: 'LOADING', bool: false });
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
  }
  const [activeStep, setActiveStep] = React.useState(0);
  const steps = getSteps();

  const handleNext = () => setActiveStep((prevActiveStep) => prevActiveStep + 1);

  const handleBack = () => setActiveStep((prevActiveStep) => prevActiveStep - 1);

  const handleReset = () => setActiveStep(0);

  /*   function getStepContent(stepIndex) {
    switch (stepIndex) {
      case 0:
        return <PersonalInfo showPassword={true} />;

      case 1:
        return <UserAccess />;

      case 2:
        return <AppDashboardAccess checked={values.checked} onCheck={setFieldValue} />;

      default:
        return <AppDashboardAccess checked={values.checked} onCheck={setFieldValue} />;
    }
  } */

  return (
    <Formik
      initialValues={userModuleUtils.initialValues}
      validationSchema={userModuleUtils.CreateUserSchema}
      onSubmit={(values) => {
        sendDataToServer({
          ...values.personalInfo,
          User_Access: userModuleUtils.retUserAccessObj(values.userAccess),
          ...userModuleUtils.retDashboardAccessObj(values.checked),
        });
      }}
    >
      {({ handleSubmit, values, setFieldValue }) => {
        return (
          <Box sx={{ py: 2 }}>
            <Stepper activeStep={activeStep} alternativeLabel>
              {steps.map((label) => (
                <Step key={label}>
                  <StepLabel>{label}</StepLabel>
                </Step>
              ))}
            </Stepper>

            <Box mt={4}>
              {activeStep === steps.length ? (
                <Box>
                  <Typography>All steps completed</Typography>

                  <Button
                    sx={{ mt: 2 }}
                    variant="contained"
                    color="secondary"
                    onClick={handleReset}
                  >
                    Reset
                  </Button>
                </Box>
              ) : (
                <Box>
                  {/*                   <Typography>{getStepContent(activeStep)}</Typography> */}
                  {activeStep === 0 && <PersonalInfo showPassword={true} />}
                  {activeStep === 1 && <UserAccess />}
                  {activeStep === 2 && (
                    <AppDashboardAccess checked={values.checked} onCheck={setFieldValue} />
                  )}

                  <Box pt={2}>
                    <Button
                      variant="contained"
                      color="secondary"
                      disabled={activeStep === 0}
                      onClick={handleBack}
                    >
                      Back
                    </Button>

                    <Button sx={{ ml: 2 }} variant="contained" color="primary" onClick={handleNext}>
                      {activeStep === steps.length - 1 ? 'Finish' : 'Next'}
                    </Button>
                  </Box>
                </Box>
              )}
            </Box>
          </Box>
        );
      }}
    </Formik>
  );
}
