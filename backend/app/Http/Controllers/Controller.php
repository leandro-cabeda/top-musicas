<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

/**
 * @OA\Info(title="API de registro de musicas", version="2.0", description="API para registro de musicas")
 */
abstract class Controller
{

    /**
     * Extrai o ID do vídeo de uma URL do YouTube
     */
    protected function extractVideoId($url)
    {
        $patterns = [
            '/youtube\.com\/watch\?v=([^&]+)/',
            '/youtu\.be\/([^?]+)/',
            '/youtube\.com\/embed\/([^?]+)/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Busca informações do vídeo usando scraping
     */
    protected function getVideoInfo($videoId)
    {
        $url = "https://www.youtube.com/watch?v=" . $videoId;

        $response = Http::withOptions([
            'verify' => false,
        ])->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ])->get($url);

        if ($response->failed()) {
            throw new \Exception("Erro ao acessar o YouTube");
        }

        $html = $response->body();

        // Extrai o título
        if (!preg_match('/<title>(.+?) - YouTube<\/title>/', $html, $titleMatches)) {
            throw new \Exception("Não foi possível encontrar o título do vídeo");
        }

        $title = html_entity_decode($titleMatches[1], ENT_QUOTES);

        // Extrai as visualizações
        if (preg_match('/"viewCount":\s*"(\d+)"/', $html, $viewMatches)) {
            $views = (int)$viewMatches[1];
        } else if (preg_match('/\"viewCount\"\s*:\s*{.*?\"simpleText\"\s*:\s*\"([\d,\.]+)\"/', $html, $viewMatches)) {
            $views = (int)str_replace(['.', ','], '', $viewMatches[1]);
        } else {
            $views = 0;
        }

        return [
            'titulo' => $title,
            'visualizacoes' => $views,
            'youtube_id' => $videoId,
            'thumb' => 'https://img.youtube.com/vi/' . $videoId . '/hqdefault.jpg'
        ];
    }
}

class SubController extends Controller
{
    public function home()
    {
        return response('Bem-vindo à API de registro de músicas', 200);
    }
}
