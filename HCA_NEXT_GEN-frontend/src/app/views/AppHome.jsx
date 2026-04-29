import { ExpandMore as ExpandMoreIcon } from '@mui/icons-material';
import {
  Accordion,
  AccordionDetails,
  AccordionSummary,
  Grid,
  Typography,
  useTheme,
  Box,
  Card,
  IconButton,
} from '@mui/material';

const AppAccordian = ({ data }) => (
  <div>
    {data.map((parent) => {
      return (
        <div key={parent.name}>
          {parent.children && (
            <Accordion sx={{ width: '100%' }}>
              <AccordionSummary
                expandIcon={<ExpandMoreIcon />}
                aria-controls="panel1a-content"
                id="panel1a-header"
              >
                <Typography>{parent.name}</Typography>
              </AccordionSummary>
              {/*           <div style={{ zIndex: 1, position: 'absolute', width: 'auto' }}> */}

              <AccordionDetails>
                <AppAccordian data={parent.children} />
              </AccordionDetails>
              {/* </div> */}
            </Accordion>
          )}
          {!parent.children && <Typography>- {parent.name} </Typography>}
        </div>
      );
    })}
  </div>
);

const AppHome = ({ items }) => {
  const { palette } = useTheme();

  return <div></div>;
};

export default AppHome;
