export const dynamic = "force-dynamic";

import { AuthProvider } from './context/AuthContext';
import './globals.css';
import 'react-toastify/dist/ReactToastify.css';
import { ToastContainer } from "react-toastify";
import Navbar from '../components/Navbar';

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="pt-BR">
      <body>
        <AuthProvider>
          <Navbar />
          <main>
            {children}
          </main>
          <ToastContainer position="top-right" autoClose={5000} closeOnClick={true} />
        </AuthProvider>
      </body>
    </html>
  );
}

