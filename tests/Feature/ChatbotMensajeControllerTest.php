<?php

namespace Tests\Feature;

use App\Models\ChatbotMensaje;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ChatbotMensajeControllerTest extends TestCase
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
            $table->string('estado_aprobacion')->default('Aprobado');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        $conversationsMigration = require database_path('migrations/2026_11_03_180000_create_chatbot_conversaciones_table.php');
        $conversationsMigration->up();

        $messagesMigration = require database_path('migrations/2026_11_03_180001_create_chatbot_mensajes_table.php');
        $messagesMigration->up();
    }

    public function test_request_without_validated_session_is_rejected(): void
    {
        $this->postJson(route('chatbot.mensajes.store'), [
            'contenido' => 'Mensaje sin sesión',
        ])->assertUnauthorized();

        $this->assertDatabaseCount('chatbot_conversaciones', 0);
        $this->assertDatabaseCount('chatbot_mensajes', 0);
    }

    public function test_first_message_creates_owned_conversation_and_does_not_store_assistant_notice(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();

        $response = $this->withSession(['usuario_id' => $user->id])
            ->postJson(route('chatbot.mensajes.store'), [
                'contenido' => '  Consulta de prueba  ',
                'usuario_id' => $otherUser->id,
                'emisor' => ChatbotMensaje::EMISOR_ASISTENTE,
                'estado' => ChatbotMensaje::ESTADO_ERROR,
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Mensaje guardado.')
            ->assertJsonPath('data.estado', ChatbotMensaje::ESTADO_COMPLETADO);

        $conversationId = $response->json('data.conversacion_id');
        $this->assertDatabaseHas('chatbot_conversaciones', [
            'id' => $conversationId,
            'usuario_id' => $user->id,
        ]);
        $this->assertDatabaseHas('chatbot_mensajes', [
            'conversacion_id' => $conversationId,
            'emisor' => ChatbotMensaje::EMISOR_USUARIO,
            'contenido' => 'Consulta de prueba',
            'estado' => ChatbotMensaje::ESTADO_COMPLETADO,
        ]);
        $this->assertDatabaseCount('chatbot_conversaciones', 1);
        $this->assertDatabaseCount('chatbot_mensajes', 1);
    }

    public function test_second_message_is_saved_to_the_same_conversation(): void
    {
        $user = $this->createUser();
        $conversation = $user->chatbotConversaciones()->create();

        $this->withSession(['usuario_id' => $user->id])
            ->postJson(route('chatbot.mensajes.store'), [
                'contenido' => 'Segundo mensaje',
                'conversacion_id' => $conversation->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.conversacion_id', $conversation->id);

        $this->assertDatabaseCount('chatbot_conversaciones', 1);
        $this->assertDatabaseCount('chatbot_mensajes', 1);
        $this->assertSame(
            ChatbotMensaje::EMISOR_USUARIO,
            $conversation->mensajes()->sole()->emisor
        );
    }

    public function test_message_cannot_be_added_to_another_users_conversation(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $foreignConversation = $otherUser->chatbotConversaciones()->create();

        $this->withSession(['usuario_id' => $user->id])
            ->postJson(route('chatbot.mensajes.store'), [
                'contenido' => 'No guardar',
                'conversacion_id' => $foreignConversation->id,
            ])
            ->assertNotFound()
            ->assertExactJson([
                'message' => 'No se pudo acceder a esta conversación.',
            ]);

        $this->assertDatabaseCount('chatbot_mensajes', 0);
    }

    public function test_empty_whitespace_and_overlong_content_are_rejected(): void
    {
        $user = $this->createUser();

        foreach (['', " \t\n", str_repeat('a', 4001)] as $content) {
            $this->withSession(['usuario_id' => $user->id])
                ->postJson(route('chatbot.mensajes.store'), [
                    'contenido' => $content,
                ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('contenido');
        }

        $this->assertDatabaseCount('chatbot_conversaciones', 0);
        $this->assertDatabaseCount('chatbot_mensajes', 0);
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'Usuario de prueba',
            'email' => uniqid('chatbot-message-test-', true).'@example.test',
            'dni' => str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
            'password' => 'isolated-test-value',
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);
    }
}
