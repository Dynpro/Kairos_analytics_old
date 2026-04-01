import * as Yup from 'yup';

const initialValues = {
  //  category: '',
  subcategory: '',
  folder_name: '',
  folder_id: '',
  phm_folder_id: [],
  phm_folders: [],
  phm_folders_default: [],
  schema_name: '',
  contact_email: '',
  group_id: '',
  external_group_id: '',
  models: '',
  access_filters: '',
  file: '',
  logo: '',
};

function convertStrArrToNumArr(strArr) {
  return strArr.map((item) => ({
    folder_id: +item?.folder_id,
  }));
}

function findItemsWithSameFolderId2(fullArr = [], responseArr = []) {
  const numResponseArr = convertStrArrToNumArr(responseArr);
  const similarArr = numResponseArr.map((item) => item.folder_id);
  return fullArr.map((item) => ({ ...item, checked: similarArr.includes(item.id) }));
}

const createInitialValues = (initialObj = {}, responseObj = {}, allFolders = []) => {
  const newObj = { ...initialObj };
  Object.keys(initialObj).forEach((item) => {
    if (responseObj[item]) newObj[item] = responseObj[item];
  });
  if (responseObj?.logo) newObj['file'] = responseObj.logo;
  if (newObj?.phm_folder_id) {
    const newObj2 = newObj.phm_folder_id.map((item) => ({ ...item, checked: true }));
    const obj = {
      ...newObj,
      phm_folders: findItemsWithSameFolderId2(allFolders, newObj2),
    };
    const obj2 = {
      phm_folders_default: obj.phm_folders.filter((item) => item.checked),
    };
    return { ...obj, ...obj2 };
  }
  return { ...newObj, phm_folders: allFolders };
};

const validationSchema = Yup.object().shape(
  {
    folder_name: Yup.string().required('Kindly enter Client Name').label('Client Name'),
    //    category: Yup.string().required('Kindly select a Category').label('Category'),
    folder_id: Yup.string().required('Kindly select a Looker Folder').label('Looker Folder'),
    schema_name: Yup.string().required('Kindly select a Schema').label('Schema'),
    group_id: Yup.number().required('Kindly select a Group').label('Group ID').nullable(),
    models: Yup.string().required('Kindly enter Model Name').label('Model'),
    contact_email: Yup.string()
      .email('Kindly enter a valid email')
      .notRequired()
      .when('email', (value, schema) => {
        if (value?.length > 0) {
          return Yup.string().matches(
            /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i,
            'Invalid email address'
          );
        }
      })
      .label('Email'),
  },
  ['email', 'email']
);

const verifyErrors = (errors, touched, fieldName) => {
  if (Boolean(touched[fieldName] && errors[fieldName]))
    return <div style={{ color: 'red' }}>* {errors[fieldName]}</div>;
  return null;
};

export default {
  initialValues,
  validationSchema,
  createInitialValues,
  verifyErrors,
};
