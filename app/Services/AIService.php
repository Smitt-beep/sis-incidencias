<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    public function sendMessage(string $systemContext, string $userPrompt)
    {
        $providers = [
            fn() => $this->callGroq($systemContext, $userPrompt),
            fn() => $this->callGemini($systemContext, $userPrompt),
            fn() => $this->callOpenRouter($systemContext, $userPrompt),
            fn() => $this->callAion($systemContext, $userPrompt),
            fn() => $this->callCohere($systemContext, $userPrompt),
            fn() => $this->callCloudflare($systemContext, $userPrompt),
        ];

        foreach ($providers as $provider) {
            try {
                $response = $provider();
                if ($response) {
                    return $response;
                }
            } catch (\Exception $e) {
                Log::warning("Proveedor de IA falló, rotando al siguiente: " . $e->getMessage());
                continue;
            }
        }

        return "Lo siento, todos los servicios de IA están ocupados temporalmente.";
    }

    private function callGroq(string $systemContext, string $userPrompt)
    {
        $response = Http::withToken(env('GROQ_API_KEY'))
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    ['role' => 'system', 'content' => $systemContext],
                    ['role' => 'user', 'content' => $userPrompt]
                ]
            ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        throw new \Exception("Groq error: " . $response->status());
    }

    private function callGemini(string $systemContext, string $userPrompt)
    {
        $response = Http::withToken(env('GEMINI_API_KEY'))
            ->post('https://generativelanguage.googleapis.com/v1beta/openai/chat/completions', [
                'model' => 'gemini-2.5-flash',
                'messages' => [
                    ['role' => 'system', 'content' => $systemContext],
                    ['role' => 'user', 'content' => $userPrompt]
                ]
            ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        throw new \Exception("Gemini error: " . $response->status());
    }

    private function callOpenRouter(string $systemContext, string $userPrompt)
    {
        $response = Http::withToken(env('OPENROUTER_API_KEY'))
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'google/gemma-4-26b-a4b-it:free',
                'messages' => [
                    ['role' => 'system', 'content' => $systemContext],
                    ['role' => 'user', 'content' => $userPrompt]
                ]
            ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        throw new \Exception("OpenRouter error: " . $response->status());
    }

    private function callAion(string $systemContext, string $userPrompt)
    {
        $response = Http::withToken(env('AION_API_KEY'))
            ->post('https://api.aionlabs.ai/v1/chat/completions', [
                'model' => 'aion-labs/aion-3.0-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $systemContext],
                    ['role' => 'user', 'content' => $userPrompt]
                ]
            ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        throw new \Exception("Aion Labs error: " . $response->status());
    }

    private function callCohere(string $systemContext, string $userPrompt)
    {
        $response = Http::withToken(env('COHERE_API_KEY'))
            ->post('https://api.cohere.ai/compatibility/v1/chat/completions', [
                'model' => 'command-r-plus',
                'messages' => [
                    ['role' => 'system', 'content' => $systemContext],
                    ['role' => 'user', 'content' => $userPrompt]
                ]
            ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        throw new \Exception("Cohere error: " . $response->status());
    }

    private function callCloudflare(string $systemContext, string $userPrompt)
    {
        $accountId = env('CLOUDFLARE_ACCOUNT_ID');
        $response = Http::withToken(env('CLOUDFLARE_API_KEY'))
            ->post("https://api.cloudflare.com/client/v4/accounts/{$accountId}/ai/v1/chat/completions", [
                'model' => '@cf/meta/llama-3.3-70b-instruct-fp8-fast',
                'messages' => [
                    ['role' => 'system', 'content' => $systemContext],
                    ['role' => 'user', 'content' => $userPrompt]
                ]
            ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        throw new \Exception("Cloudflare error: " . $response->status());
    }
}