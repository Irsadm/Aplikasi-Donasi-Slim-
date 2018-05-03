<?php

use Phinx\Seed\AbstractSeed;

class UserSeeder2 extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $data = [
            [
                'name' => 'hamdan',
                'email' => 'hamdan@mail.com',
                'password' => password_hash('hamdan123', PASSWORD_BCRYPT)
            ],
            [
                'name' => 'hasan',
                'email' => 'hasan@mail.com',
                'password' => password_hash('hasan123', PASSWORD_BCRYPT)
            ],
            [
                'name' => 'haryono',
                'email' => 'haryono@mail.com',
                'password' => password_hash('haryono123', PASSWORD_BCRYPT)
            ],
            [
                'name' => 'halwan',
                'email' => 'halwan@mail.com',
                'password' => password_hash('halwan123', PASSWORD_BCRYPT)
            ],

        ];

        $this->insert('users', $data);

    }
}
