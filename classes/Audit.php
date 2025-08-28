<?php
namespace CarDeals;
class Audit {
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }
    public static function log($conn, $userId, $action, $entity, $entityId, $details) {
        $state = $conn->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, details, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $state->bind_param("issis", $userId, $action, $entity, $entityId, $details);
        return $state->execute();
    }

    public function getRecentActivity($limit = 5) {
        $state = $this->conn->prepare("
            SELECT a.*, u.name as user_name  FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id  ORDER BY a.created_at DESC LIMIT ?");
        $state->bind_param("i", $limit);
        $state->execute();
        return $state->get_result();
    }
}