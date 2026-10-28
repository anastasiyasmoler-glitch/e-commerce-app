import { QueryClientProvider } from '@tanstack/react-query'
import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { Toaster } from 'react-hot-toast'
import { queryClient } from './api/queryClient.ts'
import { sessionQueryOptions } from './hooks/useSession.ts'
import { CartProvider } from './lib/cart/CartContext.tsx'
import { SelectedStoreProvider } from './catalog/SelectedStoreContext.tsx'
import App from './App.tsx'
import './styles/app.css'

void queryClient.query(sessionQueryOptions)

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <QueryClientProvider client={queryClient}>
      <SelectedStoreProvider>
        <CartProvider>
          <App />
        </CartProvider>
      </SelectedStoreProvider>
      <Toaster
        position="top-center"
        toastOptions={{
          duration: 4000,
          style: {
            background: 'var(--surface)',
            color: 'var(--ink)',
            border: '1px solid var(--line)',
            borderRadius: '6px',
            fontFamily: 'var(--font-sans)',
          },
          success: {
            iconTheme: { primary: 'var(--success)', secondary: 'var(--surface)' },
          },
          error: {
            iconTheme: { primary: 'var(--danger)', secondary: 'var(--surface)' },
          },
        }}
      />
    </QueryClientProvider>
  </StrictMode>,
)
