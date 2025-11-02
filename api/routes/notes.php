<?php

// Authenticate user
$auth = new Auth();
$user = $auth->getCurrentUser();

if (!$user) {
    Response::unauthorized('Authentication required');
}

$method = $_SERVER['REQUEST_METHOD'];
$note_id = $parts[2] ?? null;

switch ($method) {
    case 'GET':
        if ($note_id) {
            getNote($user->user_id, $note_id);
        } else {
            getNotes($user->user_id);
        }
        break;
    
    case 'POST':
        createNote($user->user_id);
        break;
    
    case 'PUT':
        if ($note_id) {
            updateNote($user->user_id, $note_id);
        } else {
            Response::error('Note ID is required');
        }
        break;
    
    case 'DELETE':
        if ($note_id) {
            deleteNote($user->user_id, $note_id);
        } else {
            Response::error('Note ID is required');
        }
        break;
    
    default:
        Response::error('Method not allowed', 405);
}

function getNotes($user_id) {
    try {
        $database = new Database();
        $db = $database->connect();

        $search = $_GET['search'] ?? '';
        $tag = $_GET['tag'] ?? '';
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

        $query = "SELECT id, title, content, tags, created_at, updated_at, last_modified 
                  FROM notes WHERE user_id = :user_id";
        
        $params = [':user_id' => $user_id];

        if (!empty($search)) {
            $query .= " AND (title LIKE :search OR content LIKE :search OR tags LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($tag)) {
            $query .= " AND tags LIKE :tag";
            $params[':tag'] = '%' . $tag . '%';
        }

        $query .= " ORDER BY last_modified DESC LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $notes = $stmt->fetchAll();

        Response::success([
            'notes' => $notes,
            'count' => count($notes)
        ]);

    } catch (Exception $e) {
        Response::error('Failed to fetch notes: ' . $e->getMessage(), 500);
    }
}

function getNote($user_id, $note_id) {
    try {
        $database = new Database();
        $db = $database->connect();

        $query = "SELECT id, title, content, tags, created_at, updated_at, last_modified 
                  FROM notes WHERE id = :id AND user_id = :user_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $note_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $note = $stmt->fetch();

        if (!$note) {
            Response::notFound('Note not found');
        }

        Response::success($note);

    } catch (Exception $e) {
        Response::error('Failed to fetch note: ' . $e->getMessage(), 500);
    }
}

function createNote($user_id) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['title'])) {
        Response::error('Title is required');
    }

    $title = trim($data['title']);
    $content = isset($data['content']) ? trim($data['content']) : '';
    $tags = isset($data['tags']) ? trim($data['tags']) : '';

    if (empty($title)) {
        Response::error('Title cannot be empty');
    }

    try {
        $database = new Database();
        $db = $database->connect();

        $query = "INSERT INTO notes (user_id, title, content, tags) 
                  VALUES (:user_id, :title, :content, :tags)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':tags', $tags);
        
        if ($stmt->execute()) {
            $note_id = $db->lastInsertId();
            
            // Fetch the created note
            $query = "SELECT id, title, content, tags, created_at, updated_at, last_modified 
                      FROM notes WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $note_id);
            $stmt->execute();
            $note = $stmt->fetch();

            Response::success($note, 'Note created successfully', 201);
        } else {
            Response::error('Failed to create note', 500);
        }

    } catch (Exception $e) {
        Response::error('Failed to create note: ' . $e->getMessage(), 500);
    }
}

function updateNote($user_id, $note_id) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    try {
        $database = new Database();
        $db = $database->connect();

        // Check if note belongs to user
        $query = "SELECT id FROM notes WHERE id = :id AND user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $note_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        if (!$stmt->fetch()) {
            Response::notFound('Note not found');
        }

        // Build update query
        $updates = [];
        $params = [':id' => $note_id, ':user_id' => $user_id];

        if (isset($data['title'])) {
            $updates[] = "title = :title";
            $params[':title'] = trim($data['title']);
        }

        if (isset($data['content'])) {
            $updates[] = "content = :content";
            $params[':content'] = trim($data['content']);
        }

        if (isset($data['tags'])) {
            $updates[] = "tags = :tags";
            $params[':tags'] = trim($data['tags']);
        }

        if (empty($updates)) {
            Response::error('No fields to update');
        }

        $query = "UPDATE notes SET " . implode(', ', $updates) . ", last_modified = CURRENT_TIMESTAMP 
                  WHERE id = :id AND user_id = :user_id";
        
        $stmt = $db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        if ($stmt->execute()) {
            // Fetch updated note
            $query = "SELECT id, title, content, tags, created_at, updated_at, last_modified 
                      FROM notes WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $note_id);
            $stmt->execute();
            $note = $stmt->fetch();

            Response::success($note, 'Note updated successfully');
        } else {
            Response::error('Failed to update note', 500);
        }

    } catch (Exception $e) {
        Response::error('Failed to update note: ' . $e->getMessage(), 500);
    }
}

function deleteNote($user_id, $note_id) {
    try {
        $database = new Database();
        $db = $database->connect();

        $query = "DELETE FROM notes WHERE id = :id AND user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $note_id);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                Response::success([], 'Note deleted successfully');
            } else {
                Response::notFound('Note not found');
            }
        } else {
            Response::error('Failed to delete note', 500);
        }

    } catch (Exception $e) {
        Response::error('Failed to delete note: ' . $e->getMessage(), 500);
    }
}
