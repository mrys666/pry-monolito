<?php

use App\Models\User;

test('la pagina de inicio muestra la propuesta de valor', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Tu inventario')
        ->assertSee('Comenzar gratis')
        ->assertSee('Catálogo centralizado')
        ->assertSee('Administrador');
});

test('la pagina de inicio invita al panel a los usuarios autenticados', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk()
        ->assertSee('Ir al panel')
        ->assertDontSee('Comenzar gratis');
});
