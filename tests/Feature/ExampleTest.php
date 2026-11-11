<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A raiz do site redireciona para o login do painel.
     */
    public function test_a_raiz_redireciona_para_o_login_do_painel(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/admin/login');
    }
}
