<?php
declare (strict_types = 1);

use PHPUnit\Framework\TestCase;
use Scaleum\Storages\PDO\Database;

class QueryBuilderTest extends TestCase {
    private Database $database;
    protected function setUp(): void {
        $file           = __DIR__ . '/test.sqlite';
        $this->database = new Database([
            // 'dsn' => 'sqlite:' . $file,
            'dsn'      => 'mysql:host=localhost;dbname=test',
            'user'     => 'root',
            'password' => '',

            // 'dsn'               => 'pgsql:host=localhost;dbname=test;port=5432',
            // 'user'              => 'postgres',
            // 'password'          => '12345678',
            
        ]);
    }

    public function testSelect(): void {
        $query = $this->database->getQueryBuilder();
        $sql   = $query
            ->prepare(true)
            ->optimize(true)
            ->select(['users.*', 'posts.title'])
            ->joinLeft('posts', 'posts.user_id = users.id')
            ->from('users')
            ->where('id', 1, false)
            ->rows();
        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Build SELECT', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, $sql);
        fwrite(STDOUT, "\n");

        // $this->assertEquals("SELECT `users`.*, `posts`.`title` FROM (`users`) LEFT JOIN `posts` ON `posts`.`user_id` = `users`.`id` WHERE id = 1", $sql);
    }

    public function testSelectWithLike(): void {
        $query = $this->database->getQueryBuilder();
        $sql   = $query
            ->prepare(true)
            // ->optimize(false)
            ->select(['users.*'])
            ->from('users')
            ->where('id', 1, false)
            ->like('name', 'John')
            ->offset(1)
            ->limit(10)
            ->rows();
        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Build SELECT(with LIKE)', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, $sql);
        fwrite(STDOUT, "\n");
    }

    public function testInsert(): void {
        $query = $this->database->getQueryBuilder();
        $sql   = $query
            ->prepare(true)
            ->optimize(true)
            ->insert('users', ['name' => 'John', 'email' => '', 'password' => '']);
        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Build INSERT', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, $sql);
        fwrite(STDOUT, "\n");
    }

    public function testInsertBatch(): void {
        $query = $this->database->getQueryBuilder();
        $sql   = $query
            ->prepare(true)
            ->optimize(true)
            ->insert('users', [
                ['name' => 'John', 'email' => '', 'password' => '1'],
                ['name' => 'Doe', 'email' => '', 'password' => '2'],
                ['name' => 'Jane', 'email' => '', 'password' => '3'],
            ]);
        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Build INSERT(as batch)', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, $sql);
        fwrite(STDOUT, "\n");
    }
    public function testInsertViaSet(): void {
        $query = $this->database->getQueryBuilder();
        $sql   = $query
            ->prepare(true)
            ->optimize(true)
            ->set(['name' => 'John', 'email' => '', 'password' => ''])
            ->insert('users');

        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Build INSERT(via set)', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, $sql);
        fwrite(STDOUT, "\n");
    }
    public function testInsertViaSetAsBatch(): void {
        $query = $this->database->getQueryBuilder();
        $sql   = $query
            ->prepare(true)
            ->optimize(true)
            ->setAsBatch([
                ['name' => 'John', 'email' => '', 'password' => ''],
                ['name' => 'Doe', 'email' => '', 'password' => ''],
                ['name' => 'Jane', 'email' => '', 'password' => ''],
            ])
            ->insert('users');

        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Build INSERT(as batch via set)', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, $sql);
        fwrite(STDOUT, "\n");
    }

    public function testUpdate(): void {
        $query = $this->database->getQueryBuilder();
        $sql   = $query
            ->prepare(true)
            ->optimize(true)
            ->where('id', 1, false)
            ->update('users', ['name' => 'John', 'email' => '', 'password' => '']);

        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Build UPDATE', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, $sql);
        fwrite(STDOUT, "\n");
    }

    public function testUpdateViaSet(): void {
        $query = $this->database->getQueryBuilder();
        $sql   = $query
            ->prepare(true)
            ->optimize(true)
            ->where('id', 1, false)
            ->set(['name' => 'John', 'email' => '', 'password' => ''])
            ->update('users');

        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Build UPDATE(via set)', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, $sql);
        fwrite(STDOUT, "\n");
    }

    public function testUpdateViaSetAsBatch(): void {
        $query = $this->database->getQueryBuilder();
        $sql   = $query
            ->prepare(true)
            // ->optimize(false)
            ->where('id', 1, false)
            ->whereKey('name')
            ->setAsBatch([
                ['name' => 'John', 'email' => '', 'password' => 1],
                ['name' => 'Doe', 'email' => '', 'password' => '2'],
                ['name' => 'Jane', 'email' => '', 'password' => 3],
            ])
            ->update('users');

        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Build UPDATE(as batch via set)', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, $sql);
        fwrite(STDOUT, "\n");
    }

    public function testDelete(): void {
        $query = $this->database->getQueryBuilder();
        $sql   = $query
            ->prepare(true)
            ->optimize(true)
            ->like('name', 'Jo', 'after')
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->delete('users');

        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Build DELETE', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, $sql);
        fwrite(STDOUT, "\n");
    }

    public function testDeleteMulti(): void {
        $query = $this->database->getQueryBuilder();
        $sql   = $query
            ->prepare(true)
            ->optimize(true)
            ->where('user_id', 1, false)
            ->delete(['users', 'posts']);

        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad('Build DELETE(multi-tables)', 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, $sql);
        fwrite(STDOUT, "\n");
    }
}