"use client"

export const dynamic = "force-dynamic";

import { createContext, useState, useEffect } from 'react';
import Api from '../service/api';
import Cookies from 'js-cookie';
import { Context, User } from '@/components/types';

const AuthContext = createContext<Context>({
    user: null,
    login: async () => { }, logout: () => { },
    register: async () => { }
});

export const AuthProvider = ({ children }: any) => {
    const [user, setUser] = useState<User | null>(null);

    const api = new Api(() => {
        window.location.href = "/login";
      });

    useEffect(() => {
        const token = Cookies.get('token') || localStorage.getItem('token');
        if (!token) {
            logout();
            setUser(null);
        } else {
            setUser(JSON.parse(localStorage.getItem('user') || ''));
        }
    }, []);

    const login = async (credentials: { email: string; password: string }) => {
        const response = await api.post('/login', credentials);
        const { token, user } = response.data;

        Cookies.set('token', token, { expires: 7 });
        setUser(user);
        localStorage.setItem('user', JSON.stringify(user));
    };

    const register = async (credentials: { name: string; email: string; password: string, role: string }) => {
        const response = await api.post('/register', credentials);
        const { token, user } = response.data;

        Cookies.set('token', token, { expires: 7 });
        setUser(user);
        localStorage.setItem('user', JSON.stringify(user));
    };

    const logout = () => {
        Cookies.remove('token');
        setUser(null);
        localStorage.removeItem('user');
    };

    return (
        <AuthContext.Provider value={{ user, login, logout, register }}>
            {children}
        </AuthContext.Provider>
    );
};

export default AuthContext;
