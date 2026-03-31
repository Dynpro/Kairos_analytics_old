import React from 'react';
import { useSelector } from 'react-redux';

export default function AppCurrentClient() {
  const currentClient = useSelector((state) => state.currentClient.client);
  const renderCurrentClient = () => {
    if (currentClient)
      return (
        <span
          style={{
            color: '#fff',
            fontWeight: 'bold',
            fontSize: '24px',
          }}
        >
          {currentClient?.client_name}
        </span>
      );
    else return null;
  };
  return renderCurrentClient();
}
