<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class CustomerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    
    public function testCustomer()
    {
        // Use $this->faker here if needed
        $response = $this->post('/api/signin', [
            'email' => '',
            'password' => 'password', // Assuming a password is also required
        ]);

        $data = json_decode($response->response->getContent());
        $status = $data->success;
        $message = $data->message;

        // Assertions
        $this->assertEquals(false, $status, 'Expected success to be false');
        $this->assertEquals('Validation errors', $message, 'Expected message to be "Validation errors"');
    }
}
