import { LinearProgress } from '@mui/material';
import { SimpleCard } from 'app/components';
import React from 'react';

const AppTableLinearProgress = () => {
  const [completed, setCompleted] = React.useState(0);

  React.useEffect(() => {
    function progress() {
      setCompleted((oldCompleted) => {
        if (oldCompleted === 100) {
          return 0;
        }
        const diff = Math.random() * 10;
        return Math.min(oldCompleted + diff, 100);
      });
    }

    const timer = setInterval(progress, 500);
    return () => {
      clearInterval(timer);
    };
  }, []);

  return (
    <SimpleCard>
      <LinearProgress />
      <br />
      <LinearProgress color="secondary" />
    </SimpleCard>
  );
};

export default AppTableLinearProgress;
