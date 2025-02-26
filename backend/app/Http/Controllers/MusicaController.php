<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Musica;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


/**
 * @OA\Tag(
 *     name="Musicas",
 *     description="Rotas de musicas"
 * )
 */
class MusicaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/musicas",
     *     summary="Retorna uma lista de músicas",
     *     tags={"Lista de Musicas"},
     *     @OA\Response(
     *         response="200",
     *         description="Lista de músicas retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="musicas", type="array",
     *                  @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="titulo", type="string", example="Titulo da musica"),
     *                     @OA\Property(property="url", type="string", example="url"),
     *                     @OA\Property(property="visualizacoes", type="integer", example=0),
     *                 ))
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro interno do servidor")
     *         )
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
     *     )
     * )
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 5);
        $page = $request->get('page', 1);

        $musicas = Musica::with('user')
            ->orderBy('visualizacoes', 'desc')
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json($musicas);
    }

    /**
     * @OA\Post(
     *     path="/musicas",
     *     summary="Realiza o cadastro da musica",
     *     tags={"Cadastro de Musicas"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"url"},
     *                 @OA\Property(property="titulo", type="string", example="Titulo da musica"),
     *                 @OA\Property(property="url", type="string", example="url")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Música cadastrada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Música cadastrada com sucesso"),
     *             @OA\Property(property="musica", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="titulo", type="string", example="Titulo da musica"),
     *                     @OA\Property(property="url", type="string", example="url"),
     *                     @OA\Property(property="visualizacoes", type="integer", example=0)
     *                 )
     *             )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Erro ao cadastrar música",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao cadastrar musica"),
     *             )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro interno do servidor"),
     *             )
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
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|string',
        ]);

        $videoId = $this->extractVideoId($request->url);

        if (!$videoId) return response()->json(['message' => 'URL inválida'], 400);

        $videoInfo = $this->getVideoInfo($videoId);

        if (!$videoInfo) return response()->json(['message' => 'Erro ao buscar informações do vídeo'], 400);

        $data = array_merge($request->all(), $videoInfo);

        if (!empty($request->titulo)) {
            $data['titulo'] = $request->titulo;
        }

        Validator::make($data, [
            'titulo' => 'required|string',
            'youtube_id' => 'required|string',
        ])->validate();

        $musica = Musica::where('youtube_id', $data['youtube_id'])->first();
        if ($musica) return response()->json(['message' => 'Musica ja cadastrada com esse youtube_id'], 400);

        return Musica::create($data);
    }


    /**
     * @OA\Get(
     *     path="/musicas/{id}",
     *     summary="Retorna uma musica",
     *     tags={"Detalhe de Musicas"},
     *     @OA\Response(
     *         response="200",
     *         description="Música retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="musica", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="titulo", type="string", example="Titulo da musica"),
     *                     @OA\Property(property="url", type="string", example="url"),
     *                     @OA\Property(property="visualizacoes", type="integer", example=0)
     *                 )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Erro ao retornar musica",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao retornar musica")
     *         )
     *     ),
     *     @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID da musica",
     *     required=true,
     *     @OA\Schema(
     *     type="integer",
     *     format="int64"
     *      )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Música não encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Música não encontrada")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro interno do servidor")
     *         )
     *     ),
     *      @OA\Response(
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
     *     )
     * )
     */
    public function show(string $id)
    {
        $musica = Musica::find($id);

        if (!$musica) return response()->json(['message' => 'Musica nao encontrada'], 404);

        return $musica;
    }


    /**
     * @OA\Put(
     *     path="/musicas/{id}",
     *     summary="Atualiza uma musica pelo id",
     *     tags={"Atualização de Musica por id"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"url"},
     *                 @OA\Property(property="titulo", type="string", example="Titulo da musica"),
     *                 @OA\Property(property="url", type="string", example="url")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Música atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Música atualizada com sucesso"),
     *             @OA\Property(property="musica", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="titulo", type="string", example="Titulo da musica"),
     *                     @OA\Property(property="url", type="string", example="url"),
     *                     @OA\Property(property="visualizacoes", type="integer", example=0)
     *                 )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Erro ao atualizar musica",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao atualizar musica")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Música nao encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Música nao encontrada")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro interno do servidor")
     *         )
     *     ),
     *     @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID da musica",
     *     required=true,
     *     @OA\Schema(
     *     type="integer",
     *     format="int64"
     *      )
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
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        Log::info('id update: ', ["id" => $id]);
        $musica = Musica::find($id);

        if (!$musica) return response()->json(['message' => 'Musica nao encontrada'], 404);

        $videoId = $this->extractVideoId($request->url);

        if (!$videoId) return response()->json(['message' => 'URL inválida'], 400);

        $videoInfo = $this->getVideoInfo($videoId);

        if (!$videoInfo) return response()->json(['message' => 'Erro ao buscar informações do vídeo'], 400);

        $musicaExistVideoId = Musica::where('youtube_id', $videoId)->first();

        if ($musicaExistVideoId && $musicaExistVideoId->id != $musica->id) {
            return response()->json(['message' => 'Link da música já cadastrada'], 400);
        }


        $data = array_merge($request->all(), $videoInfo);

        if (!empty($request->titulo)) {
            $data['titulo'] = $request->titulo;
        }

        Validator::make($data, [
            'titulo' => 'required|string',
            'youtube_id' => 'required|string',
        ])->validate();

        return $musica->update($data);
    }


    /**
     * @OA\Delete(
     *     path="/musicas/{id}",
     *     summary="Remove uma musica pelo id",
     *     tags={"Remoção de Musica por id"},
     *     @OA\Response(
     *         response="200",
     *         description="Música removida com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Música removida com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Erro ao remover musica",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao remover musica")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Música nao encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Música nao encontrada")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro interno do servidor")
     *         )
     *     ),
     *     @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="ID da musica",
     *     required=true,
     *     @OA\Schema(
     *     type="integer",
     *     format="int64"
     *      )
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
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $musica = Musica::find($id);

        if (!$musica) return response()->json(['message' => 'Musica nao encontrada'], 404);

        return Musica::destroy($id);
    }
}
