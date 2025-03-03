<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sugestao;
use App\Models\Musica;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


/**
 * @OA\Tag(
 *     name="Sugestão de Musicas",
 *     description="Rotas de sugestão de musicas"
 * )
 */
class SugestaoController extends Controller
{

    /**
     * @OA\Post(
     *     path="/musicas/sugerir",
     *     summary="Realiza a sugestão da musica",
     *     tags={"Sugestão de Musicas"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"url"},
     *                 @OA\Property(property="url", type="string", example="url")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Música sugerida com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Música sugerida com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Erro ao sugerir musica",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao sugerir musica")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro interno do servidor")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Nao autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Nao autorizado")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Proibido",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Proibido")
     *         ),
     *     ),
     * )
     */
    public function sugerir(Request $request)
    {
        try {

            Log::info('Recebendo requisição', ['data' => $request->all()]);

            $validated = $request->validate([
                'url' => 'required|string',
            ]);

            Log::info('Requisição validada', ['validated' => $validated]);

            $videoId = $this->extractVideoId($request->url);

            if (!$videoId) return response()->json(['message' => 'URL inválida'], 400);

            $videoInfo = $this->getVideoInfo($videoId);

            if (!$videoInfo) return response()->json(['message' => 'Erro ao buscar informações do vídeo'], 400);

            $request->merge($videoInfo);

            $request->validate([
                'titulo' => 'required|string',
                'youtube_id' => 'required|string',
            ]);

            $musica = Musica::where('youtube_id', $request->youtube_id)->first();
            if ($musica) return response()->json(['message' => 'Musica ja cadastrada com esse youtube_id'], 400);

            $sugestao = Sugestao::where('youtube_id', $request->youtube_id)->first();
            if ($sugestao) {
                if ($sugestao->status == 'aprovado') return response()->json(['message' => 'Musica ja cadastrada com esse youtube_id'], 400);
                else if ($sugestao->status == 'pendente') return response()->json(['message' => 'Musica ja sugerida'], 400);
            }

            Sugestao::create([
                'user_id' => Auth::id(),
                'titulo' => $request->titulo,
                'youtube_id' => $request->youtube_id,
                'status' => 'pendente',
                'url' => $request->url,
            ]);

            return response()->json(['message' => 'Sugestão enviada para aprovação!']);
        } catch (\Throwable $e) {

            Log::error('Erro ao processar a requisição', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Erro interno no servidor.'], 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/musicas/sugestoes",
     *     summary="Retorna uma lista de musicas sugeridas",
     *     tags={"Lista de Sugestão de Musicas"},
     *     @OA\Response(
     *         response="200",
     *         description="Retorna uma lista de musicas sugeridas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Retorna uma lista de musicas sugeridas com sucesso"),
     *             @OA\Property(property="sugestoes", type="array",
     *              @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="titulo", type="string", example="Musica 1"),
     *                     @OA\Property(property="youtube_id", type="string", example="youtube_id"),
     *                     @OA\Property(property="status", type="string", example="pendente"),
     *                     @OA\Property(property="url", type="string", example="url")
     *                 )
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro interno do servidor")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Nao autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Nao autorizado")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Proibido",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Proibido")
     *         ),
     *     ),
     * )
     */
    public function listar(Request $request)
    {
        $limit = $request->get('limit', 5);
        $page = $request->get('page', 1);

        $sugestoes = Sugestao::with('user')
            ->orderBy('id', 'desc')
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json($sugestoes);
    }


    /**
     * @OA\Patch(
     *     path="/musicas/sugestoes/{id}/aprovar",
     *     summary="Aprova uma musica sugerida",
     *     tags={"Aprovação de Musica sugerida"},
     *     @OA\Response(
     *         response="200",
     *         description="Música sugerida aprovada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Música sugerida aprovada com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Erro ao aprovar musica sugerida",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao aprovar musica sugerida")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro interno do servidor")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Nao autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Nao autorizado")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Proibido",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Proibido")
     *         ),
     *     ),
     *     @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="ID da musica sugerida",
     *      required=true,
     *      @OA\Schema(
     *          type="integer",
     *          format="int64"
     *      )
     *  ),
     *  @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             @OA\Property(
     *                 property="id",
     *                 type="integer",
     *                 format="int64"
     *             ),
     *         )
     *     )
     *   )
     *
     * )
     */
    public function aprovar($id)
    {

        $sugestao = Sugestao::findOrFail($id);


        if (!$sugestao) return response()->json(['message' => 'Sugestão nao encontrada!']);


        if ($sugestao->status == 'aprovado') {
            return response()->json(['message' => 'Sugestão ja foi aprovada!']);
        }


        $videoId = $this->extractVideoId($sugestao->url);


        if (!$videoId) return response()->json(['message' => 'URL inválida'], 400);


        $videoInfo = $this->getVideoInfo($videoId);


        if (!$videoInfo) return response()->json(['message' => 'Erro ao buscar informações do vídeo'], 400);


        $sugestao->update(['status' => 'aprovado']);

	Log::info('Sugestão aprovado status', ['sugestao-status' => $sugestao->status]);

        $musica = Musica::create([
            'titulo' => $videoInfo['titulo'],
            'visualizacoes' => $videoInfo['visualizacoes'],
            'youtube_id' => $videoInfo['youtube_id'],
            'thumb' => $videoInfo['thumb'],
            'user_id' => Auth::id(),
            'url' => $sugestao->url
        ]);

        return response()->json(['message' => 'Sugestão aprovada!', 'musica' => $musica]);
    }

    /**
     * @OA\Patch(
     *     path="/musicas/sugestoes/{id}/reprovar",
     *     summary="Reprova uma musica sugerida",
     *     tags={"Reprovação de Musica sugerida"},
     *     @OA\Response(
     *         response="200",
     *         description="Música sugerida reprovada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Música sugerida reprovada com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Erro ao reprovar musica sugerida",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao reprovar musica sugerida")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro interno do servidor")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Nao autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Nao autorizado")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Proibido",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Proibido")
     *         ),
     *     ),
     * @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="ID da musica sugerida",
     *      required=true,
     *      @OA\Schema(
     *          type="integer",
     *          format="int64"
     *      )
     *  ),
     * @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             @OA\Property(
     *                 property="id",
     *                 type="integer",
     *                 format="int64"
     *             ),
     *         )
     *      )
     *    )
     * )
     *
     */
    public function reprovar($id)
    {
        $sugestao = Sugestao::findOrFail($id);

        if (!$sugestao) return response()->json(['message' => 'Sugestão nao encontrada!']);

        if ($sugestao->user_id != Auth::id()) {
            return response()->json(['message' => 'Sugestão nao pertence ao usuario logado!']);
        }

        if ($sugestao->status == 'reprovado') {
            return response()->json(['message' => 'Sugestão ja foi reprovada!']);
        }

        $sugestao->update(['status' => 'reprovado']);

        return response()->json(['message' => 'Sugestão reprovada!']);
    }
}
