"use client"

export const dynamic = "force-dynamic";

import { useEffect, useContext } from 'react';
import { useRouter } from 'next/navigation';
import AuthContext from '../../app/context/AuthContext';


export default function Logout() {
  const { logout, user } = useContext(AuthContext);
  const router = useRouter();

  useEffect(() => {
    if (!user) router.push('/login');

    logout();
    router.push('/login');
  }, [logout, router]);

  return <p className="text-center text-2xl font-bold mt-10">Desconectando...</p>;
}
