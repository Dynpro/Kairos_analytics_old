import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Typography, Paper, TextField, Button, IconButton, InputAdornment } from '@mui/material';
import AutorenewRoundedIcon from '@mui/icons-material/AutorenewRounded'; 
import SearchIcon from '@mui/icons-material/Search'; 
import './styles.css'; 


const MyComponent = () => {
  const [data, setData] = useState([]);
  const [filteredData, setFilteredData] = useState([]);
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');
  const [filterClientName, setFilterClientName] = useState('');
  const [autoRefresh, setAutoRefresh] = useState(false);
  

  useEffect(() => {
    fetchData();
  }, []);

  useEffect(() => {
    filterDataByLast7Days();
  }, [data]);

  useEffect(() => {
    if (autoRefresh) {
      const intervalId = setInterval(fetchData, 10000); 
      return () => clearInterval(intervalId); 
    }
  }, [autoRefresh]);




  


  const fetchData = async () => {
    try {
     
      const response = await axios.get('https://y3xuepjuj1.execute-api.us-east-1.amazonaws.com/default/Automation_Ui', {
        headers: {
          'x-api-key': process.env.REACT_APP_DATA_AUTOMATION_KEY
        }
      });
      const jsonData = response.data.body;
      const parsedData = JSON.parse(jsonData);
      setData(parsedData);
      setFilteredData(parsedData);
      
    } catch (error) {
      console.error('Error fetching data:', error);
    }
  };
  

  

  const filterDataByLast7Days = () => {
    const currentDate = new Date();
    const last7Days = new Date(currentDate);
    last7Days.setDate(last7Days.getDate() - 7);
    const filtered = data.filter(item => new Date(item.CREATED_AT) >= last7Days);
    setFilteredData(filtered);
  };

  const handleStartDateChange = (event) => {
    setStartDate(event.target.value);
    
  };

  const handleEndDateChange = (event) => {
    setEndDate(event.target.value);
    
  };

  const handleClientNameFilter = (event) => {
    const { value } = event.target;
    setFilterClientName(value);
  };

  const handleSearch = () => {
    let searchData = data;
  
    // Filter by date range
    if (startDate && endDate) {
      const start = new Date(startDate);
      const end = new Date(endDate);
      searchData = searchData.filter(item => {
        const createdAt = new Date(item.CREATED_AT);
        return createdAt >= start && createdAt <= end;
      });
    }
  
    // Filter by client name
    if (filterClientName) {
      searchData = searchData.filter(item => 
        item.CLIENT_NAME.toLowerCase().includes(filterClientName.toLowerCase())
      );
    }

    setFilteredData(searchData);
  };

  const handleClearFilter = () => {
    setStartDate('');
    setEndDate('');
    setFilterClientName('');
    filterDataByLast7Days(); 
  };

  const toggleAutoRefresh = () => {
    setAutoRefresh(!autoRefresh);
  };

  return (
    <div className="container">
      

      <Paper elevation={3} className="banner">
      <Typography variant="h4" gutterBottom style={{ color: 'black', fontWeight: 'bold', marginLeft: '2rem', textAlign: 'left', marginTop: '1rem' }}>
        Data Load Logs
      </Typography>
    </Paper>

      <div className="filterSection">
        <div className="filter_s" style={{ marginRight: '10px' }}>
          <TextField
            id="client-name-filter"
            label="Search by Client Name"
            value={filterClientName}
            onChange={handleClientNameFilter}
          />
        </div>
        <div className="filter">
          <TextField
            id="start-date-filter"
            label="Start Date"
            type="date"
            value={startDate}
            onChange={handleStartDateChange}
            InputLabelProps={{
              shrink: true,
            }}
          />
          <TextField
            id="end-date-filter"
            label="End Date"
            type="date"
            value={endDate}
            onChange={handleEndDateChange}
            InputLabelProps={{
              shrink: true,
            }}
          />
          <IconButton onClick={handleSearch} color="primary">
            <SearchIcon />
          </IconButton>
        </div>
        <Button variant="outlined" onClick={handleClearFilter} className="clearButton">Clear</Button>
        <Button variant="outlined" onClick={toggleAutoRefresh} className="autoRefreshButton">
          {autoRefresh ? 'Stop Refresh' : <AutorenewRoundedIcon />}
        </Button>
      </div>
      <TableContainer component={Paper} className="tableContainer">
        <Table style={{ minWidth: '100%' }}>
          <TableHead>
            <TableRow>
              <TableCell className="tableHeaderCell">Client Name</TableCell>
              <TableCell className="tableHeaderCell">Data Type</TableCell>
              <TableCell className="tableHeaderCell">File Received At</TableCell>

              <TableCell className="tableHeaderCell">Moved to Master</TableCell>
              <TableCell className="tableHeaderCell">File Conversion</TableCell>
              <TableCell className="tableHeaderCell">Moved to Matillion</TableCell>
              <TableCell className="tableHeaderCell">Raw Layer</TableCell>
              <TableCell className="tableHeaderCell">Stage Layer</TableCell>
              <TableCell className="tableHeaderCell">Reversal Process</TableCell>
              <TableCell className="tableHeaderCell">LKR Process</TableCell>
              <TableCell className="tableHeaderCell">Data Processing</TableCell>
              
              <TableCell className="tableHeaderCell">Error</TableCell>
              <TableCell className="tableHeaderCell">Error Description</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {filteredData.map(item => (
              <TableRow key={item.UUID}>
                
                <TableCell className={`tableCell ${item.CLIENT_NAME_STATUS}`} style={{ textAlign: 'left' }}>{item.CLIENT_NAME}</TableCell>

                <TableCell className={`tableCell ${item.DATA_TYPE_STATUS}`}>{item.DATA_TYPE}</TableCell>
                <TableCell className={`tableCell ${item.FILE_RECEIVED_AT}`}>{item.FILE_RECIEVED_AT}</TableCell>

                <TableCell className={`tableCell ${item.MOVED_TO_MASTER_BUCKET ? 'done' : 'pending'}`}>{item.MOVED_TO_MASTER_BUCKET ? 'Done' : 'Pending'}</TableCell>
                <TableCell className={`tableCell ${item.CONVERTED_TO_STANDARD_FORMAT ? 'done' : 'pending'}`}>{item.CONVERTED_TO_STANDARD_FORMAT ? 'Done' : 'Pending'}</TableCell>
                <TableCell className={`tableCell ${item.CLONED_TO_ETL_BUCKET ? 'done' : 'pending'}`}>{item.CLONED_TO_ETL_BUCKET ? 'Done' : 'Pending'}</TableCell>
                <TableCell className={`tableCell ${item.RAW_DATA_LOAD}`}>{item.RAW_DATA_LOAD}</TableCell>
                <TableCell className={`tableCell ${item.STAGING_PROCESS}`}>{item.STAGING_PROCESS}</TableCell>
                <TableCell className={`tableCell ${item.REVERSAL_PROCESS}`}>{item.REVERSAL_PROCESS}</TableCell>
                <TableCell className={`tableCell ${item.LKR_PROCESS}`}>{item.LKR_PROCESS}</TableCell>
                <TableCell className={`tableCell ${item.DATA_PROCESSING_STATUS}`}>{item.DATA_PROCESSING_STATUS}</TableCell>

                
                <TableCell className={`tableCell ${item.ERROR ? 'failed' : 'No'}`}>{item.ERROR ? 'Failed' : 'No'}</TableCell>
                <TableCell className={`tableCell ${item.ERROR_DESCRIPTION}`}>{item.ERROR_DESCRIPTION}</TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </TableContainer>
    </div>
  );
};

export default MyComponent;
