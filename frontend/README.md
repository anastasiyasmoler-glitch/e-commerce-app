# Domo storefront

Customer SPA (not Admin). React + TypeScript + Vite. UI from the Domo `index.html` mock.

```powershell
cd frontend
npm install
npm run dev
```

http://localhost:5173 — header + Sign in (`POST https://localhost/login`). Access JWT stays in memory.

Auth must allow CORS from this origin. Refresh cookie is HttpOnly on Auth.
