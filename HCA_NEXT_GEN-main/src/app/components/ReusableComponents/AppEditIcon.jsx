import React from 'react';
import { Icon, styled } from '@mui/material';

const EditIcon = styled(Icon)(({ theme }) => ({
  color: theme.palette.text.secondary,
}));

export default function AppEditIcon({ iconType = 'edit_square' }) {
  return <EditIcon>{iconType}</EditIcon>;
}
