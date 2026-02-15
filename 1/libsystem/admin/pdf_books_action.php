<?php
include 'includes/conn.php';
session_start();

$uploadDir = __DIR__ . "/uploads/pdf_books/";
if(!is_dir($uploadDir)){
    mkdir($uploadDir, 0777, true);
}

// === Upload PDF ===
if(isset($_POST['upload'])){
    $title = $_POST['title'];
    $file = $_FILES['pdf_file']['name'];
    $tmp = $_FILES['pdf_file']['tmp_name'];

    if(empty($file)){
        $_SESSION['error'] = "No file selected!";
        header("Location: pdf_books.php");
        exit();
    }

    $ext = pathinfo($file, PATHINFO_EXTENSION);
    if(strtolower($ext) != "pdf"){
        $_SESSION['error'] = "Only PDF files are allowed!";
    } else {
        $newFileName = pathinfo($file, PATHINFO_FILENAME) . '_' . time() . '.' . $ext;
        $path = $uploadDir . $newFileName;
        if(move_uploaded_file($tmp, $path)){
            $stmt = $conn->prepare("INSERT INTO pdf_books (title, file_path) VALUES (?, ?)");
            $stmt->bind_param("ss", $title, $newFileName);
            if($stmt->execute()){
                $_SESSION['success'] = "PDF uploaded successfully!";
            } else {
                $_SESSION['error'] = "Database error!";
            }
        } else {
            $_SESSION['error'] = "Failed to upload file!";
        }
    }

    header("Location: pdf_books.php");
    exit();
}

// === Delete PDF (Move to Archive) ===
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $result = $conn->query("SELECT * FROM pdf_books WHERE id='$id'");
    
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $file_path = $uploadDir . $row['file_path'];

        // Move to archive table
        $stmt = $conn->prepare("INSERT INTO archived_pdf_books (title, file_path) VALUES (?, ?)");
        $stmt->bind_param("ss", $row['title'], $row['file_path']);
        $stmt->execute();

        // Delete from main table
        $conn->query("DELETE FROM pdf_books WHERE id='$id'");

        // (Optional) You can delete or keep the file — if you want to KEEP, comment out the unlink line below
        if(file_exists($file_path)){
            unlink($file_path);
        }

        $_SESSION['success'] = "PDF moved to archive successfully!";
    } else {
        $_SESSION['error'] = "PDF not found!";
    }
    header("Location: pdf_books.php");
    exit();
}

// === Get PDF for Edit (AJAX) ===
if(isset($_POST['getEdit'])){
    $id = $_POST['id'];
    $res = $conn->query("SELECT * FROM pdf_books WHERE id='$id'");
    echo json_encode($res->fetch_assoc());
    exit();
}

// === Update PDF ===
if(isset($_POST['update'])){
    $id = $_POST['id'];
    $title = $_POST['title'];
    $file = $_FILES['pdf_file']['name'];

    if(!empty($file)){
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if(strtolower($ext) != "pdf"){
            $_SESSION['error'] = "Only PDF files allowed!";
            header("Location: pdf_books.php");
            exit();
        }

        $newFileName = pathinfo($file, PATHINFO_FILENAME) . '_' . time() . '.' . $ext;
        $path = $uploadDir . $newFileName;

        if(move_uploaded_file($_FILES['pdf_file']['tmp_name'], $path)){
            // Delete old file
            $old = $conn->query("SELECT file_path FROM pdf_books WHERE id='$id'")->fetch_assoc();
            if(file_exists($uploadDir.$old['file_path'])){
                unlink($uploadDir.$old['file_path']);
            }
            $conn->query("UPDATE pdf_books SET title='$title', file_path='$newFileName' WHERE id='$id'");
            $_SESSION['success'] = "PDF updated successfully!";
        } else {
            $_SESSION['error'] = "File upload failed!";
        }
    } else {
        $conn->query("UPDATE pdf_books SET title='$title' WHERE id='$id'");
        $_SESSION['success'] = "PDF title updated successfully!";
    }

    header("Location: pdf_books.php");
    exit();
}
?>
