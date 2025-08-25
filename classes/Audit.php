<?php
namespace CarDeals;

class Audit {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public static function log($conn, $userId, $action, $entity, $entityId, $details) {
        $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, details, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("issis", $userId, $action, $entity, $entityId, $details);
        return $stmt->execute();
    }

    public function getRecentActivity($limit = 5) {
        $stmt = $this->conn->prepare("
            SELECT a.*, u.name as user_name  FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id  ORDER BY a.created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result();
    }
}