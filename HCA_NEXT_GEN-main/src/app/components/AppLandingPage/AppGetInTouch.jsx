import { Button, Grid, TextField, Typography } from '@mui/material';
import SnackbarUtils from 'SnackbarUtils';
import axios from 'axios';
import { Formik } from 'formik';
import { useRef, useState } from 'react';
import * as Yup from 'yup';
import commonConfig from '../commonConfig';
import ReCAPTCHA from 'react-google-recaptcha';
import { LoadingButton } from '@mui/lab';

const initialValues = {
  name: '',
  email: '',
  subject: '',
  contact: '',
  message: '',
};
const contactRegExp =
  /^((\\+[1-9]{1,4}[ \\-]*)|(\\([0-9]{2,3}\\)[ \\-]*)|([0-9]{2,4})[ \\-]*)*?[0-9]{3,4}?[ \\-]*[0-9]{3,4}?$/;

const validationSchema = Yup.object().shape({
  name: Yup.string().required('Kindly enter your Name').label('Name'),
  email: Yup.string()
    .email('Kindly enter a valid email')
    .required('Kindly enter a valid email')
    .label('Email'),
  subject: Yup.string().required('Kindly enter your Subject').label('Subject'),
  contact: Yup.string()
    .required()
    .matches(contactRegExp, 'Contact number is not valid')
    .label('Contact'),
  message: Yup.string().required('Kindly enter your Message').label('Message'),
});

const verifyErrors = (errorName, touchedName) => {
  if (Boolean(touchedName && errorName)) return <div style={{ color: 'red' }}>* {errorName}</div>;
  return null;
};

const AppGetInTouch = () => {
  const [loading, setLoading] = useState(false);
  const [disableSubmit, setDisableSubmit] = useState(true);

  const captchaRef = useRef(null);

  async function sendDataToServer(data) {
    try {
      setLoading(true);
      const response = await axios.post(commonConfig.urls.getInTouch, data);
      setLoading(false);
      if (response.data?.Error === 'Failed') {
        SnackbarUtils.error(response.data.Message || 'Something went wrong');
      }
      if (response.data?.Status === 'Success') {
        SnackbarUtils.success('Your response has been saved');
      }
    } catch (error) {
      setLoading(false);
      SnackbarUtils.error(error?.Message || 'Something went wrong!!');
    }
  }
  return (
    <Formik
      initialValues={initialValues}
      validationSchema={validationSchema}
      onSubmit={(values) => {
        sendDataToServer(values);
      }}
    >
      {({ errors, touched, values, handleBlur, handleChange, handleSubmit }) => (
        <div
          style={{
            display: 'flex',
            justifyContent: 'center',
            marginTop: '1rem',
            paddingBottom: '75px',
          }}
        >
          <div
            style={{
              width: '100%',
              maxWidth: 600,
              padding: '1.5rem',
              borderRadius: '0.5rem',
            }}
          >
            <h4
              style={{
                fontSize: '50px',
                fontWeight: 600,
                lineHeight: '55px',
                color: '#121212',
                textAlign: 'center',
              }}
            >
              Get In touch
            </h4>
            <form
              id="contact-form"
              action="assets/contact.php"
              method="post"
              data-toggle="validator"
            >
              <Grid container spacing={2}>
                <Grid item xs={12} sm={6}>
                  <TextField
                    autoComplete="off"
                    type="text"
                    name="name"
                    placeholder="Your Name"
                    required
                    onChange={handleChange}
                    onBlur={handleBlur}
                    value={values.name}
                    fullWidth
                  />
                  <div>{verifyErrors(errors.name, touched.name)}</div>
                </Grid>
                <Grid item xs={12} sm={6}>
                  <TextField
                    type="email"
                    name="email"
                    placeholder="Your Email"
                    required
                    onChange={handleChange}
                    onBlur={handleBlur}
                    value={values.email}
                    fullWidth
                  />
                  <div>{verifyErrors(errors.email, touched.email)}</div>
                </Grid>
                <Grid item xs={12} sm={6}>
                  <TextField
                    autoComplete="off"
                    type="text"
                    name="subject"
                    placeholder="Subject"
                    required
                    onChange={handleChange}
                    onBlur={handleBlur}
                    value={values.subject}
                    fullWidth
                  />
                  <div>{verifyErrors(errors.subject, touched.subject)}</div>
                </Grid>
                <Grid item xs={12} sm={6}>
                  <TextField
                    autoComplete="off"
                    type="text"
                    name="contact"
                    placeholder="Contact"
                    required
                    onChange={handleChange}
                    onBlur={handleBlur}
                    value={values.contact}
                    fullWidth
                  />
                  <div>{verifyErrors(errors.contact, touched.contact)}</div>
                </Grid>
                <Grid item xs={12}>
                  <TextField
                    multiline
                    rows={4}
                    placeholder="Your Message"
                    name="message"
                    required
                    onChange={handleChange}
                    onBlur={handleBlur}
                    value={values.message}
                    fullWidth
                  />
                  <div>{verifyErrors(errors.message, touched.message)}</div>
                </Grid>
                <Grid item xs={12}>
                  <Typography className="form-message" />
                </Grid>
                <Grid item xs={12}>
                  <div style={{ display: 'flex', justifyContent: 'center' }}>
                    <ReCAPTCHA
                      sitekey={process.env.REACT_APP_SITE_KEY}
                      ref={captchaRef}
                      onChange={(value) => {
                        if (value) setDisableSubmit(false);
                      }}
                      onExpired={() => setDisableSubmit(true)}
                    />
                  </div>
                </Grid>

                <Grid item xs={12}>
                  <div style={{ display: 'flex', justifyContent: 'center' }}>
                    <LoadingButton
                      disabled={
                        !(
                          Object.keys(errors).length === 0 &&
                          Boolean(touched.name) &&
                          Boolean(touched.email) &&
                          Boolean(touched.subject) &&
                          Boolean(touched.message) &&
                          !disableSubmit
                        )
                      }
                      type="submit"
                      color="success"
                      loading={loading}
                      variant="contained"
                      onClick={handleSubmit}
                      sx={{ my: 2 }}
                    >
                      {loading ? 'Sending...' : 'Send Message'}
                    </LoadingButton>
                  </div>
                </Grid>
              </Grid>
            </form>
          </div>
        </div>
      )}
    </Formik>
  );
};

export default AppGetInTouch;
