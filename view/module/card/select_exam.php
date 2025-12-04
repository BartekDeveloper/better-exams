<?php

function SelectExamType(
    string $examType
): string {
    global $db;
        
    return <<<HTML
        <section>
            <div>
                <img src="" alt="" onerror="alert('Brak obrazka')">
                <h2><?= $examType ?></h2>
            </div>
            <a>Sprawdź swoją wiedzę</a>
        </section>
    HTML;
}