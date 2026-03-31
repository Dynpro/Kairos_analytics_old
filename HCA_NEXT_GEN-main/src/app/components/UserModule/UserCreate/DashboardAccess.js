import { useState } from 'react';
import { styled } from '@mui/material';

import CheckboxTree from 'react-checkbox-tree';
import 'react-checkbox-tree/lib/react-checkbox-tree.css';
import {
  CheckBox,
  ChevronRight,
  CheckBoxOutlineBlank,
  IndeterminateCheckBox,
  KeyboardArrowDown,
  AddBox,
  Folder,
  FolderOpen,
  InsertDriveFile,
} from '@mui/icons-material';

import { useFormikContext } from 'formik';
import { useSelector } from 'react-redux';
import useApiLooker from 'app/hooks/useApiLooker';

const StyledSpan = styled('span')(({ theme }) => ({
  color: theme.palette.text.primary,
}));

const transformArray = (clients, folderObj, dashboardObj) => {
  return clients.map(({ client_primary_id, client_id, client_name }) => {
    let children = folderObj[client_id] && Object.values(folderObj[client_id]);
    const newClient = {
      label: <StyledSpan>{client_name}</StyledSpan>,
      value: `${client_primary_id}_${client_id}`,
    };
    if (children) {
      newClient['children'] = children.map(({ folder_id, folder_name }) => {
        let innerChild = null;
        if (dashboardObj && dashboardObj[client_id] && dashboardObj[client_id][folder_id])
          innerChild = Object.values(dashboardObj[client_id][folder_id]);
        const folder = {
          label: <span style={{ color: 'dodgerblue' }}>{folder_name}</span>,
          value: `${client_primary_id}_${client_id}_${folder_id}`,
        };
        if (innerChild)
          folder['children'] = innerChild.map(({ dash_id, title }) => ({
            label: <span style={{ color: 'teal' }}>{title}</span>,
            value: `${client_primary_id}_${client_id}_${folder_id}_${dash_id}`,
          }));
        return folder;
      });
    }
    return newClient;
  });
};

export default function DashboardAccess() {
  const [expanded, setExpanded] = useState([0]);
  useApiLooker();
  const allClients = useSelector((state) => state.userAccess.allClients);
  const allFolders = useSelector((state) => state.userAccess.allFolders);
  const allDashboards = useSelector((state) => state.userAccess.allDashboards);

  const { values, setFieldValue } = useFormikContext();
  const icons = {
    check: <CheckBox />,
    uncheck: <CheckBoxOutlineBlank />,
    halfCheck: <IndeterminateCheckBox />,
    expandClose: <ChevronRight />,
    expandOpen: <KeyboardArrowDown />,
    expandAll: <AddBox />,
    collapseAll: <IndeterminateCheckBox />,
    parentClose: <Folder />,
    parentOpen: <FolderOpen />,
    leaf: <InsertDriveFile />,
  };
  return (
    <>
      <p>Dashboard Access</p>
      <CheckboxTree
        parentOpen
        nodes={[
          {
            value: '0',
            label: <StyledSpan>CheckAll</StyledSpan>,
            children: [...transformArray(allClients || [], allFolders || {}, allDashboards || {})],
          },
        ]}
        checked={values.checked}
        expanded={expanded}
        onCheck={(checked) => setFieldValue('checked', checked)}
        onExpand={(expanded) => setExpanded(expanded)}
        showNodeIcon={false}
        icons={icons}
      />
    </>
  );
}
