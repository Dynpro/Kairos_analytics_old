import { useContext, useState } from 'react';
import { useIdleTimer } from 'react-idle-timer';
import AuthContext from 'app/contexts/JWTAuthContext';

const min = 60 * 1000;
const useIdleTimeout = ({ onPrompt, onIdle }) => {
  const [isIdle, setIdle] = useState(false);
  const { logout } = useContext(AuthContext);
  const handleIdle = () => {
    setIdle(true);
    logout();
  };
  const idleTimer = useIdleTimer({
    timeout: 25 * min,
    promptTimeout: 5 * min,
    onPrompt: onPrompt,
    onIdle: handleIdle,
    debounce: 500,
    crossTab: true,
  });
  return {
    isIdle,
    setIdle,
    idleTimer,
  };
};
export default useIdleTimeout;
