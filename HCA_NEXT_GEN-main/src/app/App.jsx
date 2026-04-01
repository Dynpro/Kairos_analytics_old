//import '../fake-db';
import { Provider } from 'react-redux';
import { useRoutes } from 'react-router-dom';
import { MatxTheme } from './components';
import { AuthProvider } from './contexts/JWTAuthContext';
import { SettingsProvider } from './contexts/SettingsContext';
import { Store, persistor } from './redux/Store';
import routes from './routes';
import { PersistGate } from 'redux-persist/integration/react';

const App = () => {
  const content = useRoutes(routes);
  return (
    <Provider store={Store}>
      <PersistGate loading={null} persistor={persistor}>
        <SettingsProvider>
          <MatxTheme>
            <AuthProvider>{content}</AuthProvider>
          </MatxTheme>
        </SettingsProvider>
      </PersistGate>
    </Provider>
  );
};

export default App;
