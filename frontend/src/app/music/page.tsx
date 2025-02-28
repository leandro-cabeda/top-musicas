"use client"

export const dynamic = "force-dynamic";

import Loading from '@/components/loading/loading';
import { useState, useEffect, useContext, useRef } from 'react';
import { toast } from 'react-toastify';
import Api from '../../app/service/api';
import { Musica } from '@/components/types';
import AuthContext from '@/app/context/AuthContext';
import { useRouter } from 'next/navigation';
import { ChevronLeft, ChevronRight, Delete, Edit, Link, PlayArrow } from '@mui/icons-material';

const MusicPage = () => {
    const { user } = useContext(AuthContext);
    const [top5Musicas, setTop5Musicas] = useState<Musica[]>([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [loading, setLoading] = useState(false);
    const router = useRouter();
    const [deleting, setDeleting] = useState(false);
    const [lastPage, setLastPage] = useState(1);
    const [isEditing, setIsEditing] = useState(false);
    const [editingMusic, setEditingMusic] = useState<Musica | null>(null);
    const formRef: any = useRef(null);
    const limit = 5;

    const api = new Api(() => {
        window.location.href = "/login";
      });

    const getTopMusics = async (page: number = 1) => {
        setLoading(true);
        try {
            const res = await api.get(`/musicas?page=${page}&limit=${limit}`);
            setTop5Musicas(res.data?.data);
            setTotalPages(res.data?.total);
            setLastPage(res.data?.last_page);

        } catch (error: any) {
            toast.error("Erro ao carregar as músicas. => " + error?.response?.data?.message);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        getTopMusics();
    }, []);

    useEffect(() => {
        getTopMusics(currentPage);
    }, [currentPage]);

    const nextPage = () => {
        if (currentPage < totalPages) {
            setCurrentPage(currentPage + 1);
        }
    };

    const prevPage = () => {
        if (currentPage > 1) {
            setCurrentPage(currentPage - 1);
        }
    };

    const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const formData = new FormData(e.currentTarget);
        const url = formData.get('url') as string;

        if (!url.trim()) {
            toast.error("Por favor, insira uma URL válida!");
            return;
        }

        setLoading(true);

        try {
            await api.post('/musicas/sugerir', { url });
            getTopMusics();
            toast.success("Música sugerida com sucesso!");
            setCurrentPage(1);
        } catch (error: any) {
            toast.error("Erro ao enviar a musica => " + error?.response?.data?.message);
        } finally {
            setLoading(false);
        }
    };

    const handleEditMusic = async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        if (!editingMusic) return;

        setIsEditing(true);
        console.log(editingMusic);

        try {
            await api.put(`/musicas/${editingMusic.id}`, {
                titulo: editingMusic.titulo,
                url: editingMusic.url,
            });
            toast.success('Música editada com sucesso!');
            getTopMusics();
            setEditingMusic(null);
            setCurrentPage(1);
        } catch (error: any) {
            toast.error('Erro ao editar música. => ' + error?.response?.data?.message);
        } finally {
            setIsEditing(false);
        }
    };

    const handleDelete = async (id: number) => {
        if (!window.confirm("Tem certeza que deseja excluir esta música?")) return;

        setDeleting(true);
        try {
            await api.delete(`/musicas/${id}`);
            toast.success("Música excluída com sucesso!");
            getTopMusics();
            setCurrentPage(1);
        } catch (error) {
            toast.error("Erro ao excluir música.");
        } finally {
            setDeleting(false);
        }
    };

    return (
        <>
            <header className="header">
                <img src="https://dicionariompb.com.br/wp-content/uploads/2021/04/Tiao-Carreiro-e-Pardinho.png"
                    alt="Tião Carreiro" className="artist-img" />
                <h1 className="title">Top 5 Músicas Mais Tocadas</h1>
                <h2 className="subtitle">Tião Carreiro & Pardinho</h2>
            </header>
            <div className="container">
                <div className="submit-form">
                    <h3 className="section-title">Sugerir Novo Link de Música</h3>
                    <form onSubmit={handleSubmit}>
                        <input type="url" name="url" placeholder="Cole aqui o link do YouTube" required
                            className="input-url" />
                        <button type="submit" className="submit-button">
                            Enviar Link
                            <Link fontSize="small" style={{ marginLeft: '5px' }} />
                        </button>
                    </form>
                </div>

                <h3 className="section-title">Ranking Atual</h3>

                {loading ? (
                    <Loading />
                ) : (
                    <>
                        {top5Musicas?.map((item, index) => (

                            <div className='music-link' key={item.youtube_id}>
                                <div className="music-card">
                                    <div className="rank">{index + 1}</div>
                                    <div className="music-info">
                                        <div className="music-title">Titulo: {item.titulo}</div>
                                        <div className="views">{item.visualizacoes} visualizações</div>
                                        {item.user && (<>
                                            <p className="music-user">Sugerido por: {item.user.name || 'sem nome'}</p>
                                            <p className="music-user">Email: {item.user.email}</p>
                                        </>)}
                                        <a className='link-video' href={`https://www.youtube.com/watch?v=${item.youtube_id}`} target="_blank">
                                            Assistir Video
                                            <PlayArrow fontSize="small" />
                                        </a>
                                        {item.url && <p className="music-views">URL: {item.url}</p>}
                                    </div>
                                    <img src={item.thumb} alt={`Thumbnail ${item.titulo}`} className="thumbnail" />
                                    {user?.role === 'admin' && (
                                        <div className="admin-actions">
                                            <button onClick={() => {
                                                setEditingMusic(item);
                                                setTimeout(() => {
                                                    if (formRef.current) {
                                                        formRef.current.scrollIntoView({ behavior: "smooth", block: "start" });
                                                        formRef.current.focus();
                                                    }
                                                }, 100);
                                            }}
                                                className="edit-button"
                                            >
                                                <Edit fontSize="small" />
                                                Editar
                                            </button>
                                            <button onClick={() => handleDelete(Number(item.id))}
                                                className="delete-button">
                                                <Delete fontSize="small" />
                                                {deleting ? 'Excluindo...' : 'Excluir'}
                                            </button>
                                        </div>
                                    )}
                                </div>
                            </div>
                        ))}

                        <div className="music-actions">
                            <button disabled={currentPage === 1}
                                className={`prev-button top-margin
                            ${currentPage === 1 ? 'disabled' : ''}`}
                                onClick={prevPage}>
                                <ChevronLeft fontSize="small" className='mr-2' />
                                Anterior
                            </button>
                            <span className='page-info top-margin'> Página {currentPage} de {lastPage} total {totalPages}</span>
                            <button disabled={currentPage >= Math.ceil(totalPages / limit)}
                                className={`next-button top-margin
                            ${currentPage >= Math.ceil(totalPages / limit) ? 'disabled' : ''}`}
                                onClick={nextPage}>
                                Próxima
                                <ChevronRight fontSize="small" className='ml-2' />
                            </button>
                        </div>
                    </>
                )}
            </div>

            {editingMusic && (
                <div className="container" ref={formRef}>
                    <form onSubmit={handleEditMusic} className="modal-content">
                        <h2 className="modal-title">Editar Link de Música</h2>
                        <input
                            type="text"
                            value={editingMusic.titulo}
                            placeholder="Título"
                            onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEditingMusic({ ...editingMusic, titulo: e.target.value })}
                            className='input-title rounded-md'
                            required
                        />
                        <input
                            type="url"
                            value={editingMusic.url}
                            placeholder="URL do YouTube"
                            onChange={(e: React.ChangeEvent<HTMLInputElement>) => setEditingMusic({ ...editingMusic, url: e.target.value })}
                            className="input-url rounded-md"
                            required
                        />
                        {isEditing ? <Loading /> :
                            (<div className='flex justify-start'>
                                <button type="submit" className='bg-green-500 mr-5 text-white p-2 mt-4 rounded w-4/12'>Salvar</button>
                                <button type="button" className='bg-red-500 text-white p-2 mt-4 rounded w-4/12' onClick={() => setEditingMusic(null)}>Cancelar</button>
                            </div>)}
                    </form>
                </div>
            )}
        </>
    );
};

export default MusicPage;
