<?php
namespace CarDeals;

class Car {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    public function create($data) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO cars (brand, model, year, price, mileage, description, image, status, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $chostatus = ['Available', 'Sold', 'Reserved'];
            $status = in_array($data['status'], $chostatus) ? $data['status'] : 'Available';
            
            $stmt->bind_param("ssiidsssi", 
                $data['brand'],
                $data['model'],
                $data['year'],
                $data['price'],
                $data['mileage'],
                $data['description'],
                $data['image'],
                $status,
                $data['created_by']
            );

            if($stmt->execute()) {
                $id = $this->conn->insert_id;
                \CarDeals\Audit::log($this->conn, $data['created_by'], 'create', 'cars', $id, "Created car {$data['brand']} {$data['model']}");
                return $id;
            }
            return false;
        } catch (\Exception $e) {
            error_log("Error creating car: " . $e->getMessage());
            throw new \Exception("Error creating car record: " . $e->getMessage());
        }
    }   

    public function getAll() {
        return $this->conn->query("SELECT * FROM cars ORDER BY created_at DESC");
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM cars WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function update($id, $data) {
        $stmt = $this->conn->prepare("UPDATE cars SET brand=?, model=?, year=?, price=?, mileage=?, description=?, status=? WHERE id=?");
        $stmt->bind_param("ssiidssi",
            $data['brand'],
            $data['model'],
            $data['year'],
            $data['price'],
            $data['mileage'],
            $data['description'],
            $data['status'],
            $id
        );

        if($stmt->execute()) {
            Audit::log($this->conn, $data['user_id'], 'update', 'cars', $id, "Updated car {$data['brand']} {$data['model']}");
            return true;
        }
        return false;
    }

    public function delete($id, $userId) {
        $car = $this->getById($id);
        $stmt = $this->conn->prepare("DELETE FROM cars WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()) {
            Audit::log($this->conn, $userId, 'delete', 'cars', $id, "Deleted car {$car['brand']} {$car['model']}");
            return true;
        }
        return false;
    }
}

?>