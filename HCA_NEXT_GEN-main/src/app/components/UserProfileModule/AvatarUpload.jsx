import { CloudUpload, Delete } from '@mui/icons-material';
import { Avatar, Button, styled } from '@mui/material';
import { useFormikContext } from 'formik';
import { createRef, useState } from 'react';

const CenteredContent = styled('div')(({ theme }) => ({
  textAlign: 'center',
}));

const BigAvatar = styled(Avatar)(({ theme }) => ({
  width: '200px',
  height: '200px',
  margin: `0 auto 16px`,
  border: `1px solid ${'grey'[500]}`,
  boxShadow: `0 0 1px 0 ${'grey'[500]} inset, 0 0 1px 0 ${'grey'[500]}`,
}));

const AppContent = styled('div')(({ theme }) => ({
  flex: 1,
  display: 'flex',
  flexDirection: 'column',
  paddingRight: '100px',
  paddingLeft: '100px',
}));

const AvatarUpload = () => {
  return (
    <AppContent>
      <Layout />
    </AppContent>
  );
};

const Layout = () => {
  const { values, setFieldValue } = useFormikContext();
  const [image, _setImage] = useState(null);
  const inputFileRef = createRef(null);

  const cleanup = () => {
    URL.revokeObjectURL(image);
    inputFileRef.current.value = null;
    setFieldValue('file', null);
  };

  const setImage = (newImage) => {
    if (image) {
      cleanup();
    }
    _setImage(newImage);
  };

  const handleOnChange = (event) => {
    const newImage = event.target?.files?.[0];

    if (newImage) {
      setImage(URL.createObjectURL(newImage));
      setFieldValue('file', newImage);
      setFieldValue('is_deleted', 0);
    }
  };

  const handleClick = (event) => {
    if (image) {
      event.preventDefault();
      setImage(null);
      setFieldValue('is_deleted', 0);
    }
  };
  const DeleteImage = (event) => {
    event.preventDefault();
    if (values.file) {
      setImage(null);
      setFieldValue('file', '');
      setFieldValue('is_deleted', 1);
    }
  };
  return (
    <CenteredContent>
      <BigAvatar $withBorder alt="Avatar" src={image ? image : values.file} />
      <input
        ref={inputFileRef}
        accept="image/*"
        hidden
        id="avatar-image-upload"
        type="file"
        onChange={handleOnChange}
      />
      <label htmlFor="avatar-image-upload">
        {!image && !values.file && (
          <Button variant="contained" color="success" component="span" mb={2} onClick={handleClick}>
            <CloudUpload mr={2} />
            &nbsp; Upload
          </Button>
        )}
        {values.file && (
          <Button variant="contained" color="error" component="span" mb={2} onClick={DeleteImage}>
            <Delete mr={2} />
            &nbsp; Delete
          </Button>
        )}
      </label>
    </CenteredContent>
  );
};

export default AvatarUpload;
