"use client"

export const dynamic = "force-dynamic";

import { useState, useContext, ChangeEvent } from 'react';
import { useRouter } from 'next/navigation';
import AuthContext from '../context/AuthContext';
import { toast } from 'react-toastify';
import Loading from '@/components/loading/loading';
import { Login } from '@mui/icons-material';

export default function LoginPage() {
  const { login } = useContext(AuthContext);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const router = useRouter();
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      await login({ email, password });
      toast.success('Login realizado com sucesso!');
      router.push('/');
    } catch (err: any) {
      toast.error('Falha no login. Verifique suas credenciais. => ' + err?.response?.data?.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex justify-center items-center h-screen">
      <form onSubmit={handleSubmit} className="bg-white p-6 rounded-lg shadow-md w-96">
        <h2 className="text-xl font-semibold mb-4">Login</h2>

        <label className="block mb-2">
          Email:
          <input
            type="email"
            placeholder="Email"
            value={email}
            onChange={(e: ChangeEvent<HTMLInputElement>) => setEmail(e.target.value)}
            className="w-full p-2 border rounded"
            required
          />
        </label>

        <label className="block mb-2">
          Senha:
          <input
            type="password"
            placeholder="Senha"
            value={password}
            onChange={(e: ChangeEvent<HTMLInputElement>) => setPassword(e.target.value)}
            className="w-full p-2 border rounded"
            required
          />
        </label>

        {loading ? <Loading /> :
          <button type="submit" className="w-full bg-blue-500 text-white p-2 mt-4 rounded">
            <Login fontSize="small" className='mr-2' />
            Entrar
          </button>
        }
      </form>
    </div>
  );
}
