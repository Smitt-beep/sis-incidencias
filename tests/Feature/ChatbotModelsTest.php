<?php

namespace Tests\Feature;

use App\Models\ChatbotConversacion;
use App\Models\ChatbotMensaje;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChatbotModelsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
            'database.connections.sqlite.foreign_key_constraints' => true,
        ]);
        DB::purge('sqlite');

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('dni');
            $table->string('password');
            $table->timestamps();
        });

        $conversationsMigration = require database_path('migrations/2026_11_03_180000_create_chatbot_conversaciones_table.php');
        $conversationsMigration->up();

        $messagesMigration = require database_path('migrations/2026_11_03_180001_create_chatbot_mensajes_table.php');
        $messagesMigration->up();
    }

    public function test_models_resolve_user_conversation_and_message_relationships(): void
    {
        $user = $this->createUser();
        $conversation = $user->chatbotConversaciones()->create();
        $message = $conversation->mensajes()->create([
            'emisor' => ChatbotMensaje::EMISOR_USUARIO,
            'contenido' => 'Consulta de prueba aislada',
            'estado' => ChatbotMensaje::ESTADO_COMPLETADO,
        ]);

        $this->assertSame($user->id, $conversation->usuario->id);
        $this->assertSame($conversation->id, $message->conversacion->id);
        $this->assertSame($message->id, $conversation->mensajes->sole()->id);
    }

    public function test_database_restricts_message_emitter_and_state_values(): void
    {
        $conversation = $this->createUser()->chatbotConversaciones()->create();

        foreach ([
            ['emisor' => 'sistema', 'estado' => ChatbotMensaje::ESTADO_COMPLETADO],
            ['emisor' => ChatbotMensaje::EMISOR_ASISTENTE, 'estado' => 'pendiente'],
        ] as $invalidValues) {
            try {
                $conversation->mensajes()->create([
                    ...$invalidValues,
                    'contenido' => 'Valor inválido de prueba',
                ]);
                $this->fail('Se aceptó un emisor o estado que no está permitido.');
            } catch (QueryException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_user_cannot_be_deleted_while_chatbot_history_exists(): void
    {
        $user = $this->createUser();
        $conversation = $user->chatbotConversaciones()->create();
        $conversation->mensajes()->create([
            'emisor' => ChatbotMensaje::EMISOR_ASISTENTE,
            'contenido' => 'Respuesta de prueba aislada',
            'estado' => ChatbotMensaje::ESTADO_COMPLETADO,
        ]);

        $this->expectException(QueryException::class);

        $user->delete();
    }

    public function test_conversation_cannot_be_deleted_while_messages_exist(): void
    {
        $conversation = $this->createUser()->chatbotConversaciones()->create();
        $conversation->mensajes()->create([
            'emisor' => ChatbotMensaje::EMISOR_USUARIO,
            'contenido' => 'Consulta de prueba aislada',
            'estado' => ChatbotMensaje::ESTADO_COMPLETADO,
        ]);

        $this->expectException(QueryException::class);

        $conversation->delete();
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'Usuario de prueba',
            'email' => uniqid('chatbot-test-', true).'@example.test',
            'dni' => str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
            'password' => 'isolated-test-value',
        ]);
    }
}
