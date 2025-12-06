<?php

include MODEL."tables.php";
use Category;

function SelectExamType(
    Category $data
): string {
    global $db;
        
    return <<<HTML
        <section>
            <div>
                <img src="{$data->name}" alt="{$data->name}" />
                <h2>{$data->name}</h2>
            </div>
            <a href="{$data->name}">Sprawdź swoją wiedzę</a>
        </section>
    HTML;
}