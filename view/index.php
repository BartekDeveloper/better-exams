<!DOCTYPE html>
<html lang="pl">
<head>
    <title>Wybierz egzamin ~ Egzaminy Inf.03 i inf.04</title>
    <?php
        include "module/bootstrap.php";
        include MODULE."/common_head.html";
        include MODULE."/card/select_exam.php";
        include MODEL."/tables.php";
        include MODEL."/database.php";
    ?>
</head>
<body>

    <nav id="main-nav" class="glass">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a href="/" class="nav-brand">Teoria</a>
            <div class="nav-links d-flex gap-2">
                <a href="/" class="nav-item">Start</a>
                <a href="#exams" class="nav-item">Egzaminy</a>
                <a href="#contact" class="nav-item">Kontakt</a>
            </div>
        </div>
    </nav>

    <header class="hero-section container py-5 text-center mt-5"> 
        <h1 class="display-4 fw-bold mb-4 text-gradient">Egzaminy Zawodowe</h1>
        <p class="lead mb-5 text-muted">Wybierz kategorię, aby rozpocząć test.</p>
    </header>

    <main class="container">
        <div class="row">
            <?php
            if(!isset($db)) {
                $db = new Database();
            }

            $result = Database::Query("SELECT * FROM categories ORDER BY qualification, name;");
            
            while($row = mysqli_fetch_row($result)) {
                echo SelectExamType(Category::FromRow($row));
            }
            ?>
        </div>
    </main>

    <footer></footer>
</body>
</html>