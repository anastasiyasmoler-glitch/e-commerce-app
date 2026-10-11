import { QueryClientProvider } from '@tanstack/react-query'
import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { queryClient } from './api/queryClient.ts'
import { sessionQueryOptions } from './hooks/useSession.ts'
import App from './App.tsx'
import './styles/app.css'

void queryClient.query(sessionQueryOptions)

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <QueryClientProvider client={queryClient}>
      <App />
    </QueryClientProvider>
  </StrictMode>,
)
