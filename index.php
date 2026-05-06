<?php
// 1. بدء الجلسة ومعالجة منطق PHP قبل أي HTML
session_start();

// 2. المصفوفات الأساسية
$genres = ["Fiction", "Non-Fiction", "Science", "History", "Biography", "Technology"];

// مصفوفة الكتب التجريبية (في الواقع العملي يتم تخزينها في جلسة أو قاعدة بيانات لمحاكاة الإضافة)
if (!isset($_SESSION['books'])) {
    $_SESSION['books'] = [
        ["id" => 1, "title" => "Clean Code", "author" => "Robert Martin", "genre" => "Technology", "year" => 2008, "pages" => 464],
        ["id" => 2, "title" => "The Hobbit", "author" => "J.R.R. Tolkien", "genre" => "Fiction", "year" => 1937, "pages" => 310],
        ["id" => 3, "title" => "A Brief History of Time", "author" => "Stephen Hawking", "genre" => "Science", "year" => 1988, "pages" => 256],
    ];
}

$books = &$_SESSION['books'];
$errors = [];
$submittedData = ['title' => '', 'author' => '', 'genre' => '', 'year' => '', 'pages' => ''];

// 3. معالجة إرسال النموذج (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // التطهير (Sanitization)
    foreach ($submittedData as $key => $val) {
        $submittedData[$key] = trim(htmlspecialchars($_POST[$key] ?? ''));
    }

    // قواعد التحقق (Validation Logic)[cite: 1]
    if (empty($submittedData['title']) || strlen($submittedData['title']) < 3 || strlen($submittedData['title']) > 120) {
        $errors['title'] = "Title is required (3-120 chars).";
    }

    $authorWords = explode(' ', $submittedData['author']);
    if (count($authorWords) < 2) {
        $errors['author'] = "Author must contain at least first and last name.";
    }

    if (!in_array($submittedData['genre'], $genres)) {
        $errors['genre'] = "Please select a valid genre.";
    }

    $currentYear = date("Y");
    if (!filter_var($submittedData['year'], FILTER_VALIDATE_INT) || $submittedData['year'] < 1000 || $submittedData['year'] > $currentYear) {
        $errors['year'] = "Year must be between 1000 and $currentYear.";
    }

    if (!filter_var($submittedData['pages'], FILTER_VALIDATE_INT) || $submittedData['pages'] <= 0) {
        $errors['pages'] = "Pages must be a positive integer.";
    }

    // إذا لم تكن هناك أخطاء، قم بإضافة الكتاب[cite: 1]
    if (empty($errors)) {
        // توليد المعرف يدوياً[cite: 1]
        $maxId = 0;
        foreach ($books as $book) {
            if ($book['id'] > $maxId) $maxId = $book['id'];
        }
        
        $newBook = [
            "id" => $maxId + 1,
            "title" => $submittedData['title'],
            "author" => $submittedData['author'],
            "genre" => $submittedData['genre'],
            "year" => (int)$submittedData['year'],
            "pages" => (int)$submittedData['pages']
        ];

        $books[] = $newBook;
        $_SESSION['success'] = "Book added successfully!";
        
        // إعادة التوجيه لمنع تكرار الإرسال[cite: 1]
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Book Library - Abu Yasir</title>
    <!-- Bootstrap 5 CDN[cite: 1] -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <!-- رسالة النجاح[cite: 1] -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- قسم النموذج[cite: 1] -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">Add New Book</div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">Please correct the errors below.</div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($submittedData['title']) ?>">
                            <div class="invalid-feedback"><?= $errors['title'] ?? '' ?></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Author</label>
                            <input type="text" name="author" class="form-control <?= isset($errors['author']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($submittedData['author']) ?>">
                            <div class="invalid-feedback"><?= $errors['author'] ?? '' ?></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Genre</label>
                            <select name="genre" class="form-select <?= isset($errors['genre']) ? 'is-invalid' : '' ?>">
                                <option value="">Select Genre</option>
                                <?php foreach ($genres as $g): ?>
                                    <option value="<?= $g ?>" <?= ($submittedData['genre'] == $g) ? 'selected' : '' ?>><?= $g ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"><?= $errors['genre'] ?? '' ?></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" class="form-control <?= isset($errors['year']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($submittedData['year']) ?>">
                            <div class="invalid-feedback"><?= $errors['year'] ?? '' ?></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pages</label>
                            <input type="number" name="pages" class="form-control <?= isset($errors['pages']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($submittedData['pages']) ?>">
                            <div class="invalid-feedback"><?= $errors['pages'] ?? '' ?></div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Add Book</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- قسم الجدول[cite: 1] -->
        <div class="col-md-8">
            <div class="table-responsive bg-white p-3 rounded shadow-sm">
                <table class="table table-striped table-hover table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Genre</th>
                            <th>Year</th>
                            <th>Pages</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= $book['id'] ?></td>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><span class="badge bg-secondary"><?= $book['genre'] ?></span></td>
                            <td><?= $book['year'] ?></td>
                            <td><?= $book['pages'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>