const sortByName = (a, b) => a.name?.toLowerCase().localeCompare(b.name?.toLowerCase());

export const createSubCategory = (categoryObj, folders, dashboards, subFolders) => {
  const onlyClientsArr = [];
  const subCategoryArr = [];

  const keys = Object.keys(categoryObj);
  keys.forEach((item) => {
    if (item === '') {
      onlyClientsArr.push(...transformArray(categoryObj[item], folders, dashboards, subFolders, null));
    } else if (item.length > 0) {
      const [firstCategory] = categoryObj[item];

      // Skip "Hospitals" subcategory but keep its clients
      if (firstCategory.subcategory?.toLowerCase().includes('hospital')) {
        onlyClientsArr.push(
          ...transformArray(
            categoryObj[item],
            folders,
            dashboards,
            subFolders,
            firstCategory.subcategory_id
          )
        );
        return;
      }

      const config = { icon: 'group', level: 0, path: '' };
      const child = {
        ...config,
        label: <span style={{ color: 'tomato' }}>{firstCategory.subcategory}</span>,
        name: firstCategory.subcategory,
        subcategory_id: firstCategory.subcategory_id,
        value: firstCategory.subcategory_id,
        children: transformArray(
          categoryObj[item],
          folders,
          dashboards,
          subFolders,
          firstCategory.subcategory_id
        ),
      };
      subCategoryArr.push(child);
    }
  });

  return [...onlyClientsArr, ...subCategoryArr].sort(sortByName); // Sort the merged array here
};

export const transformArray = (clients, folderObj, dashboardObj, subFolderObj, catnsubcatval) => {
  const transformedClients = clients.map(
    ({ client_id, client_name, client_primary_id, subcategory }) => {
      let condn =
        folderObj[client_id] &&
        Object.values(folderObj[client_id]) &&
        Object.keys(folderObj[client_id]).filter(Boolean).length > 0;
      let children = folderObj[client_id] && Object.values(folderObj[client_id]);

      const newClient = {
        label: <span style={{ color: 'tomato' }}>{client_name}</span>,
        value: `${catnsubcatval}_${client_primary_id}_${client_id}`,
        client_primary_id,
        name: client_name,
        subcategory_name: subcategory,
        client_name,
        id: client_id,
        route: `/dashboard/${client_id}`,
        path: `/dashboard/${client_id}`,
        client_id,
        callDefault: true,
        level: 1,
        icon: 'person',
      };
      if (condn) {
        const flattenedChildren = [];

        children.forEach(({ folder_id, folder_name }) => {
          let innerChild = null;
          if (dashboardObj && dashboardObj[client_id] && dashboardObj[client_id][folder_id])
            innerChild = Object.values(dashboardObj[client_id][folder_id]);

          const subFoldersData =
            subFolderObj && subFolderObj[client_id] && subFolderObj[client_id][folder_id];

          // Regular folder
          const folder = {
            label: <span style={{ color: 'dodgerblue' }}>{folder_name}</span>,
            value: `${catnsubcatval}_${client_primary_id}_${client_id}_${folder_id}`,
            setFolderClient: true,
            name: folder_name,
            subcategory_name: subcategory,
            id: folder_id,
            folder_name,
            client_id,
            folder_id,
            client_name,
            level: 2,
            icon: 'folder',
          };
          if (innerChild)
            folder['children'] = innerChild
              .map(({ dash_id, title }) => ({
                label: <span style={{ color: 'teal' }}>{title}</span>,
                value: `${catnsubcatval}_${client_primary_id}_${client_id}_${folder_id}_${dash_id}`,
                client_id,
                dash_id,
                icon: 'dashboard',
                name: title,
                client_name,
                folder_name,
                subcategory_name: subcategory,
                route: `/dashboard/${encodeURIComponent(client_id + folder_id + dash_id)}`,
                path: `/dashboard/${encodeURIComponent(client_id + folder_id + dash_id)}`,
                level: 3,
                callDashboard: true,
                folder_id,
              }))
              .sort(sortByName);

          if (subFoldersData)
            folder['children'] = [
              ...(folder['children'] || []),
              ...subFoldersData.map(({ sub_folder_id, sub_folder_name }) => {
                const subFolderDashboards =
                  dashboardObj[client_id] && dashboardObj[client_id][sub_folder_id];
                const subFolder = {
                  label: <span style={{ color: 'lightcoral' }}>{sub_folder_name}</span>,
                  value: `${catnsubcatval}_${client_primary_id}_${client_id}_${folder_id}_${sub_folder_id}`,
                  setFolderClient: true,
                  name: sub_folder_name,
                  subcategory_name: subcategory,
                  id: sub_folder_id,
                  sub_folder_name,
                  client_id,
                  sub_folder_id,
                  client_name,
                  level: 3,
                  icon: 'folder_open',
                };
                if (subFolderDashboards)
                  subFolder['children'] = Object.values(subFolderDashboards)
                    .map(({ dash_id, title }) => ({
                      label: <span style={{ color: 'teal' }}>{title}</span>,
                      value: `${catnsubcatval}_${client_primary_id}_${client_id}_${folder_id}_${sub_folder_id}_${dash_id}`,
                      client_id,
                      dash_id,
                      icon: 'dashboard',
                      name: title,
                      client_name,
                      sub_folder_name,
                      folder_name,
                      subcategory_name: subcategory,
                      route: `/dashboard/${encodeURIComponent(client_id + folder_id + sub_folder_id + dash_id)}`,
                      path: `/dashboard/${encodeURIComponent(client_id + folder_id + sub_folder_id + dash_id)}`,
                      level: 4,
                      callDashboard: true,
                      folder_id,
                    }))
                    .sort(sortByName);
                return subFolder;
              }),
            ];

          flattenedChildren.push(folder);
        });

        newClient['children'] = flattenedChildren.sort(sortByName);
      }
      return newClient;
    }
  );

  return transformedClients;
};

export const navigations = [{ name: 'Dashboard', path: '/dashboard', icon: 'dashboard' }];
