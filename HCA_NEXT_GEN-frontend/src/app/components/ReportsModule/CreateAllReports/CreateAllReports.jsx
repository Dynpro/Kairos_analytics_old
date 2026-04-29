import React, { useState, useEffect } from 'react';
import {
  Accordion,
  AccordionSummary,
  AccordionDetails,
  Typography,
  TextField,
  Box,
  IconButton,
  Button,
  MenuItem,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Grid,
} from '@mui/material';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import AddCircleIcon from '@mui/icons-material/AddCircle';
import DeleteIcon from '@mui/icons-material/Delete';
import ReactQuill from 'react-quill';
import { useNavigate } from 'react-router-dom'; // For navigation
import 'react-quill/dist/quill.snow.css';
import commonConfig from 'app/components/commonConfig';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import commonRoutes from 'app/components/commonRoutes';
import SnackbarUtils from 'SnackbarUtils';

const CreateAllReports = () => {
  const navigate = useNavigate(); // For the Back button
  const [loading, setLoading] = useState(false);
  const [clientList, setClientList] = useState([]);
  const [looksList, setLooksList] = useState([]);
  const [selectedLookSection, setSelectedLookSection] = useState(null);
  const [openLookDialog, setOpenLookDialog] = useState(false);
  const authToken = getAccessToken();

  // State for top form fields
  const [reportDetails, setReportDetails] = useState({
    reportName: '',
    clientName: '',
    reportType: '',
    medStartDate: '',
    medEndDate: '',
    pharmaStartDate: '',
    pharmaEndDate: '',
  });
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

  const handleClientSelect = async (clientId) => {
    if (clientId) {
      try {
        const response = await axios.get(commonConfig.urls.getLooks + '/' + clientId, {
          headers: {
            Authorization: `Bearer ${authToken}`,
            'Content-Type': 'application/json',
          },
        });
        if (response && response.data.Status === 'Success') {
          setLooksList(response.data.Response);
        }
      } catch (error) {
        SnackbarUtils.error(error?.message || 'Something went wrong!!');
      }
    }
  };
  // State for sections and subsections
  const [sections, setSections] = useState([
    {
      id: 1,
      title: '',
      description: '',
      subsections: [{ id: 1.1, title: '', description: '', looks: [] }], // Initialize 'looks' as an empty array
    },
  ]);

  // Handlers for top form fields
  const handleInputChange = (field, value) => {
    setReportDetails({ ...reportDetails, [field]: value });
  };

  const handleOpenLookDialog = (sectionId, subId) => {
    setSelectedLookSection({ sectionId, subId });
    setOpenLookDialog(true);
  };

  // Close the Look selection dialog
  const handleCloseLookDialog = () => {
    setSelectedLookSection(null);
    setOpenLookDialog(false);
  };

  const handleSelectLook = (look) => {
    setSections(
      sections.map((section) =>
        section.id === selectedLookSection.sectionId
          ? {
              ...section,
              subsections: section.subsections.map((sub) =>
                sub.id === selectedLookSection.subId
                  ? { ...sub, looks: [...(sub.looks || []), look] } // Store selected look
                  : sub
              ),
            }
          : section
      )
    );
    handleCloseLookDialog();
  };

  const handleRemoveLook = (sectionId, subId, lookId) => {
    setSections(
      sections.map((section) =>
        section.id === sectionId
          ? {
              ...section,
              subsections: section.subsections.map((sub) =>
                sub.id === subId ? { ...sub, looks: sub.looks.filter((l) => l.id !== lookId) } : sub
              ),
            }
          : section
      )
    );
  };
  // Add new section with a default subsection
  const addSection = () => {
    setSections([
      ...sections,
      {
        id: sections.length + 1, // Auto-increment section number
        title: '',
        description: '',
        subsections: [],
      },
    ]);
  };

  // Delete a section
  const deleteSection = (sectionId) => {
    setSections(sections.filter((section) => section.id !== sectionId));
  };

  // Add subsection to a section
  const addSubSection = (sectionId) => {
    setSections(
      sections.map((section) =>
        section.id === sectionId
          ? {
              ...section,
              subsections: [
                ...section.subsections,
                {
                  id: `${section.id}.${section.subsections.length + 1}`, // Auto-increment subsection number
                  title: '',
                  description: '',
                },
              ],
            }
          : section
      )
    );
  };

  // Delete a subsection
  const deleteSubSection = (sectionId, subSectionId) => {
    setSections(
      sections.map((section) =>
        section.id === sectionId
          ? {
              ...section,
              subsections: section.subsections.filter((sub) => sub.id !== subSectionId),
            }
          : section
      )
    );
  };

  // Collect all section and subsection data
  const handleSave = async () => {
    // Add 'async' here
    const reportData = sections.map((section, sectionIndex) => ({
      sectionNo: sectionIndex + 1, // Section number
      title: section.title,
      description: section.description,
      subsections: section.subsections.map((sub, subIndex) => ({
        subSectionNo: subIndex + 1, // Pass numeric subsection number (1, 2, 3)
        title: sub.title,
        description: sub.description,
        lookId: sub.looks && sub.looks.length > 0 ? sub.looks[sub.looks.length - 1].id : null, // Include look ID if exists
        lookUrl:
          sub.looks && sub.looks.length > 0
            ? sub.looks[sub.looks.length - 1].image_embed_url
            : null, // Include look URL
      })),
    }));

    const fullReportData = {
      ...reportDetails,
      sections: reportData,
    };

    try {
      setLoading(true);
      const authToken = getAccessToken();
      const response = await axios.post(commonConfig.urls.phmStore, fullReportData, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      setLoading(false);
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data.Message);
        navigate(commonRoutes.reports.reportsList);
      }
      if (response && response.data.Status === 'Failed') {
        SnackbarUtils.error(Object.values(response.data.Errors).map((item) => item.toString()));
      }
    } catch (error) {
      setLoading(false);
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
    console.log('Full Report Data:', fullReportData);

    // Replace with your API endpoint and send `fullReportData` via POST or PUT request
  };

  return (
    <Box sx={{ mx: 2, mt: 2 }}>
      {/* Top Form Fields */}
      <Box sx={{ mb: 4 }}>
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
              handleClientSelect(clientId); // Fetch the looks for the selected client
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

      {/* Sections and Subsections */}
      {sections.map((section, sectionIndex) => (
        <Accordion key={section.id}>
          <AccordionSummary
            expandIcon={<ExpandMoreIcon />}
            aria-controls={`panel${section.id}-content`}
            id={`panel${section.id}-header`}
          >
            <Typography style={{ color: '#1976d2' }}>
              <b>Section {sectionIndex + 1}</b>
            </Typography>
            <Box sx={{ ml: 'auto' }}>
              <IconButton onClick={() => deleteSection(section.id)} color="error">
                <DeleteIcon />
              </IconButton>
            </Box>
          </AccordionSummary>
          <AccordionDetails>
            <Box sx={{ mb: 2 }}>
              <TextField
                fullWidth
                label="Title"
                value={section.title}
                onChange={(e) =>
                  setSections(
                    sections.map((s) => (s.id === section.id ? { ...s, title: e.target.value } : s))
                  )
                }
                sx={{ mb: 2 }}
              />
              <Typography sx={{ mb: 1 }}>Description:</Typography>
              <ReactQuill
                theme="snow"
                value={section.description}
                onChange={(value) =>
                  setSections(
                    sections.map((s) => (s.id === section.id ? { ...s, description: value } : s))
                  )
                }
                placeholder="Write your description here..."
                style={{
                  height: '150px',
                  borderRadius: '8px',
                  paddingBottom: '25px',
                  marginTop: '10px',
                }}
              />
            </Box>
            {section.subsections.map((sub, subIndex) => (
              <Accordion sx={{ mt: 2 }} key={sub.id}>
                <AccordionSummary
                  expandIcon={<ExpandMoreIcon />}
                  aria-controls={`sub-panel${sub.id}-content`}
                  id={`sub-panel${sub.id}-header`}
                >
                  <Typography style={{ color: 'Orange' }}>
                    <b>
                      Sub-Section {section.id}.{subIndex + 1}
                    </b>
                  </Typography>
                  <Box sx={{ ml: 'auto' }}>
                    <IconButton onClick={() => deleteSubSection(section.id, sub.id)} color="error">
                      <DeleteIcon />
                    </IconButton>
                  </Box>
                </AccordionSummary>
                <AccordionDetails>
                  <Box sx={{ mb: 2 }}>
                    <TextField
                      fullWidth
                      label="Title"
                      value={sub.title}
                      onChange={(e) =>
                        setSections(
                          sections.map((s) =>
                            s.id === section.id
                              ? {
                                  ...s,
                                  subsections: s.subsections.map((subItem) =>
                                    subItem.id === sub.id
                                      ? { ...subItem, title: e.target.value }
                                      : subItem
                                  ),
                                }
                              : s
                          )
                        )
                      }
                      sx={{ mb: 2 }}
                    />
                    <Typography sx={{ mb: 1 }}>Description:</Typography>
                    <ReactQuill
                      theme="snow"
                      value={sub.description}
                      onChange={(value) =>
                        setSections(
                          sections.map((s) =>
                            s.id === section.id
                              ? {
                                  ...s,
                                  subsections: s.subsections.map((subItem) =>
                                    subItem.id === sub.id
                                      ? { ...subItem, description: value }
                                      : subItem
                                  ),
                                }
                              : s
                          )
                        )
                      }
                      placeholder="Write your description here..."
                      style={{
                        height: '150px',
                        borderRadius: '8px',
                        paddingBottom: '25px',
                        marginTop: '10px',
                      }}
                    />
                  </Box>
                  <Box
                    sx={{
                      mb: 2,
                      display: 'flex',
                      justifyContent: 'center', // Horizontally center content
                      alignItems: 'center', // Vertically center content
                    }}
                  >
                    {sub.looks && sub.looks.length > 0 ? (
                      <img
                        src={sub.looks[sub.looks.length - 1].image_embed_url} // Get the last selected look's image
                        alt="Look"
                        // style={{ width: '280px', height: '280px', objectFit: 'contain' }}
                      />
                    ) : (
                      <Typography variant="body2" color="text.secondary">
                        No Look Added
                      </Typography>
                    )}
                  </Box>

                  <Box>
                    {sub.looks && sub.looks.length > 0 ? (
                      <Box>
                        <Typography variant="body2" color="text.secondary">
                          Look Added: {sub.looks[sub.looks.length - 1].name}
                        </Typography>
                        <Button
                          variant="outlined"
                          color="error"
                          size="small"
                          startIcon={<DeleteIcon />}
                          onClick={() =>
                            handleRemoveLook(section.id, sub.id, sub.looks[sub.looks.length - 1].id)
                          } // Remove look
                        >
                          Remove Look
                        </Button>
                      </Box>
                    ) : (
                      <Button
                        variant="contained"
                        color="primary"
                        size="small"
                        startIcon={<AddCircleIcon />}
                        onClick={() => handleOpenLookDialog(section.id, sub.id)} // Open Look Dialog
                        style={{
                          marginTop: '10px',
                        }}
                      >
                        Add Look
                      </Button>
                    )}
                  </Box>
                </AccordionDetails>
              </Accordion>
            ))}
            <Button
              onClick={() => addSubSection(section.id)}
              startIcon={<AddCircleIcon />}
              variant="contained"
              color="secondary"
              size="small"
              sx={{ mt: 2 }}
            >
              Add Sub Section
            </Button>
          </AccordionDetails>
        </Accordion>
      ))}

      {/* Add Section Button */}
      <Box sx={{ mt: 4, display: 'flex', justifyContent: 'space-between' }}>
        <Button
          onClick={addSection}
          startIcon={<AddCircleIcon />}
          variant="contained"
          color="primary"
        >
          Add Section
        </Button>
        <Box>
          <Button onClick={() => navigate(-1)} variant="outlined" color="primary" sx={{ mr: 2 }}>
            Back
          </Button>
          <Button onClick={handleSave} variant="contained" color="success">
            Save Report
          </Button>
        </Box>
      </Box>

      <Dialog open={openLookDialog} onClose={handleCloseLookDialog}>
        <DialogTitle>Select a Look</DialogTitle>
        <DialogContent>
          <Grid container spacing={2}>
            {looksList.map((look) => (
              <Grid item xs={12} key={look.id}>
                <Box
                  sx={{
                    cursor: 'pointer',
                    textAlign: 'center',
                    border: '1px solid #ddd',
                    borderRadius: 2,
                    p: 1,
                    '&:hover': { backgroundColor: '#f5f5f5' },
                  }}
                  onClick={() => handleSelectLook(look)}
                >
                  <Typography variant="body1" noWrap>
                    {look.name}
                  </Typography>
                </Box>
              </Grid>
            ))}
          </Grid>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleCloseLookDialog} color="primary">
            Close
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};

export default CreateAllReports;
