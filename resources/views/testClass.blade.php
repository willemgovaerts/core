<?= "<?php" ?>

namespace Tests\Feature\Http{{ str_replace('/', '\\', $temp['classpath']) }};

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Domain\User\User;


class {{ $temp['name'] }} extends TestCase
{
@if($temp['method'] == 'post')

    public function testWithoutLogin()
    {
    $sentData = [];
    $response = $this->postJson(route('{{$temp['routeName']}}'), $sentData);
    $response
    ->assertStatus(401);
    }

    public function testNoDataSend()
    {
    $user = User::query()->findOrFail(1);
    $response = $this
    ->actingAs($user, 'api')
    ->postJson(route('{{$temp['routeName']}}'), []);

    $response
    ->assertStatus(422);
    }

    public function testDataSend()
    {
    $sentData = [];
    $receivedData = [];

    $user = User::query()->findOrFail(1);
    $response = $this
    ->actingAs($user, 'api')
    ->postJson(route('{{$temp['routeName']}}'), $sentData);

    $response
    ->assertStatus(200)
    ->assertJson(['data' => $receivedData]);
    }
@else

    public function testWithoutLogin()
    {
    $response = $this->getJson(route('{{$temp['routeName']}}'));
    $response
    ->assertStatus(401);
    }

    public function testWithLogin()
    {
    $user = User::query()->findOrFail(1);
    $response = $this->actingAs($user,'api')->getJson(route('{{$temp['routeName']}}'));
    $response
    ->assertStatus(200)
    ->assertJsonStructure(['data' => []]);
    }
@endif

}