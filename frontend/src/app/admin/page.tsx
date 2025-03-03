"use client"

export const dynamic = "force-dynamic";

import { ChangeEvent, useContext, useEffect, useState } from 'react';
import AuthContext from '../../app/context/AuthContext';
import Api from '../../app/service/api';
import { toast } from 'react-toastify';
import Loading from '@/components/loading/loading';
import '../../components/styles/admin.css';
import { Sugestao } from '@/components/types';
import { useRouter } from 'next/navigation';
import { Add, Cancel, CheckCircle, ChevronLeft, ChevronRight, PlayArrow } from '@mui/icons-material';

const AdminPage = () => {
  const { user } = useContext(AuthContext);
  const [sugestoes, setSugestoes] = useState<Sugestao[]>([]);
  const [loading, setLoading] = useState(true);
  const [newSugestao, setNewSugestao] = useState({ url: '', titulo: '' });
  const [isAdding, setIsAdding] = useState(false);
  const [approveOrReject, setApproveOrReject] = useState(false);
  const router = useRouter();
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const limit = 5;

  const api = new Api(() => {
    window.location.href = "/login";
  });

  useEffect(() => {
    if (typeof window !== "undefined" && user?.role !== 'admin') {
      setTimeout(() => {
        window.location.href = "/";
      }, 5000);
    }
  }, []);

  useEffect(() => {
    if (!user) {
      router.push('/login');
    } else if (user.role !== 'admin') {
      router.push('/');
    }

    fetchSugestoes();
  }, [user, router]);

  const fetchSugestoes = async (page: number = 1) => {
    try {
      const res = await api.get(`/musicas/sugestoes?page=${page}&limit=${limit}`);
      setSugestoes(res.data?.data);
      setTotalPages(res.data?.total);
      setLastPage(res.data?.last_page);
    } catch (error: any) {
      toast.error('Erro ao carregar as sugestões de musicas. => ' + error?.response?.data?.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchSugestoes(currentPage);
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

  const handleAddSugestao = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setIsAdding(true);

    try {
      await api.post('/musicas/sugerir', newSugestao);
      toast.success('Musica sugerida com sucesso!');
      fetchSugestoes();
      setCurrentPage(1);
    } catch (error: any) {
      toast.error('Erro ao sugerir musica. => ' + error?.response?.data?.message);
    } finally {
      setIsAdding(false);
    }
  };

  const handleApprove = async (id: number) => {
    setApproveOrReject(true);

    try {
      await api.patch(`/musicas/sugestoes/${id}/aprovar`);
      toast.success('Música sugerida aprovada!');
      fetchSugestoes();
      setCurrentPage(1);
    } catch (error: any) {
      toast.error('Erro ao aprovar música sugerida. => ' + error?.response?.data?.message);
    } finally {
      setApproveOrReject(false);
    }
  };

  const handleReject = async (id: number) => {
    setApproveOrReject(true);

    try {
      await api.patch(`/musicas/sugestoes/${id}/reprovar`);
      toast.success('Música sugerida reprovada!');
      fetchSugestoes();
      setCurrentPage(1);
    } catch (error: any) {
      toast.error('Erro ao reprovar música sugerida. => ' + error?.response?.data?.message);
    } finally {
      setApproveOrReject(false);
    }
  };


  if (user?.role !== 'admin' || !user) {
    <div>Acesso restrito. Somente administradores podem acessar esta página.</div>;
    return;
  }

  return (
    <div className="admin-container">
      <h1 className="admin-title">Painel Administrativo</h1>

      <form onSubmit={handleAddSugestao} className="bg-white p-6 rounded-lg shadow-md w-full mb-4">
        <h3 className="text-xl font-semibold mb-4 text-center">Adicionar Sugestão de Música</h3>
        <input
          type="text"
          value={newSugestao.titulo}
          onChange={(e: ChangeEvent<HTMLInputElement>) => setNewSugestao({ ...newSugestao, titulo: e.target.value })}
          placeholder="Título"
          className="input-title rounded-md"
          required
        />
        <input
          type="url"
          value={newSugestao.url}
          onChange={(e: ChangeEvent<HTMLInputElement>) => setNewSugestao({ ...newSugestao, url: e.target.value })}
          placeholder="URL do YouTube"
          className="input-url rounded-md"
          required
        />
        {isAdding ? <Loading /> :
          <button type="submit"
            className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            <Add fontSize="small" />
            Adicionar Sugestão de Música
          </button>
        }
      </form>

      {loading ? (
        <Loading />
      ) : (
        <>
          {sugestoes?.map((sugestao) => (
            <div key={sugestao.id} className="music-link" style={{ marginRight: 0 }}>
              <div className="music-card">
                <div className="music-info">
                  <h3 className="music-title">Titulo: {sugestao.titulo}</h3>
                  {sugestao.url && <p className="music-views">URL: {sugestao.url}</p>}
                  <p className="music-status">Status: {sugestao.status}</p>
                  {sugestao.user && (<>
                    <p className="music-user">Sugerido por: {sugestao.user.name || 'sem nome'}</p>
                    <p className="music-user">Email: {sugestao.user.email}</p>
                  </>)}
                  <a className='link-video' href={`https://www.youtube.com/watch?v=${sugestao.youtube_id}`} target="_blank">
                    Assistir Video
                    <PlayArrow fontSize="small" />
                  </a>
                  <div className="admin-actions2 mt-2">
                    {approveOrReject ? <Loading /> :
                      (<>
                        <button onClick={() => handleApprove(Number(sugestao.id))} className="approve">
                          <CheckCircle fontSize="small" className='mr-2' />
                          Aprovar
                        </button>
                        <button onClick={() => handleReject(Number(sugestao.id))} className="reject">
                          <Cancel fontSize="small" className='mr-2' />
                          Reprovar
                        </button>
                      </>)}
                  </div>
                </div>
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
  );
};

export default AdminPage;

