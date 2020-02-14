<?php

class User
{
    private $conn;
    private $table = 'users';

    public $id;
    public $first_name;
    public $last_name;
    public $emails;
    public $phone_numbers;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = 'INSERT INTO ' . $this->table . ' ' .
            '(first_name, last_name, created_at, updated_at) ' .
            'VALUES (?, ?, NOW(), NOW())';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->first_name);
        $stmt->bindParam(2, $this->last_name);

        $stmt->execute();
        $this->id = $this->conn->lastInsertId();

        if (!is_null($this->emails)) {
            $this->insertIntoChildTable('user_emails', 'email', $this->emails);
        }

        if (!is_null($this->phone_numbers)) {
            $this->insertIntoChildTable('user_phone_numbers', 'phone_number', $this->phone_numbers);
        }

        return 'A new user was created';
    }

    private function insertIntoChildTable($tableName, $columnName, $values)
    {
        $query = 'INSERT INTO ' . $tableName .
            ' (user_id, ' . $columnName . ', created_at, updated_at) VALUES (?, ?, NOW(), NOW())';
        foreach ($values as $value) {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->bindParam(2, $value);
            $stmt->execute();
        }
    }

    public function delete()
    {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $stmt->rowCount() == 0 ? $msg = 'No record found with id: ' . $this->id : null;

        return $msg ?? $stmt->rowCount() .
            ' user got deleted (id: ' . $this->id . ') along side the emails and phone numbers associated';
    }

    public function get()
    {
        $query = 'SELECT ' .
            'u.id, u.first_name, u.last_name, u.created_at, u.updated_at,' .
            'GROUP_CONCAT(DISTINCT ue.email SEPARATOR ", ") as emails, ' .
            'GROUP_CONCAT(DISTINCT ue.email SEPARATOR ", ") as emails, ' .
            'GROUP_CONCAT(DISTINCT upn.phone_number SEPARATOR ", ") as phone_numbers ' .
            'FROM ' . $this->table . ' AS u ' .
            'LEFT JOIN user_emails as ue ON u.id = ue.user_id ' .
            'LEFT JOIN user_phone_numbers AS upn ON u.id = upn.user_id ' . $this->resolveFilter();

        $stmt = $this->conn->prepare($query);

        $bindValue = $this->resolveBind();
        if (!is_null($bindValue)) {
            $stmt->bindParam(1, $bindValue);
        }
        $stmt->execute();

        $resp = array();
        while ($outPutData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resp['data'][] = [
                'id' => $outPutData['id'],
                'first_name' => $outPutData['first_name'],
                'last_name' => $outPutData['last_name'],
                'emails' => $outPutData['emails'],
                'phone_numbers' => $outPutData['phone_numbers'],
                'created_at' => $outPutData['created_at'],
                'updated_at' => $outPutData['updated_at'],
            ];
        }

        return $resp;
    }

    private function resolveFilter()
    {
        $group = 'GROUP BY u.id';
        if ($this->isFieldSet($this->id)) {
            return 'WHERE u.id = ? LIMIT 1 ';
        }
        if ($this->isFieldSet($this->first_name)) {
            return 'WHERE u.first_name = ? ';
        }
        if ($this->isFieldSet($this->last_name)) {
            return 'WHERE u.last_name = ? ';
        }
        if ($this->isFieldSet($this->emails)) {
            return 'WHERE ue.email = ? GROUP BY u.id';
        }
        if ($this->isFieldSet($this->phone_numbers)) {
            return 'WHERE upn.phone_number = ?  GROUP BY u.id';
        }

        return $group;
    }

    private function resolveBind()
    {
        if ($this->isFieldSet($this->id)) {
            return $this->id;
        }
        if ($this->isFieldSet($this->first_name)) {
            return $this->first_name;
        }
        if ($this->isFieldSet($this->last_name)) {
            return $this->last_name;
        }
        if ($this->isFieldSet($this->emails)) {
            return $this->emails;
        }
        if ($this->isFieldSet($this->phone_numbers)) {
            return $this->phone_numbers;
        }
        return null;
    }

    private function isFieldSet($field)
    {
        return !is_null($field) && !empty($field);
    }

    public function update()
    {
        $query = 'UPDATE ' . $this->table . ' SET ' .
            '(first_name, last_name, created_at, updated_at) ' .
            'values (?, ?, NOW(), NOW())';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->first_name);
        $stmt->bindParam(2, $this->last_name);
        $stmt->execute();

        return $stmt->rowCount() . ' user updated';
    }
}
