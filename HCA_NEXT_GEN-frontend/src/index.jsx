import { CssBaseline } from '@mui/material';
import { StyledEngineProvider } from '@mui/styled-engine';
import 'perfect-scrollbar/css/perfect-scrollbar.css';
import ReactDOM from 'react-dom';
import { BrowserRouter } from 'react-router-dom';
import App from './app/App';
import * as serviceWorker from './serviceWorker';
import { SnackbarProvider } from 'notistack';
import { SnackbarUtilsConfigurator } from 'SnackbarUtils';
import styled from '@emotion/styled';

const StyledSnackbarProvider = styled(SnackbarProvider)`
  &.SnackbarContainer-root {
    height: '50px';
  }
`;

ReactDOM.render(
  <StyledEngineProvider injectFirst>
    <BrowserRouter>
      <StyledSnackbarProvider
        autoHideDuration={2000}
        anchorOrigin={{ horizontal: 'right', vertical: 'top' }}
        style={{ marginTop: '50px' }}
      >
        <CssBaseline />
        <SnackbarUtilsConfigurator />
        <App />
      </StyledSnackbarProvider>
    </BrowserRouter>
  </StyledEngineProvider>,
  document.getElementById('root')
);

// for IE-11 support un-comment cssVars() and it's import in this file
// and in MatxTheme file

// If you want your app to work offline and load faster, you can change
// unregister() to register() below. Note this comes with some pitfalls.
// Learn more about service workers: https://bit.ly/CRA-PWA
serviceWorker.unregister();
