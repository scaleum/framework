<?php
declare (strict_types = 1);

use PHPUnit\Framework\TestCase;
use Scaleum\Storages\PDO\Database;
use Scaleum\Storages\PDO\ModelAbstract;

class ModelTest extends TestCase {
    private Database $database;

    protected function setUp(): void {
        $file           = __DIR__ . '/test.sqlite';
        $this->database = new Database([
            // 'dsn'               => 'sqlite:' . $file,
            'dsn'               => 'mysql:host=localhost;dbname=test',
            'user'              => 'root',
            'password'          => '',

            // 'dsn'               => 'pgsql:host=localhost;dbname=test;port=5432',
            // 'user'              => 'postgres',
            // 'password'          => '12345678',

            'multiple_commands' => true,
        ]);

        $schema = $this->database->getSchemaBuilder();
        $schema
            ->prepare(value: false)
            ->optimize(false)
            ->addColumn([
                $schema->columnPrimaryKey(11)->setColumn('id'),
                $schema->columnString(255)->setColumn('name')->setNotNull(),
                $schema->columnString(255)->setColumn('email')->setNotNull(),
                $schema->columnTimestamp()->setColumn('created_at')->setNotNull()->setDefaultValue('CURRENT_TIMESTAMP', FALSE),
            ])
            // ->addIndex($schema->indexUnique(['name'], 'key_name'))
            ->createTable('users', true);

        // $this->printSection("Create table: users", (string) $this->database->getQuery());

        $schema = $this->database->getSchemaBuilder();
        $schema
            ->prepare(value: false)
            ->optimize(false)
            ->addColumn([
                $schema->columnPrimaryKey(11)->setColumn('id'),
                $schema->columnInt(11)->setColumn('user_id')->setNotNull(),
                $schema->columnString(255)->setColumn('city')->setNotNull(),
                $schema->columnString(255)->setColumn('bio')->setNotNull(),
            ])
            ->addIndex($schema->indexForeign('user_id', 'fk_users_profile')->reference('users', 'id', 'CASCADE'))
            ->createTable('profiles', true);

        // $this->printSection("Create table: profiles", (string) $this->database->getQuery());

        $schema = $this->database->getSchemaBuilder();
        $schema
            ->prepare(value: false)
            ->optimize(false)
            ->addColumn([
                $schema->columnPrimaryKey(11)->setColumn('id'),
                $schema->columnInt(11)->setColumn('user_id')->setNotNull(),
                $schema->columnString(255)->setColumn('text')->setNotNull(),
                $schema->columnTimestamp()->setColumn('created_at')->setNotNull()->setDefaultValue('CURRENT_TIMESTAMP', FALSE),
            ])
            ->addIndex($schema->indexForeign('user_id', 'fk_users_comments')->reference('users', 'id', 'CASCADE'))
            ->createTable('comments', true);

        // $this->printSection("Create table: comments", (string) $this->database->getQuery());

        // $query = $this->database->getQueryBuilder();
        // $query
        //     ->prepare(false)
        //     ->optimize(true)
        //     ->insert('users', [
        //         ['name' => 'Alice', 'email' => 'alice@example.com'],
        //         ['name' => 'Bob', 'email' => 'bob@example.com'],
        //     ]);

        // $query
        //     ->prepare(false)
        //     ->optimize(true)
        //     ->insert('profiles', [
        //         ['user_id' => 1, 'city' => 'New York', 'bio' => 'Software Engineer'],
        //         ['user_id' => 2, 'city' => 'Los Angeles', 'bio' => 'Graphic Designer'],
        //     ]);
        // $query
        //     ->prepare(false)
        //     ->optimize(true)
        //     ->insert('comments', [
        //         ['user_id' => 1, 'text' => 'Hello, world'],
        //         ['user_id' => 1, 'text' => 'Hello, Alice'],
        //         ['user_id' => 2, 'text' => 'Hello, Bob'],
        //     ]);               
    }

    protected function printSection(string $title, string $sql): void {
        fwrite(STDOUT, "\n");
        fwrite(STDOUT, str_pad($title, 76, '-', STR_PAD_BOTH) . "\n");
        fwrite(STDOUT, (string) $sql);
        fwrite(STDOUT, "\n");
    }

    protected function printLine(string $line){
        fwrite(STDOUT, $line . "\n");        
    }

    public function testUserModelInsert(): void{
        $user = new UserModel($this->database);
        $formData = [
            'id' => 1,
            'name' => 'John',
            'email' => 'john@example.com',
            'profile' => [
                // 'id' => 1,
                'city' => 'Toronto',
                'bio' => 'Web Developer'
            ],
            'comments' => [
                ['text' => 'Первый комментарий'],
                ['text' => 'Второй комментарий']
            ]
        ];

        $user->load($formData)->insert();
        $this->printLine("\n");
        $this->printLine("UserID: " . $user->id);
        $this->printLine("Имя: " . $user->name);
        $this->printLine("Email: " . $user->email);
        $this->printLine("Дата создания: " . $user->created_at);
        $this->printLine("Город: " . $user->profile->city);
        $this->printLine("Биография: " . $user->profile->bio);
        $this->printLine("Комментарии:");
        foreach ($user->comments as $comment) {
            $this->printLine(" - " . $comment->text);
        }        
    }

    public function testUserModelFind(): void {
        $user = (new UserModel($this->database))->find(1);
        // $this->printSection("users->find(id)", (string) $this->database->getQuery());

        $this->printLine("\n");
        $this->printLine("UserID: " . $user->id);
        $this->printLine("Имя: " . $user->name);
        $this->printLine("Email: " . $user->email);
        $this->printLine("Дата создания: " . $user->created_at);
        $this->printLine("Город: " . $user->profile->city);
        $this->printLine("Биография: " . $user->profile->bio);
        $this->printLine("Комментарии:");
        foreach ($user->comments as $comment) {
            $this->printLine(" - " . $comment->text);
        }
    }

    public function testUserModelUpdate(){
        $user = (new UserModel($this->database))->find(1);
        if ($user) {
            $updateData = [
                'name' => 'Alice Smith',
                'profile' => ['city' => 'Paris', 'bio' => 'CIO & CTO','id' => $user->profile->id],
                'comments' => [
                    ['text' => 'Comment 1', 'id' => $user->comments[0]->id],
                    ['text' => 'Comment 2', 'id' => $user->comments[1]->id],
                    // ['text' => 'Comment 2', 'id' => 123],
                ]
            ];
        
            $result = $user->load($updateData)->update();
            $this->printLine("\n---");
            $this->printLine("Имя: " . $user->name);
            $this->printLine("Email: " . $user->email);
            $this->printLine("Дата создания: " . $user->created_at);
            $this->printLine("Город: " . $user->profile->city);
            $this->printLine("Биография: " . $user->profile->bio);

            // var_export($result);
        }                        
    }

    public function testUserModelDelete(){
        $user = (new UserModel($this->database))->find(1);
        if ($user) {
            // $user->delete(true);
        }                        
    }    
}

class UserModel extends ModelAbstract {
    protected ?string $table     = 'users';
    protected string $primaryKey = 'id';

    protected function getRelations(): array {
        return [
            'profile' => [
                'model'       => ProfileModel::class,
                'method'      => 'findByUserId',
                'primary_key' => 'id',
                'foreign_key' => 'user_id',
                'type'        => 'hasOne',
            ],
            'comments' => [
                'model'       => CommentModel::class,
                'method'      => 'findByUserId',
                'primary_key' => 'id',
                'foreign_key' => 'user_id',
                'type'        => 'hasMany',
            ],
        ];
    }
}

class ProfileModel extends ModelAbstract {
    protected ?string $table     = 'profiles';
    protected string $primaryKey = 'id';

    public function findByUserId(mixed $userId): ?self {
        return $this->findOneBy(['user_id' => $userId]);
    }
}

class CommentModel extends ModelAbstract {
    protected ?string $table     = 'comments';
    protected string $primaryKey = 'id';

    public function findByUserId(mixed $userId): array {
        return $this->findAllBy(['user_id' => $userId]);
    }
}