<?php
function Select_Question_Count_Card(int $count, string $name, string $scope = 'category'): string {
    $encodedName = urlencode($name);
    $link = RELATIVE_PATH_APP . "/exam?{$scope}={$encodedName}&count={$count}";
    $linkEscaped = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
    $countEscaped = htmlspecialchars((string)$count, ENT_QUOTES, 'UTF-8');
    return <<<HTML
        <article class="col-12 col-md-4 col-lg-3 mb-4">
            <div class="card h-100 glass clickable-card" data-href="{$linkEscaped}">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <h5 class="card-title display-1 fw-bold">{$countEscaped}</h5>
                    <p class="card-text text-muted">pytań</p>
                    <a href="{$linkEscaped}" class="btn btn-primary mt-3">Start</a>
                </div>
            </div>
        </article>
    HTML;
}
?>