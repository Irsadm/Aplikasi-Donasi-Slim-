<?php

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
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
        $faker = Faker\Factory::create();
        $data = [];
        for ($i = 0; $i < 20; $i++) {
            $data[] = [
                'name'  => $faker->name,
                'password' => password_hash($faker->password, PASSWORD_DEFAULT),
                'email'    => $faker->email,
                // 'lokasi_id' => $faker->randomDigit,
                'phone'     => $faker->phoneNumber,
                'biografi'  => $faker->word,
                'status'    => 0,
                'foto_profil' => $faker->url
                ] ;
        }
        $this->insert('users', $data);
    }
}
