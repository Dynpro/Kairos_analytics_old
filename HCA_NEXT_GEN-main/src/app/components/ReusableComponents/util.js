const transformArrayToDashboardAccessCheckboxTree = (clients, folderObj, dashboardObj) => {
  return clients.map(({ client_primary_id, client_id, client_name }) => {
    let children = folderObj[client_id] && Object.values(folderObj[client_id]);
    const newClient = {
      label: <span style={{ color: 'tomato' }}>{client_name}</span>,
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

export default {
  transformArrayToDashboardAccessCheckboxTree,
};
