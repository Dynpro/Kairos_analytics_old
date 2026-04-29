import React, { useState, useEffect } from 'react';
import Paper from '@mui/material/Paper';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TablePagination from '@mui/material/TablePagination';
import TableRow from '@mui/material/TableRow';
import Button from '@mui/material/Button';
import CloudUploadIcon from '@mui/icons-material/CloudUpload';
import CloudDownloadIcon from '@mui/icons-material/CloudDownload';
import IconButton from '@mui/material/IconButton';
import Tooltip from '@mui/material/Tooltip';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import SnackbarUtils from 'SnackbarUtils';
import { getAccessToken } from 'app/utils/utils';
import Accordion from '@mui/material/Accordion';
import AccordionSummary from '@mui/material/AccordionSummary';
import commonConfig from '../commonConfig';
import AccordionDetails from '@mui/material/AccordionDetails';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import axios from 'axios';
import TextField from '@mui/material/TextField';
import DialogContentText from '@mui/material/DialogContentText';
import Typography from '@mui/material/Typography';
import { useNavigate } from 'react-router-dom';
import { styled } from '@mui/material';
import commonRoutes from 'app/components/commonRoutes';
import AppEditIcon from 'app/components/ReusableComponents/AppEditIcon';
import FileCopyIcon from '@mui/icons-material/FileCopy';
import DeleteIcon from '@mui/icons-material/Delete';
import { Select, MenuItem, FormControl, InputLabel, Box } from '@mui/material';

const StyledButton = styled(Button)(({ theme }) => ({
  margin: theme.spacing(1),
  backgroundColor: theme.palette.info.main,
  marginTop: 0,
}));
const columns = [
  { id: 'name', label: 'Name', minWidth: 100, align: 'center' },
  { id: 'report_type', label: 'Report Type', minWidth: 100, align: 'center' },
  { id: 'folder_name', label: 'Folder Name', minWidth: 100, align: 'center' },
  { id: 'action', label: 'Action', minWidth: 100, align: 'center' },
];

const ColumnGroupingTable = () => {
  const [loading, setLoading] = useState(false);
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const [rows, setRows] = useState([]);
  const [selectedFile, setSelectedFile] = useState(null);
  const [selectedPhmId, setSelectedPhmId] = useState(null);
  const [openUploadDialog, setOpenUploadDialog] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [openReportsDialog, setOpenReportsDialog] = useState(false);
  const [clientNames, setClientNames] = useState([]); // State to store client names
  const [accordionData, setAccordionData] = useState([]); // State to store data for accordions
  const [openDeleteDialog, setOpenDeleteDialog] = useState(false); // State for delete dialog
  const [rowToDelete, setRowToDelete] = useState(null); // Store the row to delete
  const [rowToCopy, setRowToCopy] = useState(null); // Store the row to delete
  const [openCopyDialog, setOpenCopyDialog] = useState(false);
  const [phmname, setPhmname] = useState(''); // To hold the value of PHM name
  const [selectedOption, setSelectedOption] = useState(''); // To hold the selected dropdown value
  const [clientList, setClientList] = useState([]);

  const authToken = getAccessToken();
  const navigate = useNavigate();
  const [reportDetails, setReportDetails] = useState({
    reportName: '',
    clientName: '',
    reportType: '',
    medStartDate: '',
    medEndDate: '',
    pharmaStartDate: '',
    pharmaEndDate: '',
  });
  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await axios.get(commonConfig.urls.reports, {
          headers: { Authorization: `Bearer ${authToken}`, 'Content-Type': 'application/json' },
        });
        setRows(response?.data?.Response || []);
      } catch (error) {
        console.error('Error fetching data:', error);
      }
    };
    fetchData();
  }, []);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await axios.get(commonConfig.urls.reportModule, {
          headers: { Authorization: `Bearer ${authToken}`, 'Content-Type': 'application/json' },
        });
        console.log('Client names response:', response.data); // Log the response to verify its structure
        // Extract client names from the response
        const clientNamesResponse = response?.data?.Response || {};
        const clientNames = Object.keys(clientNamesResponse);
        setClientNames(clientNames);
      } catch (error) {
        console.error('Error fetching client names:', error);
        SnackbarUtils.error('Error fetching client names. Please try again later.');
      }
    };
    fetchData();
  }, []);

  const fetchClientNames = async () => {
    try {
      const response = await axios.get(commonConfig.urls.reportModule, {
        headers: { Authorization: `Bearer ${authToken}`, 'Content-Type': 'application/json' },
      });

      console.log('Client names response:', response.data); // Log the response to verify its structure

      // Extract client names from the response
      const clientNamesResponse = response?.data?.Response || {};
      const clientNames = Object.keys(clientNamesResponse);

      setClientNames(clientNames);
    } catch (error) {
      console.error('Error fetching client names:', error);
      SnackbarUtils.error('Error fetching client names. Please try again later.');
    }
  };

  const handleFileChange = (event) => {
    const selectedFile = event.target.files[0];
    if (selectedFile && selectedFile.type !== 'application/pdf') {
      alert('Please select a PDF file.');
      return;
    }
    setSelectedFile(selectedFile);
  };

  const handleUploadClick = (phm_id, fileName) => {
    setSelectedPhmId(phm_id);
    setSelectedFile({ name: fileName });
    setOpenUploadDialog(true);
  };

  const handleUpload = async () => {
    if (!selectedFile || !selectedPhmId) {
      console.error('Please select a file and a PHM_ID.');
      return;
    }

    const formData = new FormData();
    formData.append('file', selectedFile);
    formData.append('phm_id', selectedPhmId); // Append the selectedPhmId

    try {
      const response = await axios.post(commonConfig.urls.reportsUpload, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
          Authorization: `Bearer ${authToken}`,
        },
      });

      setOpenUploadDialog(false);
    } catch (error) {
      console.error('Error uploading file:', error);
      SnackbarUtils.error(error?.message || 'Something went wrong uploading the file!');
    }
  };

  const handleDownload = async (filePath, fileName) => {
    // Check if filePath is valid
    if (!filePath) {
      console.error('Invalid file path:', filePath);
      SnackbarUtils.error('Invalid file information. Please try again.');
      return;
    }

    const authToken = getAccessToken();

    try {
      const response = await axios.post(
        commonConfig.urls.reportsDownload,
        { file_path: filePath, file_name: fileName }, // Pass both file_path and file_name
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            Accept: 'application/pdf', // Specify expected content type
          },
          responseType: 'blob', // Receive response as blob (binary)
        }
      );

      if (response && response.data) {
        const blob = new Blob([response.data], { type: 'application/pdf' }); // Create a Blob with PDF type
        const url = window.URL.createObjectURL(blob);

        // Check if the browser supports downloading files
        if (navigator.msSaveBlob) {
          // For IE and Edge browsers
          navigator.msSaveBlob(blob, fileName);
        } else {
          // For other browsers
          const a = document.createElement('a');
          document.body.appendChild(a);
          a.style = 'display: none';

          a.href = url;
          a.download = fileName || 'downloaded_file.pdf'; // Set download attribute to the file name or a default name
          a.click();
          window.URL.revokeObjectURL(url);
          document.body.removeChild(a);
        }
      }
    } catch (error) {
      console.error('Error downloading file:', error);
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
  };

  const handleSearchButtonClick = () => {
    const filtered = rows.filter(
      (row) =>
        row.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        row.report_type.toLowerCase().includes(searchQuery.toLowerCase()) ||
        row.folder_name.toLowerCase().includes(searchQuery.toLowerCase())
    );
    setRows(filtered);
  };

  const handleChangePage = (event, newPage) => {
    setPage(newPage);
  };

  const handleChangeRowsPerPage = (event) => {
    setRowsPerPage(+event.target.value);
    setPage(0);
  };

  const handleUploadDialogClose = () => {
    setOpenUploadDialog(false);
    setSelectedFile(null);
    setSelectedPhmId(null);
  };

  const handleReportsDialogOpen = async () => {
    setOpenReportsDialog(true);
    fetchClientNames(); // Fetch client names when the reports dialog is opened
  };

  const handleReportsDialogClose = async () => {
    setOpenReportsDialog(false);
    // Fetch client names when the reports dialog is closed
  };

  const handleAccordionClick = async (clientName) => {
    try {
      const response = await axios.get(commonConfig.urls.reportModule, {
        params: { client_name: clientName },
        headers: { Authorization: `Bearer ${authToken}`, 'Content-Type': 'application/json' },
      });
      console.log('Accordion data response:', response.data); // Log the response to verify its structure
      const data = response?.data?.Response?.[clientName] || []; // Assuming the data is nested under the clientName
      setAccordionData(data);
    } catch (error) {
      console.error('Error fetching data for accordion:', error);
      SnackbarUtils.error('Error fetching data for accordion. Please try again later.');
    }
  };
  const handleDeleteClick = (row) => {
    setRowToDelete(row);
    setOpenDeleteDialog(true);
  };
  const handleDeleteDialogClose = () => {
    setOpenDeleteDialog(false);
    setRowToDelete(null);
  };
  const handleCopyClick = (row) => {
    setRowToCopy(row);
    setOpenCopyDialog(true);
  };
  const handleCopyDialogClose = () => {
    setOpenCopyDialog(false);
    setRowToCopy(null);
  };
  const handleDropdownChange = (event) => {
    setSelectedOption(event.target.value);
  };

  const handleDeleteConfirm = async () => {
    if (!rowToDelete) return;

    const authToken = getAccessToken();
    try {
      const response = await axios.delete(commonConfig.urls.deletephm + '/' + rowToDelete.id, {
        headers: { Authorization: `Bearer ${authToken}`, 'Content-Type': 'application/json' },
      });
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data.Message);
        navigate(commonRoutes.reports.reportsList, {
          state: { openSnackbar: true, msgSnackbar: 'DELETION SUCCESSFUL' },
        });
      }
      setOpenDeleteDialog(false);
      setRowToDelete(null);
    } catch (error) {
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
  };
  const handleCopyConfirm = async () => {
    if (!rowToCopy) return;
    console.log('reportDetails', reportDetails);
    console.log('rowToCopy', rowToCopy);
    const fullReportData = {
      ...reportDetails, // Add all fields from reportDetails
      rowToCopy, // Add rowToCopy as part of the payload
    };
    const authToken = getAccessToken();
    try {
      const response = await axios.post(commonConfig.urls.copyPHM, fullReportData, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data.Message);
        navigate(commonRoutes.reports.reportsList, {
          state: { openSnackbar: true, msgSnackbar: 'DELETION SUCCESSFUL' },
        });
      }
      setOpenDeleteDialog(false);
      setRowToDelete(null);
    } catch (error) {
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
  };
  const fetchClient = async () => {
    try {
      setLoading(true);
      const response = await axios(commonConfig.urls.phmClients, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        },
      });
      setLoading(false);
      if (response?.data?.Response) setClientList(response.data.Response);
    } catch (error) {
      setLoading(false);
      console.error(error?.message || 'Something went wrong!!');
    }
  };

  useEffect(() => {
    fetchClient();
  }, []);
  const handleInputChange = (field, value) => {
    setReportDetails({ ...reportDetails, [field]: value });
  };
  return (
    <Paper
      sx={{
        width: '100%',
        padding: '12px', // Adjust this value for top, left, and right spacing
        margin: '12px', // Add margin around the Paper
      }}
    >
      <TableContainer>
        <Table stickyHeader aria-label="sticky table">
          <TableHead>
            <TableRow>
              <TableCell
                colSpan={columns.length}
                style={{
                  fontSize: '1rem',
                  fontWeight: '500',
                  textTransform: 'capitalize',
                  marginBottom: '16px',
                }}
                className="breadcrumb"
                sx={{ m: 1 }}
              >
                <div style={{ display: 'flex', alignItems: 'center' }}>
                  Report List
                  <div style={{ marginLeft: 'auto', display: 'flex', alignItems: 'center' }}>
                    <input
                      type="text"
                      placeholder="Search"
                      value={searchQuery}
                      onChange={(e) => setSearchQuery(e.target.value)}
                      style={{ marginRight: '8px', padding: '8px', width: '200px' }}
                    />
                    <Button variant="contained" onClick={handleSearchButtonClick}>
                      Search
                    </Button>
                    <Button
                      variant="contained"
                      onClick={handleReportsDialogOpen} // Added onClick event handler here
                      style={{ marginLeft: '8px' }}
                    >
                      Reports
                    </Button>
                    <StyledButton
                      variant="contained"
                      onClick={() => navigate(commonRoutes.reports.reportsAdd)}
                    >
                      Create Report
                    </StyledButton>
                  </div>
                </div>
              </TableCell>
            </TableRow>
            <TableRow>
              {columns.map((column) => (
                <TableCell
                  key={column.id}
                  align={column.align}
                  style={{ top: 57, minWidth: column.minWidth }}
                >
                  {column.label}
                </TableCell>
              ))}
            </TableRow>
          </TableHead>
          <TableBody>
            {Array.isArray(rows) &&
              rows.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage).map((row, index) => (
                <TableRow key={row.id}>
                  {columns.map((column) => (
                    <TableCell key={column.id} align={column.align}>
                      {column.id === 'action' ? (
                        <div>
                          <Tooltip title="Upload">
                            <IconButton onClick={() => handleUploadClick(row.id, row.fileName)}>
                              <CloudUploadIcon />
                            </IconButton>
                          </Tooltip>
                          <Tooltip title="Download">
                            <IconButton
                              onClick={() => handleDownload(row.file_path, row.name)}
                              disabled={!row.file_path}
                            >
                              <CloudDownloadIcon />
                            </IconButton>
                          </Tooltip>
                          <Tooltip title="Edit">
                            {/* Edit Button */}
                            <IconButton
                              onClick={() =>
                                navigate(commonRoutes.reports.reportsEdit, { state: { ...row } })
                              }
                            >
                              <AppEditIcon />
                            </IconButton>
                          </Tooltip>

                          <Tooltip title="Copy">
                            <IconButton onClick={() => handleCopyClick(row)}>
                              <FileCopyIcon color="error" />
                            </IconButton>
                          </Tooltip>
                          <Tooltip title="Delete">
                            <IconButton onClick={() => handleDeleteClick(row)}>
                              <DeleteIcon color="error" />
                            </IconButton>
                          </Tooltip>
                        </div>
                      ) : (
                        row[column.id]
                      )}
                    </TableCell>
                  ))}
                </TableRow>
              ))}
          </TableBody>
        </Table>
      </TableContainer>
      <TablePagination
        rowsPerPageOptions={[10, 25, 100]}
        component="div"
        count={rows.length}
        rowsPerPage={rowsPerPage}
        page={page}
        onPageChange={handleChangePage}
        onRowsPerPageChange={handleChangeRowsPerPage}
      />
      <Dialog open={openUploadDialog} onClose={handleUploadDialogClose} maxWidth="xl">
        <DialogTitle>Upload File</DialogTitle>
        <DialogContent style={{ display: 'flex', alignItems: 'center' }}>
          <input
            type="file"
            accept=".pdf"
            onChange={handleFileChange}
            style={{ display: 'none' }}
            id="fileInput"
          />
          <TextField
            label="Selected File"
            value={selectedFile ? selectedFile.name : ''}
            variant="outlined"
            fullWidth
            disabled
            style={{ marginRight: '10px' }}
          />
          <label htmlFor="fileInput">
            <Button variant="outlined" component="span">
              Browse Files
            </Button>
          </label>
        </DialogContent>

        <DialogActions>
          <Button onClick={handleUploadDialogClose}>Cancel</Button>
          <Button onClick={handleUpload} variant="contained" disabled={!selectedFile}>
            Submit
          </Button>
        </DialogActions>
      </Dialog>

      {/* Reports Dialog */}
      <Dialog open={openReportsDialog} onClose={handleReportsDialogClose} maxWidth="lg" fullWidth>
        <DialogTitle>Reports</DialogTitle>
        <DialogContent dividers>
          {clientNames.map((clientName, index) => (
            <Accordion
              key={clientName} // Use clientName as the unique key
              onChange={() => handleAccordionClick(clientName)}
            >
              <AccordionSummary
                expandIcon={<ExpandMoreIcon />}
                aria-controls={`panel-${index}-content`}
                id={`panel-${index}-header`}
              >
                <Typography>{clientName}</Typography>
              </AccordionSummary>
              <AccordionDetails>
                {accordionData.map((item, innerIndex) => (
                  <Button
                    key={innerIndex}
                    variant="outlined"
                    fullWidth
                    onClick={() => handleDownload(item.file_path, item.phm_name)}
                    style={{
                      textAlign: 'left',
                      justifyContent: 'flex-start',
                      marginBottom: '10px',
                    }}
                  >
                    <Typography>
                      {item.phm_name} - {item.file_path.split('/').pop()}
                    </Typography>
                  </Button>
                ))}
              </AccordionDetails>
            </Accordion>
          ))}
        </DialogContent>
        <DialogActions>
          <Button onClick={handleReportsDialogClose} color="primary">
            Close
          </Button>
        </DialogActions>
      </Dialog>
      <Dialog open={openDeleteDialog} onClose={handleDeleteDialogClose}>
        <DialogTitle>Confirm Delete</DialogTitle>
        <DialogContent>
          <DialogContentText>
            Are you sure you want to delete the report <strong>{rowToDelete?.name}</strong>? This
            action cannot be undo.
          </DialogContentText>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleDeleteDialogClose} color="primary">
            Cancel
          </Button>
          <Button onClick={handleDeleteConfirm} color="error" variant="contained">
            Delete
          </Button>
        </DialogActions>
      </Dialog>
      <Dialog open={openDeleteDialog} onClose={handleDeleteDialogClose}>
        <DialogTitle>Confirm Delete</DialogTitle>
        <DialogContent>
          <DialogContentText>
            Are you sure you want to delete the report <strong>{rowToDelete?.name}</strong>? This
            action cannot be undone.
          </DialogContentText>
          {/* Add TextField here */}
          <TextField
            label="Reason for Deletion"
            fullWidth
            variant="outlined"
            margin="normal"
            value={rowToDelete?.deletionReason || ''} // If the row has a reason, pre-fill it
            onChange={(e) => {
              setRowToDelete((prevState) => ({
                ...prevState,
                deletionReason: e.target.value, // Update the reason as user types
              }));
            }}
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={handleDeleteDialogClose} color="primary">
            Cancel
          </Button>
          <Button
            onClick={() => handleDeleteConfirm(rowToDelete)}
            color="error"
            variant="contained"
            disabled={!rowToDelete?.deletionReason} // Ensure there's a reason before submitting
          >
            Delete
          </Button>
        </DialogActions>
      </Dialog>
      <Dialog
        open={openCopyDialog}
        onClose={handleCopyDialogClose}
        sx={{ '& .MuiDialog-paper': { width: '80%', maxWidth: 'none' } }}
      >
        <DialogTitle>Confirm Copy</DialogTitle> {/* Updated title */}
        <DialogContent>
          <Box sx={{ mb: 4, mt: 2 }}>
            <Box sx={{ display: 'flex', gap: 2, mb: 2 }}>
              <TextField
                fullWidth
                label="Report Name"
                value={reportDetails.reportName}
                onChange={(e) => handleInputChange('reportName', e.target.value)}
              />
              <TextField
                select
                fullWidth
                label="Client Name"
                value={reportDetails.clientName} // Correctly bind to the state
                onChange={(e) => {
                  const clientId = e.target.value;
                  setReportDetails({ ...reportDetails, clientName: clientId }); // Update the state with the selected client ID
                }}
              >
                {clientList.map((client) => (
                  <MenuItem key={client.id} value={client.id}>
                    {client.name} {/* Display client name */}
                  </MenuItem>
                ))}
              </TextField>

              <TextField
                select
                fullWidth
                label="Report Type"
                value={reportDetails.reportType}
                onChange={(e) => handleInputChange('reportType', e.target.value)}
              >
                {/* Add your report type options here */}
                <MenuItem value="1">PHM</MenuItem>
                <MenuItem value="2">EBR</MenuItem>
                <MenuItem value="3">MSK</MenuItem>
              </TextField>
            </Box>
            <Box sx={{ display: 'flex', gap: 2, mb: 2 }}>
              <TextField
                fullWidth
                type="date"
                label="Med Start Date"
                InputLabelProps={{ shrink: true }}
                value={reportDetails.medStartDate}
                onChange={(e) => handleInputChange('medStartDate', e.target.value)}
              />
              <TextField
                fullWidth
                type="date"
                label="Med End Date"
                InputLabelProps={{ shrink: true }}
                value={reportDetails.medEndDate}
                onChange={(e) => handleInputChange('medEndDate', e.target.value)}
              />
              <TextField
                fullWidth
                type="date"
                label="Pharma Start Date"
                InputLabelProps={{ shrink: true }}
                value={reportDetails.pharmaStartDate}
                onChange={(e) => handleInputChange('pharmaStartDate', e.target.value)}
              />
              <TextField
                fullWidth
                type="date"
                label="Pharma End Date"
                InputLabelProps={{ shrink: true }}
                value={reportDetails.pharmaEndDate}
                onChange={(e) => handleInputChange('pharmaEndDate', e.target.value)}
              />
            </Box>
          </Box>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCopyDialogClose} color="primary">
            Cancel
          </Button>
          <Button
            onClick={() => handleCopyConfirm({ ...reportDetails }, rowToCopy)}
            color="error"
            variant="contained"
          >
            Save
          </Button>
        </DialogActions>
      </Dialog>
    </Paper>
  );
};

export default ColumnGroupingTable;
