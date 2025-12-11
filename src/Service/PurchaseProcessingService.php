<?php

namespace App\Service;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final class PurchaseProcessingService
{
    private const USER_BALANCE_UPDATE = 'A user balance has been updated.';
    private const USER_ITEM_RELATION = 'A user/item relation has been created.';
    private EntityManager $em;
    private LoggerInterface $monolog;

    public function __construct(
        LoggerInterface $monolog,
        EntityManagerInterface $em
    ) {
        $this->em = $em;
        $this->monolog = $monolog;
    }

    public function buyItem(int $userId, int $itemId): bool|array
    {
        // we do NOT use the EntityManager here in order to stick to the task initial code!
        /** @var \PDO $conn */
        $conn = $this->getDBInstance();

        $item = $this->getDataFromDB($conn, 'item', 'itemId', $itemId);

        try {
            $conn->beginTransaction();
            $user = $this->getDataFromDB($conn, 'user', 'userId', $userId, ' FOR UPDATE');

            if ($user['balance'] < $item['cost']) {
                $this->monolog->critical('User has insufficient funds!');
                $conn->rollBack();

                return false;
            }
            $stm = $conn->prepare("UPDATE user SET balance = balance - :itemCost WHERE id=:userId");
            $stm->bindValue(":itemCost", $item['cost'], \PDO::PARAM_INT);
            $stm->bindValue(":userId", $user['id'], \PDO::PARAM_INT);
            $success = $stm->execute();

            if (!$success) {
                $this->monolog->error("There was an error of charging {$item['cost']} to the client {$user['id']}");
                $conn->rollBack();

                return false;
            }

            // just for time checking purposes is...
            $this->createTransaction(self::USER_BALANCE_UPDATE, $item['cost']);

            $stm = $conn->prepare("INSERT INTO user_item (item_id, user_id) VALUES (:itemId, :userId)");
            $stm->bindValue(":itemId", $item['id'], \PDO::PARAM_INT);
            $stm->bindValue(":userId", $user['id'], \PDO::PARAM_INT);
            $success = $stm->execute();

            if (!$success) {
                $this->monolog->error(
                    "There was an error of making relatable a user {$user['id']} with an item {$item['id']}"
                );
                $conn->rollBack();

                return false;
            }

            // just for time checking purposes is...
            $this->createTransaction(self::USER_ITEM_RELATION);

            $conn->commit();
        } catch (\Exception $e)
        {
            $this->monolog->critical($e->getCode(), $e->getMessage());
            throw new Exception($e->getCode(), $e->getMessage());
        }

        $transactionsSmt = $conn->query('select * from transaction');

        return $transactionsSmt->fetchAll();
    }

    private function getDBInstance(): \PDO
    {
        $servername = "select_ms_db:3306";
        $username = "root";
        $password = "root";
        $dbname = "check";

        try {
            $conn = new \PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch(\PDOException $e) {
            $this->monolog->critical("Connection failed: {$e->getMessage()}");
            throw new \PDOException($e->getMessage(), $e->getCode());
        }

        return $conn;
    }

    private function getDataFromDB(
        \PDO $conn,
        string $table,
        string $parameter,
        int $value,
        string $selectForUpdate = ''
    ): array {
        $stm = $conn->prepare("SELECT * FROM $table WHERE id = :$parameter $selectForUpdate");
        $stm->bindValue(":$parameter", $value, \PDO::PARAM_INT);
        $stm->execute();
        $data = $stm->fetch(\PDO::FETCH_ASSOC);

        if (
            !is_array($data)
            && !isset($data['id'])
        ) {
            $message = "There id no $parameter with ID = $value";
            $this->monolog->critical($message);
            throw new \Exception($message, \Monolog\Level::Critical);
        }

        return $data;
    }

    private function createTransaction(string $name, int $amount = 0): void
    {
        // just for time checking purposes is...
        $transaction = new Transaction();
        $transaction->setName($name);
        $transaction->setAmount($amount);
        $transaction->setCreated(new \DateTimeImmutable());
        $this->em->persist($transaction);
        $this->em->flush();
    }

    public function buyItem2(int $userId, int $itemId): bool|array
    {
        // we do NOT use the EntityManager here in order to stick to the task initial code!
        /** @var \PDO $conn */
        $conn = $this->getDBInstance();

        $item = $this->getDataFromDB($conn, 'item', 'itemId', $itemId);
        $user = $this->getDataFromDB($conn, 'user', 'userId', $userId);

        $stm = $conn->prepare("UPDATE user SET balance = balance - :itemCost WHERE id=:userId");
        $stm->bindValue(":itemCost", $item['cost'], \PDO::PARAM_INT);
        $stm->bindValue(":userId", $user['id'], \PDO::PARAM_INT);
        $stm->execute();

        $stm = $conn->prepare("INSERT INTO user_item (item_id, user_id) VALUES (:itemId, :userId)");
        $stm->bindValue(":itemId", $item['id'], \PDO::PARAM_INT);
        $stm->bindValue(":userId", $user['id'], \PDO::PARAM_INT);
        $stm->execute();

        return true;
    }
}
