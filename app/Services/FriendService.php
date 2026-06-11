<?php

require_once __DIR__ . '/../Repositories/FriendRepository.php';

class FriendService
{
    public static function sendRequest(mysqli $conn, int $requesterId, int $targetId): array
    {
        if ($targetId <= 0 || $targetId === $requesterId) {
            return ['error' => 'Utilizador inválido.', 'code' => 400];
        }

        $existing = FriendRepository::getRelation($conn, $requesterId, $targetId);
        if ($existing) {
            if ($existing['status'] === 'accepted') return ['error' => 'Já são amigos.', 'code' => 409];
            if ($existing['status'] === 'pending')  return ['error' => 'Pedido já enviado.', 'code' => 409];
            if ($existing['status'] === 'blocked')  return ['error' => 'Não é possível enviar pedido.', 'code' => 403];
        }

        FriendRepository::insert($conn, $requesterId, $targetId);
        return ['success' => true, 'status' => 'pending'];
    }

    public static function respond(mysqli $conn, int $userId, int $fromId, string $action): array
    {
        if (!in_array($action, ['accept', 'decline'], true)) {
            return ['error' => 'Ação inválida.', 'code' => 400];
        }
        if ($fromId <= 0) {
            return ['error' => 'user_id inválido.', 'code' => 400];
        }

        $newStatus = $action === 'accept' ? 'accepted' : 'declined';
        $affected  = FriendRepository::updateStatus($conn, $fromId, $userId, $newStatus);

        if ($affected === 0) {
            return ['error' => 'Pedido não encontrado.', 'code' => 404];
        }
        return ['success' => true, 'status' => $newStatus];
    }

    public static function remove(mysqli $conn, int $userId, int $targetId): array
    {
        if ($targetId <= 0 || $targetId === $userId) {
            return ['error' => 'user_id inválido.', 'code' => 400];
        }
        FriendRepository::delete($conn, $userId, $targetId);
        return ['success' => true];
    }

    public static function listWithStatus(mysqli $conn, int $userId): array
    {
        $friends = FriendRepository::listAccepted($conn, $userId);
        return ['friends' => $friends];
    }
}
