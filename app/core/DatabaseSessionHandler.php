<?php
// FILE: /app/core/DatabaseSessionHandler.php

/**
 * DatabaseSessionHandler
 *
 * Stores PHP sessions in MySQL so they persist across
 * Vercel's stateless serverless function invocations.
 */
class DatabaseSessionHandler implements SessionHandlerInterface {
    private $pdo;
    private $lifetime;

    public function __construct() {
        $this->lifetime = (int) ini_get('session.gc_maxlifetime') ?: 1440;
    }

    public function open($path, $name): bool {
        try {
            $this->pdo = Database::getInstance()->getConnection();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function close(): bool {
        return true;
    }

    public function read($id): string {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT data FROM sessions WHERE id = :id AND expires_at > NOW()'
            );
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? base64_decode($row['data']) : '';
        } catch (Exception $e) {
            return '';
        }
    }

    public function write($id, $data): bool {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO sessions (id, data, expires_at) VALUES (:id, :data, :expires)
                 ON CONFLICT (id) DO UPDATE SET data = EXCLUDED.data, expires_at = EXCLUDED.expires_at'
            );
            $expires = date('Y-m-d H:i:s', time() + $this->lifetime);
            return $stmt->execute([
                ':id'      => $id,
                ':data'    => base64_encode($data),
                ':expires' => $expires,
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function destroy($id): bool {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE id = :id');
            return $stmt->execute([':id' => $id]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function gc($max_lifetime): int|false {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE expires_at < NOW()');
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            return false;
        }
    }
}
