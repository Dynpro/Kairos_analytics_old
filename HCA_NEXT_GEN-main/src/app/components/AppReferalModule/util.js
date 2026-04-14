import * as Yup from 'yup';

const initialValues = {
  firstname: '',
  lastname: '',
  dob: new Date(),
  gender: '',
  clientname: '',
  member_phone_number: '',
  member_address: '',
  member_city: '',
  member_state: '',
  member_zip_code: '',
  primary_insured_first_name: '',
  primary_insured_last_name: '',
  primary_insured_dob: '',
  member_diagnoses: '',
  eligibility_effective_date: new Date(),
  eligibility_termination_date: new Date(),
  relationship_to_employee: '',
  recommended_program: '',
  member_id: '',
  member_ssn: '',
  class_code: '',
  group_id: '',
  group_name: '',
  icd10_code: '',
};

const validationSchema = Yup.object().shape({
  firstname: Yup.string()
    .required('Kindly enter your First Name')
    .matches(/^[^0-9]+$/, 'Should not contain numbers')
    .label('First Name'),
  lastname: Yup.string()
    .required('Kindly enter your Last Name')
    .matches(/^[^0-9]+$/, 'Should not contain numbers')
    .label('Last Name'),
  dob: Yup.date()
    .required('Kindly enter a valid Date')
    .label('Date Of Birth')
    .max(new Date(Date.now()), 'Should be atleast today'),
  clientname: Yup.string().required('Kindly select a Client').label('Client Name').nullable(),
  gender: Yup.string().required('Kindly select Gender').label('Gender').nullable(),
  member_phone_number: Yup.string().required('Required field').label('Member Phone Number'),
  member_address: Yup.string().required('Required field').label('Member Address'),
  member_city: Yup.string().label('Memeber City'),
  member_state: Yup.string().label('Member State'),
  member_zip_code: Yup.string().label('Member Zipcode'),
  primary_insured_first_name: Yup.string().label('Primary Insured First Name'),
  primary_insured_last_name: Yup.string().label('Primary Insured Last Name'),
  primary_insured_dob: Yup.date()
    .label('Primary Insured DOB')
    .max(new Date(Date.now()), 'Should be atleast today'),
  member_diagnoses: Yup.string().label('Member Diagnoses'),
  eligibility_effective_date: Yup.date()
    .required('Kindly enter a valid Date')
    .label('Eligibility Effective Date'),
  eligibility_termination_date: Yup.date()
    .required('Kindly enter a valid Date')
    .label('Eligibility Termination Date'),
  relationship_to_employee: Yup.string().label('Relationship to Employee'),
  recommended_program: Yup.string().label('Recommended ProgramD'),
  member_id: Yup.string().label('Member ID'),
  member_ssn: Yup.string().label('Member SSN'),
  class_code: Yup.string().label('Class Code'),
  group_id: Yup.string().label('Group ID'),
  group_name: Yup.string().label('Group Name'),
  icd10_code: Yup.string().label('ICD10 CODE'),
});

const formatDateString = (date) => {
  const year = date.getFullYear();
  const month = (date.getMonth() + 1).toString().padStart(2, '0'); // Months are zero-based
  const day = date.getDate().toString().padStart(2, '0');

  return `${year}-${month}-${day}`;
};

const verifyErrors = (errorName, touchedName) => {
  if (Boolean(touchedName && errorName)) return <div style={{ color: 'red' }}>* {errorName}</div>;
  return null;
};

const genders = [
  { name: 'Male', value: 'M' },
  { name: 'Female', value: 'F' },
  { name: 'Other', value: 'O' },
];

export default {
  genders,
  initialValues,
  validationSchema,
  formatDateString,
  verifyErrors,
};
