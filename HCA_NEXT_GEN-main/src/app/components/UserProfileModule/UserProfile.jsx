import {
  Button,
  Card,
  Link,
  TextField,
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import { Edit, Email, Group, Work } from '@mui/icons-material';
import { Badge, Box, Modal, styled, useTheme } from '@mui/material';
import IconButton from '@mui/material/IconButton';
import React, { Fragment, useEffect, useState } from 'react';
import axios from 'axios';
import { H4, Small } from 'app/components/Typography';
import { getAccessToken } from 'app/utils/utils';
import commonConfig from '../commonConfig';
import EditUserProfile from './EditUserProfile';
import ResetPassword from './ResetPassword';
import SnackbarUtils from 'SnackbarUtils';

const style = {
  position: 'absolute',
  top: '45%',
  left: '50%',
  transform: 'translate(-50%, -50%)',
  width: 500,
  bgcolor: 'background.paper',
  boxShadow: 24,
  p: 4,
};

const StyledBadge = styled(Badge)(({ theme, width, height }) => ({
  '& .MuiBadge-badge': {
    width: width,
    height: height,
    borderRadius: '50%',
    backgroundColor: 'dodgerblue',
    boxShadow: `0 0 0 2px ${theme.palette.background.paper}`,
  },
  '& .MuiBadge-colorSuccess.MuiBadge-badge': {
    backgroundColor: theme.palette.success.main,
    boxShadow: `0 0 0 1px ${theme.palette.background.paper}`,
  },
}));

const AvatarBadge = ({ children, width, height, ...props }) => {
  return (
    <StyledBadge
      width={width || 25}
      height={height || 25}
      overlap="circular"
      anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
      {...props}
    >
      {children}
    </StyledBadge>
  );
};

const FlexBox = styled(Box)({ display: 'flex' });

const FlexBetween = styled(Box)({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
});
const FlexCenter = styled(Box)({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
});

// styled components
const ContentWrapper = styled(Box)(({ theme }) => ({
  zIndex: 1,
  marginTop: 55,
  position: 'relative',
  [theme.breakpoints.down('sm')]: { paddingLeft: 20, paddingRight: 20 },
}));

const CoverPicWrapper = styled(Box)(() => ({
  top: 0,
  left: 0,
  height: 125,
  width: '100%',
  overflow: 'hidden',
  position: 'absolute',
  backgroundColor: '#C6D3ED',
}));

const ImageWrapper = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',

  width: 100,
  height: 100,
  margin: 'auto',
  overflow: 'hidden',
  borderRadius: '50%',
  border: '2px solid',
  borderColor: 'white',
  backgroundColor: theme.palette.primary[200],
}));

const UserProfile = () => {
  const [userObj, setData] = useState([]);
  const [loading, setLoading] = useState(false);
  const [openDialog, setOpenDialog] = useState(false); // State for dialog
  const [openConfirmationDialog, setOpenConfirmationDialog] = useState(false);

  const authToken = getAccessToken();

  const FetchOtp = async (values) => {
    setLoading(true);
    try {
      const response = await axios.post(commonConfig.urls.forget_password, {
        email: userObj?.email,
        entity_id: process.env.REACT_APP_env_entity_id,
      });
      setLoading(false);
      if (response.data && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data.Message);
        setLoading(false);
      } else {
        SnackbarUtils.error('Email Not Available...!!!');
      }
    } catch (error) {
      setLoading(false);
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      setLoading(true);
      const response = await axios(commonConfig.urls.getUserProfile, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        },
      });
      setLoading(false);
      if (response && response['data'] && response['data']['Response'])
        setData(response['data']['Response']);
    } catch (error) {
      setLoading(false);
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
  };

  const [openModal, setOpenModal] = useState(false);
  const [openRegisterModal, setOpenRegisterModal] = useState(false);
  const theme = useTheme();

  return (
    <Fragment>
      <Card sx={{ padding: 3, position: 'relative' }}>
        <CoverPicWrapper>
          <img
            width="100%"
            height="100%"
            alt="Team Member"
            src="/assets/images/study-2.jpg"
            style={{ objectFit: 'cover' }}
          />
        </CoverPicWrapper>
        <ContentWrapper>
          <FlexBox justifyContent="center">
            <AvatarBadge
              badgeContent={
                <IconButton
                  aria-label="upload picture"
                  component="span"
                  onClick={() => setOpenModal(true)}
                >
                  <Edit sx={{ fontSize: 16, color: 'white' }} />
                </IconButton>
              }
            >
              <ImageWrapper>
                <img
                  src={userObj?.profile_pic || '/assets/images/avatars/001-man.svg'}
                  alt="Team Member"
                />
              </ImageWrapper>
            </AvatarBadge>
          </FlexBox>
          <Box mt={2}>
            <H4 fontWeight={600} textAlign="center">
              {userObj?.first_name} {userObj?.last_name}
            </H4>
            <FlexBetween maxWidth={500} flexWrap="wrap" margin="auto" mt={1}>
              <FlexBox alignItems="center" gap={1}>
                <Email sx={{ fontSize: 16, color: 'text.disabled' }} />
                <Small fontWeight={600} color="text.disabled">
                  {userObj.email}
                </Small>
              </FlexBox>

              <FlexBox alignItems="center" gap={1}>
                <Group sx={{ fontSize: 16, color: 'text.disabled' }} />
                <Small fontWeight={600} color="text.disabled">
                  {userObj?.group}
                </Small>
              </FlexBox>

              <FlexBox alignItems="center" gap={1}>
                <Work sx={{ fontSize: 16, color: 'text.disabled' }} />
                <Small fontWeight={600} color="text.disabled">
                  {userObj?.role}
                </Small>
              </FlexBox>
              <FlexBox alignItems="center" gap={1}></FlexBox>
            </FlexBetween>
            {userObj?.email && (
              <FlexCenter alignItems="center">
                <Button
                  variant="contained"
                  color="success"
                  sx={{ my: 2 }}
                  onClick={() => {
                    FetchOtp();
                    setOpenRegisterModal(true);
                  }}
                >
                  Reset Password
                </Button>
              </FlexCenter>
            )}
          </Box>
        </ContentWrapper>
      </Card>
      <Modal
        open={openModal}
        onClose={() => {
          setOpenModal(false);
        }}
      >
        <Box sx={{ ...style, color: theme.palette.text.primary }}>
          <EditUserProfile
            onclose={() => {
              setOpenModal(false);
              fetchData();
            }}
          />
        </Box>
      </Modal>
      <Modal
        open={openRegisterModal}
        onClose={() => {
          setOpenRegisterModal(false);
        }}
      >
        <Box sx={{ ...style, color: theme.palette.text.primary }}>
          <ResetPassword emailId={userObj?.email} onClose={() => setOpenRegisterModal(false)} />
        </Box>
      </Modal>
    </Fragment>
  );
};

export default UserProfile;
