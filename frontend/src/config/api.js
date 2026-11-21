// Centralized API Configuration for CareerPath AI
// In local development, defaults to 'http://127.0.0.1:8000/api'
// In production (Vercel/Netlify), reads from VITE_API_BASE_URL environment variable

export const API_BASE_URL = (
  import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api'
).replace(/\/+$/, '');

export const BACKEND_ORIGIN = API_BASE_URL.replace(/\/api\/?$/, '');

export default API_BASE_URL;
