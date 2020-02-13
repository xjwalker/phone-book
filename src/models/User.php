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
        // todo; add support to insert emails and phone numbers
        $query = 'INSERT INTO ' . $this->table . ' ' .
            '(first_name, last_name, created_at, updated_at) ' .
            'values (?, ?, NOW(), NOW())';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->first_name);
        $stmt->bindParam(2, $this->last_name);
        $stmt->execute();

        return $stmt->rowCount() . ' new user was created';
    }

    public function get()
    {
        // TODO; Add emails and phone numbers to each user.
        // TODO; Add pagination.

        $query = 'SELECT ' .
            'u.id, u.first_name, u.last_name, u.created_at, u.updated_at,' .
            'GROUP_CONCAT(DISTINCT ue.email) as emails, ' .
            'GROUP_CONCAT(upn.phone_number) as phone_numbers ' .
            'FROM ' . $this->table . ' AS u ' .
            'LEFT JOIN user_emails as ue ON u.id = ue.user_id ' .
            'LEFT JOIN user_phone_numbers AS upn ON u.id = upn.user_id ' .
            'GROUP BY u.id, ue.id';

        $stmt = $this->conn->prepare($query);
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

    public function getSingle()
    {
        // TODO; Add emails and phone numbers to each user.
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = ? LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete()
    {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        return $stmt->rowCount() . ' user got deleted (id: ' . $this->id . ')' .
            ' along side the emails and phone numbers associated';
    }
}