"use client"

export const dynamic = "force-dynamic";

import { useState, useContext } from "react";
import AuthContext from "../context/AuthContext";
import { useRouter } from "next/navigation";
import routes from "@/config/routes";
import { toast } from 'react-toastify';
import Loading from "@/components/loading/loading";
import { PersonAdd } from "@mui/icons-material";

export default function RegisterPage() {
    const { user, register } = useContext(AuthContext);
    const router = useRouter();
    const [loading, setLoading] = useState(false);

    const [formData, setFormData] = useState({
        name: "",
        email: "",
        password: "",
        role: "user",
    });

    async function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        setLoading(true);

        try {
            await register(formData);
            toast.success('Cadastro realizado com sucesso!');

            router.push(routes.home);
        } catch (err: any) {
            toast.error('Falha no cadastro. Verifique suas credenciais. => ' + err?.response?.data?.message);
        } finally {
            setLoading(false);
        }
    }

    return (
        <div className="flex justify-center items-center h-screen">
            <form onSubmit={handleSubmit} className="bg-white p-6 rounded-lg shadow-md w-96">
                <h2 className="text-xl font-semibold mb-4">Registro</h2>

                <label className="block mb-2">
                    Nome:
                    <input
                        type="text"
                        name="name"
                        value={formData.name}
                        onChange={(e: React.ChangeEvent<HTMLInputElement>) => setFormData({ ...formData, name: e.target.value })}
                        required
                        className="w-full p-2 border rounded"
                    />
                </label>

                <label className="block mb-2">
                    E-mail:
                    <input
                        type="email"
                        name="email"
                        value={formData.email}
                        onChange={(e: React.ChangeEvent<HTMLInputElement>) => setFormData({ ...formData, email: e.target.value })}
                        required
                        className="w-full p-2 border rounded"
                    />
                </label>

                <label className="block mb-2">
                    Senha:
                    <input
                        type="password"
                        name="password"
                        value={formData.password}
                        onChange={(e: React.ChangeEvent<HTMLInputElement>) => setFormData({ ...formData, password: e.target.value })}
                        required
                        className="w-full p-2 border rounded"
                    />
                </label>

                {user?.role === "admin" && (
                    <label className="block mb-2">
                        Tipo de Usuário:
                        <select
                            value={formData.role}
                            onChange={(e: React.ChangeEvent<HTMLSelectElement>) => setFormData({ ...formData, role: e.target.value })}
                            className="w-full p-2 border rounded"
                        >
                            <option value="user">Usuário</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </label>
                )}

                {loading ? <Loading /> :
                    <button type="submit" className="w-full bg-blue-500 text-white p-2 mt-4 rounded">
                        <PersonAdd fontSize="small" className="mr-2" />
                        Registrar
                    </button>
                }
            </form>
        </div>
    );
}
