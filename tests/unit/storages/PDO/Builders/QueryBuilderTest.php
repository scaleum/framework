<?php
declare (strict_types = 1);

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Scaleum\Storages\PDO\Database;

class QueryBuilderTest extends TestCase {
    private Database $database;

    protected function setUp(): void {
        /** @var MockObject&Database $database */
        $database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPDO', 'getPDODriverName'])
            ->getMock();

        $database->method('getPDO')->willReturn(new \PDO('sqlite::memory:'));
        $database->method('getPDODriverName')->willReturn('mysql');

        $this->database = $database;
    }

    private function assertSqlEqualsNormalized(string $expected, string $actual): void {
        $normalize = static function (string $sql): string {
            $sql = trim((string) preg_replace('/\s+/', ' ', $sql));
            $sql = (string) preg_replace('/\s+\)/', ')', $sql);
            return $sql;
        };
        $this->assertEquals($normalize($expected), $normalize($actual));
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

    public function testWithCte(): void {
        $query      = $this->database->getQueryBuilder();
        $cteBuilder = $this->database->getQueryBuilder()
            ->prepare(true)
            ->select(['id', 'name'])
            ->from('users')
            ->where('active', 1);
        $cteSql = $cteBuilder->rows();
        $sql    = $query
            ->prepare(true)
            ->with('active_users', $cteSql, ['id', 'name'])
            ->select(['au.id', 'au.name'])
            ->from('active_users au')
            ->rows();
        $expected = "WITH `active_users` (`id`, `name`) AS (SELECT `id`, `name`\nFROM `users`\nWHERE `active` = 1 )\nSELECT `au`.`id`, `au`.`name`\nFROM `active_users` au";
        $this->assertSqlEqualsNormalized($expected, $sql);
    }

    public function testWithRecursiveCte(): void {
        $query   = $this->database->getQueryBuilder();
        $baseCte = $this->database->getQueryBuilder()
            ->prepare(true)
            ->select(['id', 'parent_id', 'name'])
            ->from('categories')
            ->where('parent_id', null);
        $recursiveCte = $this->database->getQueryBuilder()
            ->prepare(true)
            ->select(['c.id', 'c.parent_id', 'c.name'])
            ->from('categories c')
            ->joinInner('category_tree ct', 'c.parent_id = ct.id');
        $cteSql = $baseCte->rows() . "\nUNION ALL\n" . $recursiveCte->rows();
        $sql    = $query
            ->prepare(true)
            ->withRecursive('category_tree', $cteSql, ['id', 'parent_id', 'name'])
            ->select(['ct.id', 'ct.name'])
            ->from('category_tree ct')
            ->rows();
        $expected = "WITH RECURSIVE `category_tree` (`id`, `parent_id`, `name`) AS (SELECT `id`, `parent_id`, `name`\nFROM `categories`\nWHERE `parent_id` IS NULL\nUNION ALL\nSELECT `c`.`id`, `c`.`parent_id`, `c`.`name`\nFROM `categories` c\nINNER JOIN `category_tree` ct ON `c`.`parent_id` = `ct`.`id`)\nSELECT `ct`.`id`, `ct`.`name`\nFROM `category_tree` ct";
        $this->assertSqlEqualsNormalized($expected, $sql);
    }

    public function testUnion(): void {
        $query = $this->database->getQueryBuilder();
        $sql   = $query
            ->prepare(true)
            ->select(['id', 'name'])
            ->from('users')
            ->where('active', 1)
            ->union(function ($q) {
                $q->prepare(true)
                    ->select(['id', 'name'])
                    ->from('admins')
                    ->where('active', 1);
            })
            ->rows();
        $expected = "SELECT `id`, `name`\nFROM `users`\nWHERE `active` = 1\n UNION SELECT `id`, `name`\nFROM `admins`\nWHERE `active` = 1";
        $this->assertSqlEqualsNormalized($expected, $sql);
    }

    public function testUnionAll(): void {
        $query = $this->database->getQueryBuilder();
        $sql   = $query
            ->prepare(true)
            ->select(['id', 'name'])
            ->from('users')
            ->where('active', 1)
            ->unionAll(function ($q) {
                $q->prepare(true)
                    ->select(['id', 'name'])
                    ->from('admins')
                    ->where('active', 1);
            })
            ->rows();
        $expected = "SELECT `id`, `name`\nFROM `users`\nWHERE `active` = 1\n UNION ALL SELECT `id`, `name`\nFROM `admins`\nWHERE `active` = 1";
        $this->assertSqlEqualsNormalized($expected, $sql);
    }
}