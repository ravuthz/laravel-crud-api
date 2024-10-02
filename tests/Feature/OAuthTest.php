<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Tests\TestCase;

class OAuthTest extends TestCase
{
    use WithFaker;

    protected string $password = '123123';

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');

        $url = config('app.url');
        $service = new ClientRepository();
        $service->createPersonalAccessClient(null, 'Test Personal Token', $url);
        $service->createPasswordGrantClient(null, 'Test Password Token', $url);
    }

    public function test_can_access_protected_route_with_token()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertSuccessful();
        $response->assertJsonPath('email', $user->email);
    }

    public function test_can_request_token()
    {
        [$response, $client] = $this->requestToken();

        $response->assertSuccessful();
        $response->assertJsonStructure(['token_type', 'expires_in', 'access_token', 'refresh_token']);
    }

    public function test_can_refresh_token()
    {
        [$token, $client] = $this->requestToken();

        $response = $this->postJson('/oauth/token', [
            'grant_type' => 'refresh_token',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'refresh_token' => $token->json('refresh_token'),
            'scope' => ''
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure(['token_type', 'expires_in', 'access_token', 'refresh_token']);
    }

    public function requestToken(): array
    {
        $user = User::factory()->create(['password' => $this->password]);

        $repository = new ClientRepository();
        $client = $repository->createPasswordGrantClient(null, 'Test Password Token', config('app.url'));

        $token = $this->postJson('/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $user->email,
            'password' => $this->password,
            'scope' => ''
        ]);

        return [$token, $client, $user];
    }

}
