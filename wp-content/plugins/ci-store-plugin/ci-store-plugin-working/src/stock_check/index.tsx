import '../assets/plugin.scss';

import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import * as React from 'react';
import { createRoot } from 'react-dom/client';

import { StockCheck } from './StockCheck';

export const render = (id: string) => {
  const queryClient = new QueryClient();
  const root = createRoot(document.getElementById(id));
  root.render(
    <QueryClientProvider client={queryClient}>
      <StockCheck />
    </QueryClientProvider>
  );
};
