<?php
    include_once "../module/bootstrap.php";
    include_once MODULE . "/helpers.php";

    if(!isset($_GET['name'])) {
        RedirectToAppHome();
    }

    $qualificationKey = CleanLink((string)$_GET['name']);

    include MODEL . "/init.php";

    $qualificationTitle = null;
    $qualificationQuery = Database::Query("SELECT DISTINCT qualification FROM categories;");
    if ($qualificationQuery) {
        while ($row = mysqli_fetch_assoc($qualificationQuery)) {
            if (CleanLink($row['qualification']) === $qualificationKey) {
                $qualificationTitle = $row['qualification'];
                break;
            }
        }
        mysqli_free_result($qualificationQuery);
    }

    if ($qualificationTitle === null) {
        RedirectToAppHome();
    }

    $qualificationKey = CleanLink($qualificationTitle);
    $qualificationTitleEscaped = htmlspecialchars($qualificationTitle, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <title>Wybierz ilość pytań ~ <?php echo $qualificationTitleEscaped; ?></title>
    <?php
        include_once "../module/bootstrap.php";
        include MODULE . "/common_head.html";
    ?>
</head>
<body class="qualification-page">
    <?php 
        include_once MODULE . "/header.php";
        echo Common_Header();
    ?>

    <header class="hero-section container py-5 text-center mt-5">
        <h1 class="display-4 fw-bold mb-4 text-gradient">Wybierz ilość pytań</h1>
        <p class="lead mb-5 text-muted">Kwalifikacja: <span class="fw-bold"><?php echo $qualificationTitleEscaped; ?></span></p>
    </header>

    <main class="container">
        <div class="row justify-content-center">
            <?php
                include MODULE . "/card/select_question_count.php";
                echo Select_Question_Count_Card(1, $qualificationKey, 'qualification');
                echo Select_Question_Count_Card(20, $qualificationKey, 'qualification');
                echo Select_Question_Count_Card(40, $qualificationKey, 'qualification');
            ?>
            <div class="col-12 col-md-4 col-lg-3 mb-4">
                <article class="card h-100 glass">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <p class="card-text text-muted">Niestandardowe</p>
                        <form action="<?php echo htmlspecialchars(RELATIVE_PATH_APP . '/exam', ENT_QUOTES, 'UTF-8'); ?>" method="GET" class="mt-3">
                            <input type="hidden" name="qualification" value="<?php echo htmlspecialchars($qualificationKey, ENT_QUOTES, 'UTF-8'); ?>">
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
