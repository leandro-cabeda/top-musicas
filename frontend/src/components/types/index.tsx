export type Musica = {
    id?: number;
    youtube_id?: string;
    titulo?: string;
    visualizacoes: number;
    thumb?: string;
    url?: string;
    user?: User;
};

export type Sugestao = {
    id?: number;
    user_id?: number;
    user?: User;
    titulo?: string;
    youtube_id?: string;
    status: 'pendente' | 'aprovado' | 'reprovado';
    url?: string;
}

export type User = {
    id?: number;
    name: string;
    email: string;
    role: 'admin' | 'user';
};

export type Context = {
    user: User | null;
    login: (credentials: { email: string; password: string }) => Promise<void>;
    logout: () => void;
    register: (credentials: { name: string; email: string; password: string, role: string }) => Promise<void>;
};