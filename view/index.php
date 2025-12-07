<!DOCTYPE html>
<html lang="pl">
<head>
    <title>Wybierz egzamin ~ Egzaminy Inf.03 i inf.04</title>
    <?php
        include "module/bootstrap.php";
        include_once MODULE."/helpers.php";
        include MODULE."/common_head.html";
        include MODULE."/card/select_exam.php";
        include MODEL."/tables.php";
        include MODEL."/database.php";
    ?>
</head>
<body>
    <?php 
        include_once "module/header.php";
        echo Common_Header();
    ?>

    <main id="exams" class="container">
        <div class="row">
            <?php
            if(!isset($db)) {
                $db = new Database();
            }

            $qualifications = Database::Query("SELECT DISTINCT qualification FROM categories;");
            while($qualification = mysqli_fetch_row($qualifications)) {
                $categories = Database::Query("SELECT * FROM categories WHERE name != 'inne' AND qualification = '{$qualification[0]}' ORDER BY name;");
                
                $qualificationName = (string) $qualification[0];

                echo "<section class='qualification-section'>";
                echo "<h2>{$qualificationName}</h2>";
                echo "<div class='row'>";

                echo Select_Exam_Whole($qualificationName);

                while($row = mysqli_fetch_row($categories)) {
                    echo Select_Exam_Category(Category::FromRow($row));
                }

                echo "</div>";
                echo "</section>";
            }
            ?>
        </div>
    </main>

    <footer id="contact"></footer>
</body>
</html>