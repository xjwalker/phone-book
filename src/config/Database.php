<?php

class Database
{
    // TODO; Update this I don't like hard-coded values.
    private $host = 'api_database';
    private $db = 'phone_book';
    private $user = 'root';
    private $password = 1234;
    private $conn;

    public function getConnection()
    {
        $this->conn = null;
        $dataSourceName = 'mysql:host=' . $this->host . ';dbname=' . $this->db;
        try {
            $this->conn = new PDO($dataSourceName, $this->user, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage(). ' ->' . $e->getTraceAsString();
        }

        return $this->conn;
    }
}

