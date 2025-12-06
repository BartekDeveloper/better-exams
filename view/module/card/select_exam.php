<?php

function SelectExamType(
    Category $data
): string {
    global $db;
        
    (string)$image = "https://placehold.co/600x400/EEE/31343C?text=Kategoria+{$data->id}";

    if(!empty($data->image)) {
        $image = ASSET_IMG . "/category/" . $data->image;
    }

    return <<<HTML
        <div class="col-12 col-md-6 col-lg-4 mb-4">
            <div class="card h-100 glass">
                <img src="{$image}" class="card-img-top" alt="{$data->name}" style="height: 200px; object-fit: cover;">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">{$data->name}</h5>
                    <p class="card-text text-muted small">Kwalifikacja: {$data->qualification}</p>
                    <a href="/category/{$data->id}" class="btn btn-primary mt-auto stretched-link">Sprawdź swoją wiedzę</a>
                </div>
            </div>
        </div>
    HTML;
}