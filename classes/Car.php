<?php
namespace CarDeals;

class Car {
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }
    public function create($data) {
        try {
            $state = $this->conn->prepare("INSERT INTO cars (brand, model, year, price, mileage, description, image, status, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $chostatus = ['Available', 'Sold', 'Reserved'];
            $status = in_array($data['status'], $chostatus) ? $data['status'] : 'Available';
            $state->bind_param("ssiidsssi", $data['brand'],$data['model'],$data['year'],$data['price'],$data['mileage'],$data['description'], $data['image'], $status,$data['created_by']);

            if($state->execute()) {
                $id = $this->conn->insert_id;
                \CarDeals\Audit::log($this->conn, $data['created_by'], 'create', 'cars', $id, "Created car {$data['brand']} {$data['model']}");
                return $id;
            }
            return false;
        } catch (\Exception $e) {
            error_log("error creating car: " . $e->getMessage());
            throw new \Exception("error creating car record: " . $e->getMessage());
        }
    }   
    public function getAll() {
        return $this->conn->query("SELECT * FROM cars ORDER BY created_at DESC");
    }

    public function getById($id) {
        $state = $this->conn->prepare("SELECT * FROM cars WHERE id = ?");
        $state->bind_param("i", $id);
        $state->execute();
        return $state->get_result()->fetch_assoc();
    }

    public function update($id, $data) {
        $state = $this->conn->prepare("UPDATE cars SET brand=?, model=?, year=?, price=?, mileage=?, description=?, status=? WHERE id=?");
        $state->bind_param("ssiidssi", $data['brand'], $data['model'], $data['year'], $data['price'], $data['mileage'], $data['description'], $data['status'], $id);

        if($state->execute()) {
            Audit::log($this->conn, $data['user_id'], 'update', 'cars', $id, "Updated car {$data['brand']} {$data['model']}");
            return true;
        }
        return false;
    }

    public function delete($id, $userId) {
        $car = $this->getById($id);
        $state = $this->conn->prepare("DELETE FROM cars WHERE id = ?");
        $state->bind_param("i", $id);
        
        if($state->execute()) {
            Audit::log($this->conn, $userId, 'delete', 'cars', $id, "Deleted car {$car['brand']} {$car['model']}");
            return true;
        }
        return false;
    }
}

?>