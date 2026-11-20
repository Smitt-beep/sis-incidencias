<?php

namespace App\Http\Controllers;

use App\Models\ChatbotMensaje;
use App\Models\User;
use App\Services\AIService;
use App\Services\ChatbotContextoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ChatbotMensajeController extends Controller
{
    protected AIService $aiService;
    protected ChatbotContextoService $chatbotContextoService;

    public function __construct(AIService $aiService, ChatbotContextoService $chatbotContextoService)
    {
        $this->aiService = $aiService;
        $this->chatbotContextoService = $chatbotContextoService;
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->only('contenido', 'conversacion_id'), [
            'contenido' => ['required', 'string', 'max:4000'],
            'conversacion_id' => ['nullable', 'integer', 'min:1'],
        ], [
            'contenido.required' => 'Escribe un mensaje antes de enviarlo.',
            'contenido.string' => 'El mensaje debe ser texto.',
            'contenido.max' => 'El mensaje no puede superar los 4000 caracteres.',
            'conversacion_id.integer' => 'No se pudo acceder a esta conversación.',
            'conversacion_id.min' => 'No se pudo acceder a esta conversación.',
        ]);

        $validator->after(function ($validator) use ($request): void {
            $content = $request->input('contenido');

            if (is_string($content) && preg_match('/^\s*$/u', $content) === 1) {
                $validator->errors()->add('contenido', 'Escribe un mensaje antes de enviarlo.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa el mensaje e inténtalo nuevamente.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = (int) $request->session()->get('usuario_id');
        $user = User::find($userId);

        if (! $user) {
            return response()->json([
                'message' => 'Tu sesión venció. Inicia sesión nuevamente.',
            ], 401);
        }

        $validated = $validator->validated();
        $conversationId = $validated['conversacion_id'] ?? null;
        $contenidoUsuario = trim($validated['contenido']);

        // Validar conversación fuera de la transacción para responder 404 rápidamente
        $conversation = null;
        if ($conversationId !== null) {
            $conversation = $user->chatbotConversaciones()
                ->whereKey($conversationId)
                ->first();

            if (! $conversation) {
                return response()->json([
                    'message' => 'No se pudo acceder a esta conversación.',
                ], 404);
            }
        }

        try {
                        // El contexto incluye datos reales del sistema, filtrados según el rol del usuario
            $systemContext = $this->chatbotContextoService->construirPrompt($user, $contenidoUsuario);

            // La caché es POR USUARIO y depende de los datos: así una respuesta con datos de un usuario
            // nunca se le entrega a otro, y se renueva cuando los datos cambian (máx. 5 minutos).
            $preguntaKey = 'chatbot_q_' . $user->getKey() . '_' . md5(mb_strtolower($contenidoUsuario) . '|' . md5($systemContext));

            $respuestaAI = Cache::remember($preguntaKey, now()->addMinutes(5), function () use ($systemContext, $contenidoUsuario) {
                return $this->aiService->sendMessage($systemContext, $contenidoUsuario);
            });

            $result = DB::transaction(function () use ($user, $conversation, $contenidoUsuario, $respuestaAI): array {
                if (! $conversation) {
                    $conversation = $user->chatbotConversaciones()->create();
                }

                // 1. Guardar mensaje del usuario
                $messageUser = $conversation->mensajes()->create([
                    'emisor' => ChatbotMensaje::EMISOR_USUARIO,
                    'contenido' => $contenidoUsuario,
                    'estado' => ChatbotMensaje::ESTADO_COMPLETADO,
                ]);

                // 2. Guardar la respuesta generada por la IA (o recuperada de caché)
                $messageBot = $conversation->mensajes()->create([
                    'emisor' => ChatbotMensaje::EMISOR_ASISTENTE,
                    'contenido' => $respuestaAI,
                    'estado' => ChatbotMensaje::ESTADO_COMPLETADO,
                ]);

                return [
                    'conversacion_id' => $conversation->getKey(),
                    'mensaje_usuario_id' => $messageUser->getKey(),
                    'mensaje_bot_id' => $messageBot->getKey(),
                    'respuesta_bot' => $respuestaAI,
                ];
            });
        } catch (Throwable $exception) {
            Log::error('No se pudo procesar un mensaje de TungaBot con IA.', [
                'exception_class' => $exception::class,
                'message' => $exception->getMessage(),
                'usuario_id' => $userId,
            ]);

            return response()->json([
                'message' => 'No se pudo procesar el mensaje con la IA. Inténtalo nuevamente.',
            ], 500);
        }

        return response()->json([
            'message' => 'Mensaje procesado exitosamente.',
            'data' => [
                'conversacion_id' => $result['conversacion_id'],
                'mensaje_id' => $result['mensaje_usuario_id'],
                'respuesta' => $result['respuesta_bot'],
                'estado' => ChatbotMensaje::ESTADO_COMPLETADO,
            ],
        ], 201);
    }
}