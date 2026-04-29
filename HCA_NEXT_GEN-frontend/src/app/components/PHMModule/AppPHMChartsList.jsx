import React, { useState, useEffect } from 'react';
import {
  Box,
  Card,
  CardContent,
  Typography,
  Grid,
  Accordion,
  AccordionSummary,
  AccordionDetails,
  IconButton,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  TextField,
  MenuItem,
  FormControl,
  InputLabel,
  Select,
  Chip,
  Tooltip,
  CircularProgress,
  Alert,
  Paper,
  Menu,
  
} from '@mui/material';
import {
  ExpandMore,
  Visibility,
  OpenInNew,
  Search,
  FilterList,
  BarChart,
  Timeline,
  Assessment,
  TrendingUp,
  People,
  LocalHospital,
  Medication,
  Warning,
  Download,
  PictureAsPdf,
  TableChart,
  GetApp
} from '@mui/icons-material';
import axios from 'axios';
import commonConfig from '../commonConfig';
import { getAccessToken } from 'app/utils/utils';

const AppPHMChartsList = () => {
  const [charts, setCharts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [selectedChart, setSelectedChart] = useState(null);
  const [openDialog, setOpenDialog] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('all');
  const [categories, setCategories] = useState([]);
  const [downloadMenuAnchor, setDownloadMenuAnchor] = useState(null);

  const authToken = getAccessToken();

  // Chart category icons mapping
  const getCategoryIcon = (category) => {
    const iconMap = {
      'Chart Group 1': <BarChart />,
      'Chart Group 2': <People />,
      'Chart Group 3': <Assessment />,
      'Chart Group 4': <Timeline />,
      'Chart Group 5': <TrendingUp />,
      'Chart Group 6': <People />,
      'Chart Group 7': <LocalHospital />,
      'Chart Group 8': <LocalHospital />,
      'Chart Group 9': <LocalHospital />,
      'Chart Group 10': <LocalHospital />,
      'Chart Group 11': <Assessment />,
      'Chart Group 12': <Warning />,
      'Chart Group 13': <Assessment />,
      'Chart Group 15': <LocalHospital />,
      'Chart Group 16': <LocalHospital />,
      'Chart Group 17': <Warning />,
      'Chart Group 18': <Assessment />,
      'Chart Group 19': <Warning />,
      'Chart Group 20': <Warning />,
      'Chart Group 21': <LocalHospital />,
      'Chart Group 22': <Medication />,
      'Chart Group 23': <Medication />,
      'Chart Group 24': <Medication />,
      'Chart Group 25': <Medication />,
      'Chart Group 26': <Medication />
    };
    return iconMap[category] || <BarChart />;
  };

  useEffect(() => {
    fetchPHMCharts();
  }, []);

  const fetchPHMCharts = async () => {
    try {
      setLoading(true);
      setError(null);
      
      const response = await axios.get(`${commonConfig.urls.baseURL}/phm`, {
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Content-Type': 'application/json'
        }
      });

      if (response.data && response.data.Code === 200) {
        const chartsData = response.data.Response || [];
        setCharts(chartsData);
        
        // Extract categories
        const extractedCategories = [];
        chartsData.forEach(chart => {
          const match = chart.name.match(/Chart_(\d+)_\d+/);
          if (match) {
            const category = `Chart Group ${match[1]}`;
            if (!extractedCategories.includes(category)) {
              extractedCategories.push(category);
            }
          }
        });
        setCategories(extractedCategories.sort());
      } else {
        setError('Failed to fetch PHM charts');
      }
    } catch (err) {
      setError('Error connecting to server');
      console.error('Error fetching PHM charts:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleViewChart = (chart) => {
    setSelectedChart(chart);
    setOpenDialog(true);
  };

  const handleOpenInNewTab = (chart) => {
    if (chart.chart1) {
      window.open(chart.chart1, '_blank');
    }
  };

  const handleDownloadMenuOpen = (event, chart) => {
    setDownloadMenuAnchor(event.currentTarget);
    setSelectedChart(chart);
  };

  const handleDownloadMenuClose = () => {
    setDownloadMenuAnchor(null);
  };

  const downloadChartAsImage = (chart) => {
    // Create a temporary canvas to capture the iframe content
    const iframe = document.querySelector(`#chart-iframe-${chart.id}`);
    if (iframe) {
      // For Looker Studio charts, we'll download the chart image
      const chartUrl = chart.chart1;
      
      // Create a download link for the chart
      const link = document.createElement('a');
      link.href = chartUrl;
      link.download = `${chart.name.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.png`;
      link.target = '_blank';
      
      // Try to download the chart image
      fetch(chartUrl)
        .then(response => response.blob())
        .then(blob => {
          const url = URL.createObjectURL(blob);
          link.href = url;
          link.click();
          URL.revokeObjectURL(url);
        })
        .catch(() => {
          // Fallback: open in new tab if download fails
          window.open(chartUrl, '_blank');
        });
    }
    handleDownloadMenuClose();
  };

  const downloadChartAsPDF = (chart) => {
    // For PDF generation, we'll use the browser's print functionality
    const printWindow = window.open(chart.chart1, '_blank');
    if (printWindow) {
      printWindow.onload = () => {
        printWindow.print();
      };
    }
    handleDownloadMenuClose();
  };

  const downloadChartData = (chart) => {
    // Create a data export with chart information
    const data = {
      chart_name: chart.name,
      description: chart.text1,
      additional_info: chart.text2,
      chart_url: chart.chart1,
      client: 'Demo Test',
      export_date: new Date().toISOString(),
      chart_id: chart.id
    };

    const dataStr = JSON.stringify(data, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    
    const link = document.createElement('a');
    link.href = URL.createObjectURL(dataBlob);
    link.download = `${chart.name.replace(/[^a-z0-9]/gi, '_').toLowerCase()}_data.json`;
    link.click();
    
    handleDownloadMenuClose();
  };

  const downloadAllCharts = () => {
    // Create a summary of all charts
    const allChartsData = {
      export_summary: {
        total_charts: charts.length,
        export_date: new Date().toISOString(),
        client: 'Demo Test'
      },
      charts: charts.map(chart => ({
        id: chart.id,
        name: chart.name,
        description: chart.text1,
        chart_url: chart.chart1
      }))
    };

    const dataStr = JSON.stringify(allChartsData, null, 2);
    const dataBlob = new Blob([dataStr], { type: 'application/json' });
    
    const link = document.createElement('a');
    link.href = URL.createObjectURL(dataBlob);
    link.download = `phm_charts_summary_${new Date().toISOString().split('T')[0]}.json`;
    link.click();
    
    handleDownloadMenuClose();
  };

  // Group charts by category
  const groupChartsByCategory = () => {
    const grouped = {};
    charts.forEach(chart => {
      const match = chart.name.match(/Chart_(\d+)_\d+/);
      if (match) {
        const category = `Chart Group ${match[1]}`;
        if (!grouped[category]) {
          grouped[category] = [];
        }
        grouped[category].push(chart);
      }
    });
    return grouped;
  };

  // Filter charts based on search and category
  const getFilteredCharts = () => {
    let filtered = charts;
    
    if (searchTerm) {
      filtered = filtered.filter(chart =>
        chart.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        chart.text1?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        chart.text2?.toLowerCase().includes(searchTerm.toLowerCase())
      );
    }
    
    if (selectedCategory !== 'all') {
      filtered = filtered.filter(chart => {
        const match = chart.name.match(/Chart_(\d+)_\d+/);
        return match && `Chart Group ${match[1]}` === selectedCategory;
      });
    }
    
    return filtered;
  };

  const filteredCharts = getFilteredCharts();
  const groupedCharts = groupChartsByCategory();

  if (loading) {
    return (
      <Box display="flex" justifyContent="center" alignItems="center" minHeight="400px">
        <CircularProgress />
        <Typography variant="h6" sx={{ ml: 2 }}>Loading PHM Charts...</Typography>
      </Box>
    );
  }

  if (error) {
    return (
      <Alert severity="error" sx={{ mb: 2 }}>
        {error}
        <Button onClick={fetchPHMCharts} sx={{ ml: 2 }}>Retry</Button>
      </Alert>
    );
  }

  return (
    <Box sx={{ p: 3 }}>
      {/* Header */}
      <Box sx={{ mb: 3, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <Typography variant="h4" sx={{ fontWeight: 'bold', display: 'flex', alignItems: 'center' }}>
          <BarChart sx={{ mr: 2, color: 'primary.main' }} />
          PHM Charts Dashboard
        </Typography>
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
          <Button
            variant="outlined"
            startIcon={<Download />}
            onClick={(e) => handleDownloadMenuOpen(e, null)}
            disabled={charts.length === 0}
          >
            Download All
          </Button>
          <Chip 
            label={`${filteredCharts.length} Charts`} 
            color="primary" 
            variant="outlined" 
          />
        </Box>
      </Box>

      {/* Search and Filter */}
      <Paper sx={{ p: 2, mb: 3 }}>
        <Grid container spacing={2} alignItems="center">
          <Grid item xs={12} md={6}>
            <TextField
              fullWidth
              placeholder="Search charts..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              InputProps={{
                startAdornment: <Search sx={{ mr: 1, color: 'text.secondary' }} />
              }}
            />
          </Grid>
          <Grid item xs={12} md={4}>
            <FormControl fullWidth>
              <InputLabel>Filter by Category</InputLabel>
              <Select
                value={selectedCategory}
                onChange={(e) => setSelectedCategory(e.target.value)}
                label="Filter by Category"
              >
                <MenuItem value="all">All Categories</MenuItem>
                {categories.map(category => (
                  <MenuItem key={category} value={category}>
                    {category}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </Grid>
          <Grid item xs={12} md={2}>
            <Button
              fullWidth
              variant="outlined"
              startIcon={<FilterList />}
              onClick={() => {
                setSearchTerm('');
                setSelectedCategory('all');
              }}
            >
              Clear
            </Button>
          </Grid>
        </Grid>
      </Paper>

      {/* Charts Display */}
      {Object.keys(groupedCharts).length === 0 ? (
        <Alert severity="info">
          No PHM charts found. Please check your database connection.
        </Alert>
      ) : (
        Object.keys(groupedCharts).map(category => {
          const categoryCharts = groupedCharts[category].filter(chart => {
            const matchesSearch = !searchTerm || 
              chart.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
              chart.text1?.toLowerCase().includes(searchTerm.toLowerCase()) ||
              chart.text2?.toLowerCase().includes(searchTerm.toLowerCase());
            
            const matchesCategory = selectedCategory === 'all' || category === selectedCategory;
            return matchesSearch && matchesCategory;
          });

          if (categoryCharts.length === 0) return null;

          return (
            <Accordion key={category} sx={{ mb: 2 }}>
              <AccordionSummary expandIcon={<ExpandMore />}>
                <Box sx={{ display: 'flex', alignItems: 'center', width: '100%' }}>
                  {getCategoryIcon(category)}
                  <Typography variant="h6" sx={{ ml: 2, flexGrow: 1 }}>
                    {category}
                  </Typography>
                  <Chip 
                    label={`${categoryCharts.length} charts`} 
                    size="small" 
                    color="primary" 
                    variant="outlined"
                  />
                </Box>
              </AccordionSummary>
              <AccordionDetails>
                <Grid container spacing={2}>
                  {categoryCharts.map(chart => (
                    <Grid item xs={12} md={6} lg={4} key={chart.id}>
                      <Card sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                        <CardContent sx={{ flexGrow: 1 }}>
                          <Typography variant="h6" sx={{ mb: 1, fontSize: '0.9rem' }}>
                            {chart.name}
                          </Typography>
                          <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                            {chart.text1}
                          </Typography>
                          {chart.text2 && (
                            <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
                              {chart.text2}
                            </Typography>
                          )}
                          <Box sx={{ display: 'flex', gap: 1, mt: 'auto' }}>
                            <Button
                              size="small"
                              variant="contained"
                              startIcon={<Visibility />}
                              onClick={() => handleViewChart(chart)}
                              disabled={!chart.chart1}
                            >
                              View
                            </Button>
                            <Button
                              size="small"
                              variant="outlined"
                              startIcon={<OpenInNew />}
                              onClick={() => handleOpenInNewTab(chart)}
                              disabled={!chart.chart1}
                            >
                              Open
                            </Button>
                            <Button
                              size="small"
                              variant="outlined"
                              startIcon={<Download />}
                              onClick={(e) => handleDownloadMenuOpen(e, chart)}
                              disabled={!chart.chart1}
                            >
                              Download
                            </Button>
                          </Box>
                        </CardContent>
                      </Card>
                    </Grid>
                  ))}
                </Grid>
              </AccordionDetails>
            </Accordion>
          );
        })
      )}

      {/* Chart View Dialog */}
      <Dialog
        open={openDialog}
        onClose={() => setOpenDialog(false)}
        maxWidth="lg"
        fullWidth
      >
        <DialogTitle>
          {selectedChart?.name}
        </DialogTitle>
        <DialogContent>
          {selectedChart && (
            <Box>
              <Typography variant="body1" sx={{ mb: 2 }}>
                {selectedChart.text1}
              </Typography>
              {selectedChart.text2 && (
                <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
                  {selectedChart.text2}
                </Typography>
              )}
              {selectedChart.chart1 && (
                <Box sx={{ mt: 2, border: '1px solid #ddd', borderRadius: 1, overflow: 'hidden' }}>
                  <iframe
                    src={selectedChart.chart1}
                    width="100%"
                    height="500"
                    frameBorder="0"
                    title={selectedChart.name}
                  />
                </Box>
              )}
            </Box>
          )}
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setOpenDialog(false)}>Close</Button>
          {selectedChart?.chart1 && (
            <Button
              variant="contained"
              onClick={() => handleOpenInNewTab(selectedChart)}
              startIcon={<OpenInNew />}
            >
              Open in New Tab
            </Button>
          )}
        </DialogActions>
      </Dialog>

      {/* Download Menu */}
      <Menu
        anchorEl={downloadMenuAnchor}
        open={Boolean(downloadMenuAnchor)}
        onClose={handleDownloadMenuClose}
      >
        {selectedChart ? (
          <>
            <MenuItem onClick={() => downloadChartAsImage(selectedChart)}>
              <Download sx={{ mr: 1 }} />
              Download as Image
            </MenuItem>
            <MenuItem onClick={() => downloadChartAsPDF(selectedChart)}>
              <PictureAsPdf sx={{ mr: 1 }} />
              Download as PDF
            </MenuItem>
            <MenuItem onClick={() => downloadChartData(selectedChart)}>
              <TableChart sx={{ mr: 1 }} />
              Download Chart Data
            </MenuItem>
          </>
        ) : (
          <MenuItem onClick={downloadAllCharts}>
            <GetApp sx={{ mr: 1 }} />
            Download All Charts Summary
          </MenuItem>
        )}
      </Menu>
    </Box>
  );
};

export default AppPHMChartsList;
