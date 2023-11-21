<?php

declare(strict_types=1);

namespace App\Services;

use Framework\Database;

class TransactionService
{
    public function __construct(private Database $db)
    {

    }

    public function create(array $formData)
    {
        $formattedDate = "{$formData['date']} 00:00:00";

        $this->db->query("INSERT INTO transactions (user_id, amount, description, date) VALUES (:user_id, :amount, :description, :date)", [
            'user_id' => $_SESSION['user'],
            'amount' => $formData['amount'],
            'description' => $formData['description'],
            'date' => $formattedDate
        ]);
    }

    public function getUserTransactions(int $length, int $offset)
    {
        $searchTerm = addcslashes($_GET['s'] ?? '', '%_');
        $params = [
            'user_id' => $_SESSION['user'],
            'description' => "%{$searchTerm}%"
        ];

        $transactions = $this->db->query(
            "SELECT *, DATE_FORMAT(date, '%Y-%m-%d') as formatted_date 
            FROM transactions 
            WHERE user_id = :user_id
            AND description LIKE :description
            LIMIT {$length} OFFSET {$offset}",
            $params
        )->findAll();

        $transactionCount = $this->db->query(
            "SELECT COUNT(*) as formatted_date 
            FROM transactions 
            WHERE user_id = :user_id
            AND description LIKE :description",
            $params
        )->count();

        return [$transactions, $transactionCount];
    }

    public function getUserTransaction(string $id)
    {
        return $this->db->query(
            "SELECT *, DATE_FORMAT(date, '%Y-%m-%d') as formatted_date
        FROM transactions 
        WHERE id = :id AND user_id = :user_id",
            [
                'id' => $id,
                'user_id' => $_SESSION['user']
            ]
        )->find();
    }

    public function update(array $formData, int $id)
    {
        $formattedDate = "{$formData['date']} 00:00:00";
        $this->db->query(
            "UPDATE transactions
            SET amount = :amount, 
                description = :description, 
                date = :date
            WHERE id = :id 
            AND user_id = :user_id",
            [
                'amount' => $formData['amount'],
                'description' => $formData['description'],
                'date' => $formattedDate,
                'id' => $id,
                'user_id' => $_SESSION['user']
            ]
        );
    }
    public function delete(int $id)
    {
        $this->db->query(
            "DELETE from transactions
            WHERE id = :id 
            AND user_id = :user_id",
            [
                'id' => $id,
                'user_id' => $_SESSION['user']
            ]
        );
    }
}