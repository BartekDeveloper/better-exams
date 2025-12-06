<?php

class User {
    public int $id;
    public string $username;
    public string $password;
    public string $email;
    public string $role;

    function __construct(
        int    $_id,
        string $_username,
        string $_password,
        string $_email,
        string $_role
    ) {
        $this->id       = $_id;
        $this->username = $_username;
        $this->password = $_password;
        $this->email    = $_email;
        $this->role     = $_role;
    }
    
    function Get(): string {
        return "SELECT * FROM users WHERE id = " . $this->id;
    }

    function Set(): string {
        return "INSERT INTO users ("
            . "username, password, email, role)"
            . "VALUES ("
            . "'" . $this->username . "',"
            . "'" . $this->password . "',"
            . "'" . $this->email . "',"
            . "'" . $this->role . "')";
    }

    static function Generate(): string {
        return "CREATE TABLE IF NOT EXISTS users ("
            . "id INT PRIMARY KEY AUTO_INCREMENT,"
            . "username VARCHAR(255) NOT NULL,"
            . "password VARCHAR(255) NOT NULL,"
            . "email VARCHAR(255) NOT NULL,"
            . "role VARCHAR(255) NOT NULL"
            . ")";
    }
};

class Category {
    public int $id;
    public string $name;
    public string $qualification;

    function __construct(int $id, string $name, string $qualification) {
        $this->id = $id;
        $this->name = $name;
        $this->qualification = $qualification;
    }

    static function Generate(): string {
        return "CREATE TABLE IF NOT EXISTS categories ("
            . "id INT PRIMARY KEY,"
            . "name VARCHAR(255) NOT NULL,"
            . "qualification ENUM('INF.02', 'INF.03', 'INF.04') NOT NULL"
            . ")";
    }
}

class Question {
    public int $id;
    public int $question_id;
    public string $question;
    public string $a;
    public string $b;
    public string $c;
    public string $d;
    public string $correct;
    public string $image;
    public string $image_fallback;
    public int $category_id;

    function __construct(
        int $id, int $question_id, string $question, string $a, string $b, string $c, string $d, 
        string $correct, string $image, string $image_fallback, int $category_id
    ) {
        $this->id = $id;
        $this->question_id = $question_id;
        $this->question = $question;
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
        $this->d = $d;
        $this->correct = $correct;
        $this->image = $image;
        $this->image_fallback = $image_fallback;
        $this->category_id = $category_id;
    }

    function Get(): string {
        return "SELECT * FROM questions WHERE id = " . $this->id;
    }

    function Set(): string {
        return "INSERT INTO questions ("
            . "question_id, question, a, b, c, d, correct, image, image_fallback, category_id)"
            . "VALUES ("
            . "" . $this->question_id . ","
            . "'" . $this->question . "',"
            . "'" . $this->a . "',"
            . "'" . $this->b . "',"
            . "'" . $this->c . "',"
            . "'" . $this->d . "',"
            . "'" . $this->correct . "',"
            . "'" . $this->image . "',"
            . "'" . $this->image_fallback . "',"
            . "" . $this->category_id . ")";
    }

    static function Generate(): string {
        return "CREATE TABLE IF NOT EXISTS questions ("
            . "id INT PRIMARY KEY AUTO_INCREMENT,"
            . "question_id INT,"
            . "question VARCHAR(1024) NOT NULL,"
            . "a VARCHAR(255) NOT NULL,"
            . "b VARCHAR(255) NOT NULL,"
            . "c VARCHAR(255) NOT NULL,"
            . "d VARCHAR(255) NOT NULL,"
            . "correct VARCHAR(255) NOT NULL,"
            . "image VARCHAR(2048) NOT NULL,"
            . "image_fallback VARCHAR(2048) NOT NULL,"
            . "category_id INT,"
            . "FOREIGN KEY (category_id) REFERENCES categories(id)"
            . ")";
    }
};

function GenerateSQL_Code(): string {
   (string)$sql = "";
   
   $sql .= User::Generate();
   $sql .= ";\n\n";
   $sql .= Category::Generate();
   $sql .= ";\n\n";
   $sql .= Question::Generate();
   $sql .= ";\n\n";

   return $sql;
};

function GenerateSQL_File(): void {
    $sql = GenerateSQL_Code();

    (string)$path = "./sql/tables.sql";

    if(!file_exists("./sql")) {
        mkdir("./sql", 0777, true);
    }

    if(!file_exists("./sql/tables.sql")) {
        $file = fopen("./sql/tables.sql", "w+", false);
        
        fwrite($file, $sql);
        
        fclose($file);
    }
}