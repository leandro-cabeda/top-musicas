"use client"

export const dynamic = "force-dynamic";

import { useContext, useState } from "react";
import { useRouter } from 'next/navigation';
import AuthContext from "../app/context/AuthContext";
import routes from "../config/routes";
import { Link } from "@mui/material";
import { usePathname } from "next/navigation";
import { AdminPanelSettings, Login, Logout, PersonAdd } from '@mui/icons-material';

const Navbar = () => {
    const { user } = useContext(AuthContext);
    const router = useRouter();
    const pathname = usePathname();


    return (
        <nav className="flex justify-between items-center p-4 nav">
            <div className="menu-start">
                {!user && (<Link href={routes.login}
                    className={`${pathname === routes.login ? "active" : ""} login-link`}>
                    <Login fontSize="small" />
                    Login
                </Link>)}

                <Link href={routes.register}
                    className={`${pathname === routes.register ? "active" : ""} register-link`}>
                    <PersonAdd fontSize="small" />
                    Register
                </Link>
            </div>

            <ul className="menu-center">
                <li className={`${pathname === routes.home ? "active" : ""} links 
                    ${!user ? " home-link" : ""}`}
                    onClick={() => router.push(routes.home)}>🏠 Home</li>

                {user && (
                    <>
                        <li className={`${pathname === routes.music ? "active" : ""} links`}
                            onClick={() => router.push(routes.music)}>🎵 Músicas</li>

                        {user.role === "admin" && (
                            <li className={`${pathname === routes.admin ? "active" : ""} links`}
                                onClick={() => router.push(routes.admin)}>
                                <AdminPanelSettings fontSize="small" />
                                Administrativo
                            </li>
                        )}

                    </>)}
            </ul>

            {user ? (
                <Link href={routes.logout}
                    className="links menu-end">
                    <Logout fontSize="small" />
                    Logout
                </Link>
            ) : <div className="menu-end"></div>}
        </nav>
    );
};

export default Navbar;
