<?php

// Audit Log Function
function logAuditAction($employeeId, $action, $entityType, $entityId, $details = null) {
    $conn = connection(); // Use the existing database connection function
    
    $stmt = $conn->prepare("
        INSERT INTO audit_logs (employee_id, action, entity_type, entity_id, details)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issis", $employeeId, $action, $entityType, $entityId, $details);

    if (!$stmt->execute()) {
        error_log("Audit Log Error: " . $stmt->error); // Log any errors
    }

    $stmt->close();
    $conn->close();
}

// Sanitize User Input
function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}