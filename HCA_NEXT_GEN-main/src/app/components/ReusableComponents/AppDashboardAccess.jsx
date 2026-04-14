import {
  AddBox,
  CheckBox,
  CheckBoxOutlineBlank,
  ChevronRight,
  Folder,
  FolderOpen,
  IndeterminateCheckBox,
  InsertDriveFile,
  KeyboardArrowDown,
} from '@mui/icons-material';
import useApiLooker from 'app/hooks/useApiLooker';
import { createSubCategory } from 'app/navigations';
import { useState } from 'react';
import CheckboxTree from 'react-checkbox-tree';
import 'react-checkbox-tree/lib/react-checkbox-tree.css';
import { useSelector } from 'react-redux';

export default function AppDashboardAccess({ checked, onCheck }) {
  const [expanded, setExpanded] = useState([0]);
  useApiLooker();
  const allSubCategories = useSelector((state) => state.userAccess.allSubCategories);
  const allFolders = useSelector((state) => state.userAccess.allFolders);
  const allDashboards = useSelector((state) => state.userAccess.allDashboards);

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
    <div>
      <p>Dashboard Access</p>
      <CheckboxTree
        parentOpen
        nodes={[
          {
            value: '0',
            label: 'CheckAll',
            children: createSubCategory(allSubCategories, allFolders, allDashboards),
          },
        ]}
        checked={checked}
        expanded={expanded}
        onCheck={(checked) => onCheck('checked', checked)}
        onExpand={(expanded) => setExpanded(expanded)}
        showNodeIcon={false}
        icons={icons}
      />
    </div>
  );
}
