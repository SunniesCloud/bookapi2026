<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

require_once '../config/database.php';

class BookAPI {
    private $conn;
    private $table = 'books';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // GET all books
    public function getAllBooks() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($books);
    }

    // GET single book
    public function getBook($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $book = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($book);
    }

    // POST create book
    public function createBook() {
        $data = json_decode(file_get_contents("php://input"));
        
        $query = "INSERT INTO " . $this->table . " 
                  (title, author, description, isbn, publication_year) 
                  VALUES (:title, :author, :description, :isbn, :publication_year)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':title', $data->title);
        $stmt->bindParam(':author', $data->author);
        $stmt->bindParam(':description', $data->description);
        $stmt->bindParam(':isbn', $data->isbn);
        $stmt->bindParam(':publication_year', $data->publication_year);
        if($stmt->execute()) {
            echo json_encode(['message' => 'Book created successfully', 'id' => $this->conn->lastInsertId()]);
        } else {
            echo json_encode(['message' => 'Book creation failed']);
        }
    }

    // PUT update book
    public function updateBook($id) {
        $data = json_decode(file_get_contents("php://input"));
        
        $query = "UPDATE " . $this->table . " 
                  SET title = :title, author = :author, description = :description, 
                      isbn = :isbn, publication_year = :publication_year 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':title', $data->title);
        $stmt->bindParam(':author', $data->author);
        $stmt->bindParam(':description', $data->description);
        $stmt->bindParam(':isbn', $data->isbn);
        $stmt->bindParam(':publication_year', $data->publication_year);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            echo json_encode(['message' => 'Book updated successfully']);
        } else {
            echo json_encode(['message' => 'Book update failed']);
        }
    }

    // DELETE book
    public function deleteBook($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            echo json_encode(['message' => 'Book deleted successfully']);
        } else {
            echo json_encode(['message' => 'Book deletion failed']);
        }
    }
}


$api = new BookAPI();
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            $api->getBook($_GET['id']);
        } else {
            $api->getAllBooks();
        }
        break;
    case 'POST':
        $api->createBook();
        break;
    case 'PUT':
        if(isset($_GET['id'])) {
            $api->updateBook($_GET['id']);
        }
        break;
    case 'DELETE':
        if(isset($_GET['id'])) {
            $api->deleteBook($_GET['id']);
        }
        break;
    default:
        echo json_encode(['message' => 'Invalid request method']);
        break;
}
?>