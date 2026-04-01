import { Tooltip } from '@mui/material';
import { useState } from 'react';
import screenfull from 'screenfull';

const determineIcon = (curState) => (!curState ? 'fullscreen_exit' : 'fullscreen');

const AppFullscreen = ({ Icon, StyledIconButton, TooltipName }) => {
  const [sfState, setSfState] = useState(screenfull.isEnabled);
  return (
    <Tooltip title={TooltipName}>
      <StyledIconButton
        style={{ cursor: 'pointer' }}
        onClick={() => {
          screenfull.toggle();
          setSfState(!sfState);
        }}
      >
        <Icon>{determineIcon(sfState)}</Icon>
      </StyledIconButton>
    </Tooltip>
  );
};

export default AppFullscreen;
