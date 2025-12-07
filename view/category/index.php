<?php
    include_once "../module/bootstrap.php";
    include_once MODULE . "/helpers.php";

    if(!isset($_GET['name'])) {
        RedirectToAppHome();
    }

    $categoryKey = CleanLink((string)$_GET['name']);

    include MODEL . "/init.php";

    $categoryTitle = null;
    $categoryQuery = Database::Query("SELECT name FROM categories WHERE name != 'inne';");
    if ($categoryQuery) {
        while ($row = mysqli_fetch_assoc($categoryQuery)) {
            if (CleanLink($row['name']) === $categoryKey) {
                $categoryTitle = $row['name'];
                break;
            }
        }
        mysqli_free_result($categoryQuery);
    }

    if ($categoryTitle === null) {
        RedirectToAppHome();
    }

    $categoryKey = CleanLink($categoryTitle);
    $categoryTitleEscaped = htmlspecialchars($categoryTitle, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <title>Wybierz ilość pytań ~ <?php echo $categoryTitleEscaped; ?></title>
    <?php
        include_once "../module/bootstrap.php";
        include MODULE . "/common_head.html";
    ?>
</head>
<body class="category-page">
    <?php 
        include_once MODULE . "/header.php";
        echo Common_Header();
    ?>

    <header class="hero-section container py-5 text-center mt-5">
        <h1 class="display-4 fw-bold mb-4 text-gradient">Wybierz ilość pytań</h1>
        <p class="lead mb-5 text-muted">Kategoria: <span class="fw-bold"><?php echo $categoryTitleEscaped; ?></span></p>
    </header>

    <main class="container">
        <div class="row justify-content-center">
            <?php
                include MODULE . "/card/select_question_count.php";
                echo Select_Question_Count_Card(1, $categoryKey);
                echo Select_Question_Count_Card(20, $categoryKey);
                echo Select_Question_Count_Card(40, $categoryKey);
            ?>
            <div class="col-12 col-md-4 col-lg-3 mb-4">
                <article class="card h-100 glass">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <p class="card-text text-muted">Niestandardowe</p>
                        <form action="<?php echo htmlspecialchars(RELATIVE_PATH_APP . '/view/exam', ENT_QUOTES, 'UTF-8'); ?>" method="GET" class="mt-3">
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars($categoryKey, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="mb-3">
                                <label for="custom-question-count" class="form-label">Ilość pytań</label>
                                <input type="number" id="custom-question-count" class="form-control" name="count" min="1" max="100" placeholder="1-100">
                            </div>
                            <button class="btn btn-primary w-100" type="submit">Start</button>
                        </form>
                    </div>
                </article>
            </div>
        </div>
    </main>
</body>
</html>
