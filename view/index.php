<!DOCTYPE html>
<html lang="pl">
<head>
    <title>Wybierz egzamin ~ Egzaminy Inf.03 i inf.04</title>
    <?php
        include "module/bootstrap.php";
        include "module/common_head.html";
        include "module/card/select_exam.php";
        include ROOT."/model/database.php";
    ?>
</head>
<body>
    <header>
        <h2>Egzaminy</h2>
        
        <div></div>
        
        <nav>
            <ul>
                <li><a href=""></a></li>
                <li><a href=""></a></li>
                <li><a href=""></a></li>
                <li><a href=""></a></li>
                <li><a href=""></a></li>
            </ul>
        </nav>
    </header>
    
    <aside>
        <button onClick="window.scrollTo(window.top)">&UpArrow;</button>
    </aside>

    <main>
        <h1>Wybierz egzamin</h1>

        <?php
            if(!isset($db) || empty($db)) {
                $db = new Database();
            }

            $result = Database::Query("SELECT DISTINCT category_name FROM questions;");
            
            while($row = mysqli_fetch_array($result)) {
                echo SelectExamType($row[0]);
            }
            ?>
    </main>

    <footer></footer>
</body>
</html>