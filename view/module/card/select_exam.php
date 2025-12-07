<?php

function Select_Exam_Category(
    Category $data
): string {
    global $db;
        
    (string)$image = "https://placehold.co/600x400/555/CCC?text=Kategoria%20{$data->name}";
    (string)$link = RELATIVE_PATH_APP . "/view/error?code=404";

    if(!empty($data->name)) {
        $link = RELATIVE_PATH_APP . "/view/category?name=" . CleanLink($data->name);
    }

    $imageEscaped = htmlspecialchars($image, ENT_QUOTES, 'UTF-8');
    $linkEscaped = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
    $nameEscaped = htmlspecialchars($data->name, ENT_QUOTES, 'UTF-8');
    $qualificationEscaped = htmlspecialchars($data->qualification, ENT_QUOTES, 'UTF-8');
    
    return <<<HTML
        <article class="col-12 col-md-6 col-lg-4 mb-4">
            <div class="card h-100 glass clickable-card" data-href="{$linkEscaped}">
                <img src="{$imageEscaped}" class="card-img-top" alt="{$nameEscaped}" style="height: 200px; object-fit: cover;">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">{$nameEscaped}</h5>
                    <p class="card-text text-muted small">Kwalifikacja: {$qualificationEscaped}</p>
                    
                    <a href="{$linkEscaped}" class="btn btn-primary mt-auto">Sprawdź się!</a>
                </div>
            </div>
        </article> 
    HTML;
}

function Select_Exam_Whole(string $qualification): string {
    global $db;

    (string)$image = "https://placehold.co/600x400/111/EEE?text=Egzamin%20{$qualification}";
    (string)$link = RELATIVE_PATH_APP . "/view/error?code=404";

    if(!empty($qualification)) {
        $link = RELATIVE_PATH_APP . "/view/qualification?name=" . CleanLink($qualification);
    }

    $imageEscaped = htmlspecialchars($image, ENT_QUOTES, 'UTF-8');
    $linkEscaped = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
    $qualificationEscaped = htmlspecialchars($qualification, ENT_QUOTES, 'UTF-8');
    
    return <<<HTML
        <article class="col-12 col-md-6 col-lg-4 mb-4 text-center">
            <div class="card h-100 glass clickable-card" data-href="{$linkEscaped}">
                <img src="{$imageEscaped}" alt="{$qualificationEscaped}" style="height: 200px; object-fit: cover;">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">{$qualificationEscaped}</h5>
                    <p class="card-text text-muted small">Cały egzamin, wszytkie kategorie</p>
                    
                    <a href="{$linkEscaped}" class="btn btn-primary mt-auto">Sprawdź się!</a>
                </div>
            </div>
        </article>
    HTML;
}

