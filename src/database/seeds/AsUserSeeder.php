<?php


use Onlyoung4u\AsApi\Helpers\Bcrypt;
use Phinx\Seed\AbstractSeed;

class AsUserSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run()
    {
        $client = new Bcrypt();

        $data = [
            [
                'username' => 'admin',
                'nickname' => '超级管理员',
                'created_by' => 0,
                'password' => $client->make('12345678'),
            ]
        ];

        $this->table('as_users')->insert($data)->save();
    }
}
