<?php
function Common_Header(): string
{
    $home = function_exists('AppHomePath') ? AppHomePath() : '/';
    $homeEscaped = htmlspecialchars($home, ENT_QUOTES, 'UTF-8');
    $examsLink = htmlspecialchars($home . '#exams', ENT_QUOTES, 'UTF-8');
    $contactLink = htmlspecialchars($home . '#contact', ENT_QUOTES, 'UTF-8');

    return <<<HTML
    <nav id="main-nav" class="glass">
        <div class="container-fluid nav-container">
            <div class="nav-bar">
                <a href="{$homeEscaped}" class="nav-brand">Teoria</a>
                <button class="nav-toggle" id="nav-toggle" type="button" aria-expanded="false" aria-controls="nav-links">
                    <span class="nav-toggle-box" aria-hidden="true">
                        <span class="nav-toggle-line"></span>
                        <span class="nav-toggle-line"></span>
                        <span class="nav-toggle-line"></span>
                    </span>
                    <span class="nav-toggle-text">Menu</span>
                </button>
            </div>
            <div class="nav-links gap-2" id="nav-links">
                <a href="{$homeEscaped}" class="nav-item">Start</a>
                <a href="{$examsLink}" class="nav-item">Egzaminy</a>
                <a href="{$contactLink}" class="nav-item">Kontakt</a>
            </div>
        </div>
    </nav>
HTML;
}
