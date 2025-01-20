<?php

require 'JwtMiddleware.php';

class Book
{
    public $id;
    public $title;
    public $author;
    public $published_year;

    public function __construct($id, $title, $author, $published_year)
    {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->published_year = $published_year;
    }
}



$secretKey = '35da42520b3ec0c78cc6f413276e90b4f7b4dacce12dcd74d8a705045b026883';


$middleware = new JwtMiddleware($secretKey);


$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$uriParts = explode("/", trim($_SERVER["PATH_INFO"], "/"));
$resource = $uriParts[0] ?? null; // 'api'
$entity = $uriParts[1] ?? null;   // 'books'
$id = $uriParts[2] ?? null;       // Book ID (if exists)


if ($resource !== 'api' || $entity !== 'books') {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
    exit;
}


$next = function ($request) use ($method, $id) {
	  #Implement an in-memory storage for the books (an array).
	  $books = [
    new Book(1, "1984", "George Orwell", 1949),
    new Book(2, "To Kill a Mockingbird", "Harper Lee", 1960),
    new Book(3, "The Great Gatsby", "F. Scott Fitzgerald", 1925),
    new Book(4, "Pride and Prejudice", "Jane Austen", 1813),
    new Book(5, "Moby-Dick", "Herman Melville", 1851),
    new Book(6, "War and Peace", "Leo Tolstoy", 1869),
    new Book(7, "Crime and Punishment", "Fyodor Dostoevsky", 1866),
    new Book(8, "The Catcher in the Rye", "J.D. Salinger", 1951),
    new Book(9, "The Hobbit", "J.R.R. Tolkien", 1937),
    new Book(10, "Fahrenheit 451", "Ray Bradbury", 1953),
    new Book(11, "Jane Eyre", "Charlotte Brontë", 1847),
    new Book(12, "Wuthering Heights", "Emily Brontë", 1847),
    new Book(13, "Brave New World", "Aldous Huxley", 1932),
    new Book(14, "The Odyssey", "Homer", -800),
    new Book(15, "The Iliad", "Homer", -750)
	];
    
     switch ($method) {
        case "GET":
            if ($id) {
                foreach ($books as $book) {
                    if ($book->id == $id) {
                        echo json_encode($book);
                        exit();
                    }
                }
                http_response_code(404);
                echo json_encode(["message" => "Book not found"]);
            } else {
                echo json_encode($books);
            }
            break;

        case "POST":
            if ($id == "") {
                $input = json_decode(file_get_contents("php://input"), true);
                if (
                    !empty($input["title"]) &&
                    !empty($input["author"]) &&
                    !empty($input["published_year"])
                ) {
                    $newId = end($books)->id + 1;
                    $newBook = new Book(
                        $newId,
                        $input["title"],
                        $input["author"],
                        $input["published_year"]
                    );
                    $books[] = $newBook;
                    echo json_encode([
                        "message" => "Book added successfully",
                        "book" => $newBook,
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode(["message" => "Invalid input"]);
                }
            }
            if ($id == "generate-summary") {
                $input = json_decode(file_get_contents("php://input"), true);

                $bookId = $input["book_id"] ?? null;

                if (!$bookId) {
                    http_response_code(400);
                    echo json_encode(["error" => "Missing book_id"]);
                    exit();
                }
				$count =0;

                foreach ($books as $book) {
                    if ($book->id == $bookId) {
						$count =1;
                        $apiUrl =
                            "https://api-inference.huggingface.co/models/facebook/bart-large-cnn";
                        $hugging_face_api_key = "";

                        $summaryInput = "Title: {$book->title}, Author: {$book->author}, Year: {$book->published_year}";

                        try {
                            $ch = curl_init($apiUrl);

                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                "Authorization: Bearer " . $hugging_face_api_key,
                                "Content-Type: application/json",
                            ]);
                            curl_setopt(
                                $ch,
                                CURLOPT_POSTFIELDS,
                                json_encode(["inputs" => $summaryInput])
                            );

                            $response = curl_exec($ch);
                            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                            
                            curl_close($ch);

                            if ($httpCode !== 200) {
                                http_response_code(500);
                                echo json_encode([
                                    "error" => "Error generating summary",
                                    "details" => $response,
                                ]);
                                exit();
                            }

                            $result = json_decode($response, true);
                            $summary =
                                $result[0]["summary_text"] ??
                                "No summary generated";

                           
                            echo json_encode([
                                "book" => $book,
                                "summary" => $summary,
                            ]);
                        } catch (Exception $e) {
                            http_response_code(500);
                            echo json_encode([
                                "error" =>
                                    "Failed to communicate with Hugging Face API",
                                "details" => $e->getMessage(),
                            ]);
                            exit();
                        }
                    }
                }
				if($count == 0){
						http_response_code(404);
						echo json_encode(["message" => "Book not found"]);
						exit;
				}
            }
            break;

        case "PUT":
            
            $input = json_decode(file_get_contents("php://input"), true);
            foreach ($books as &$book) {
                if ($book->id == $id) {
                    if (!empty($input["title"])) {
                        $book->title = $input["title"];
                    }
                    if (!empty($input["author"])) {
                        $book->author = $input["author"];
                    }
                    if (!empty($input["published_year"])) {
                        $book->published_year = $input["published_year"];
                    }
                    echo json_encode([
                        "message" => "Book updated successfully",
                        "book" => $book,
                    ]);
                    exit();
                }
            }
            http_response_code(404);
            echo json_encode(["message" => "Book not found"]);
            break;

        case "DELETE":
           
            foreach ($books as $key => $book) {
                if ($book->id == $id) {
                    unset($books[$key]);
                    echo json_encode([
                        "message" => "Book deleted successfully",
                    ]);
                    exit();
                }
            }
			 http_response_code(404);
             echo json_encode(["message" => "Book not found"]);
			 break;
        default:
            http_response_code(405);
            echo json_encode(["message" => "Method not allowed"]);
            break;
    }
};

// Apply middleware for protected routes
$middleware->handle([], $next);

?>