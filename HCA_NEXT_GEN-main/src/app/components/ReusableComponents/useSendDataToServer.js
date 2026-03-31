import React, { useState } from 'react';
import axios from 'axios';
import commonConfig from '../commonConfig';
import { useLocation, useNavigate } from 'react-router-dom';

export default function useSendDataToServer(data, shouldUpdateOld, alertMsg, routeDest, apiDest) {
  const [loading, setLoading] = useState(false);
  const [open, setOpen] = useState(false);

  const navigate = useNavigate();

  async function sendDataToServer(data) {
    console.log(data);
  }

  return { asyncLoading: loading, sendDataToServer };
}
