<?php
    include_once "../module/bootstrap.php";
    include_once MODULE . "/helpers.php";

    if (!isset($_GET['category']) && !isset($_GET['qualification'])) {
        RedirectToAppHome();
    }

    $requestedCount = isset($_GET['count']) ? (int)$_GET['count'] : 20;
    if ($requestedCount < 1) {
        $requestedCount = 1;
    }
    if ($requestedCount > 100) {
        $requestedCount = 100;
    }

    include MODEL . "/init.php";

    function FetchCategoryByKey(string $key): ?array {
        $result = Database::Query("SELECT id, name, qualification, image FROM categories WHERE name != 'inne';");
        if (!$result) {
            return null;
        }

        $matched = null;
        while ($row = $result->fetch_assoc()) {
            if (CleanLink($row['name']) === $key) {
                $matched = $row;
                break;
            }
        }
        $result->free();

        return $matched;
    }

    function FetchQualificationByKey(string $key): ?string {
        $result = Database::Query("SELECT DISTINCT qualification FROM categories;");
        if (!$result) {
            return null;
        }

        $matched = null;
        while ($row = $result->fetch_assoc()) {
            if (CleanLink($row['qualification']) === $key) {
                $matched = $row['qualification'];
                break;
            }
        }
        $result->free();

        return $matched;
    }

    function CountQuestionsForCategory(int $categoryId): int {
        $stmt = Database::Prepare("SELECT COUNT(*) AS total FROM questions WHERE category_id = ?;");
        if (!$stmt) {
            return 0;
        }
        Database::Assign($stmt, "i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $total = 0;
        if ($result) {
            $data = $result->fetch_assoc();
            if ($data && isset($data['total'])) {
                $total = (int)$data['total'];
            }
            $result->free();
        }
        $stmt->close();
        return $total;
    }

    function FetchQuestionsForCategory(int $categoryId, int $limit): array {
        $stmt = Database::Prepare("SELECT id, question_id, question, a, b, c, d, correct, image, image_fallback FROM questions WHERE category_id = ? ORDER BY RAND() LIMIT ?;");
        if (!$stmt) {
            return [];
        }
        Database::Assign($stmt, "ii", $categoryId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $questions = [];
        if ($result) {
            $questions = $result->fetch_all(MYSQLI_ASSOC) ?: [];
            $result->free();
        }
        $stmt->close();
        return $questions;
    }

    function CountQuestionsForQualification(string $qualification): int {
        $stmt = Database::Prepare("SELECT COUNT(*) AS total FROM questions q JOIN categories c ON q.category_id = c.id WHERE c.qualification = ?;");
        if (!$stmt) {
            return 0;
        }
        Database::Assign($stmt, "s", $qualification);
        $stmt->execute();
        $result = $stmt->get_result();
        $total = 0;
        if ($result) {
            $data = $result->fetch_assoc();
            if ($data && isset($data['total'])) {
                $total = (int)$data['total'];
            }
            $result->free();
        }
        $stmt->close();
        return $total;
    }

    function FetchQuestionsForQualification(string $qualification, int $limit): array {
        $stmt = Database::Prepare("SELECT q.id, q.question_id, q.question, q.a, q.b, q.c, q.d, q.correct, q.image, q.image_fallback, c.name AS category_name FROM questions q JOIN categories c ON q.category_id = c.id WHERE c.qualification = ? ORDER BY RAND() LIMIT ?;");
        if (!$stmt) {
            return [];
        }
        Database::Assign($stmt, "si", $qualification, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $questions = [];
        if ($result) {
            $questions = $result->fetch_all(MYSQLI_ASSOC) ?: [];
            $result->free();
        }
        $stmt->close();
        return $questions;
    }

    function FetchQuestionsByIds(array $ids, string $sourceType, ?int $categoryId = null, ?string $qualification = null): array {
        $ids = array_map('intval', $ids);
        $ids = array_values(array_unique(array_filter($ids, static function ($value) {
            return $value > 0;
        })));

        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        if ($sourceType === 'category') {
            if ($categoryId === null) {
                return [];
            }

            $sql = "SELECT q.id, q.question_id, q.question, q.a, q.b, q.c, q.d, q.correct, q.image, q.image_fallback, q.category_id, c.name AS category_name, c.qualification FROM questions q JOIN categories c ON q.category_id = c.id WHERE q.id IN ($placeholders) AND q.category_id = ?;";
            $stmt = Database::Prepare($sql);
            if (!$stmt) {
                return [];
            }

            $types = str_repeat('i', count($ids)) . 'i';
            $params = array_merge($ids, [(int)$categoryId]);
        } else {
            if ($qualification === null) {
                return [];
            }

            $sql = "SELECT q.id, q.question_id, q.question, q.a, q.b, q.c, q.d, q.correct, q.image, q.image_fallback, q.category_id, c.name AS category_name, c.qualification FROM questions q JOIN categories c ON q.category_id = c.id WHERE q.id IN ($placeholders) AND c.qualification = ?;";
            $stmt = Database::Prepare($sql);
            if (!$stmt) {
                return [];
            }

            $types = str_repeat('i', count($ids)) . 's';
            $params = array_merge($ids, [$qualification]);
        }

        Database::Assign($stmt, $types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result) {
            $stmt->close();
            return [];
        }

        $fetched = [];
        while ($row = $result->fetch_assoc()) {
            $fetched[(int)$row['id']] = $row;
        }

        $result->free();
        $stmt->close();

        $ordered = [];
        foreach ($ids as $id) {
            if (isset($fetched[$id])) {
                $ordered[] = $fetched[$id];
            }
        }

        return $ordered;
    }

    $isSubmitted = (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST');
    $submittedAnswers = [];
    if ($isSubmitted && isset($_POST['answers']) && is_array($_POST['answers'])) {
        foreach ($_POST['answers'] as $questionId => $value) {
            $questionKey = (int)$questionId;
            if ($questionKey <= 0) {
                continue;
            }

            $letter = strtoupper(trim((string)$value));
            if (!in_array($letter, ['A', 'B', 'C', 'D'], true)) {
                continue;
            }

            $submittedAnswers[$questionKey] = $letter;
        }
    }

    $selectedQuestionIds = [];
    $sourceType = '';
    $sourceLabel = '';
    $contextLabel = '';
    $questions = [];
    $totalAvailable = 0;
    $category = null;
    $qualification = null;
    $categoryKey = null;
    $qualificationKey = null;

    if (isset($_GET['category'])) {
        $sourceType = 'category';
        $categoryKey = CleanLink((string)$_GET['category']);
        $category = FetchCategoryByKey($categoryKey);
        if ($category === null) {
            RedirectToAppHome();
        }

        $totalAvailable = CountQuestionsForCategory((int)$category['id']);
        if ($totalAvailable === 0) {
            RedirectToAppHome();
        }

        if ($isSubmitted) {
            $selectedQuestionIds = isset($_POST['question_ids']) && is_array($_POST['question_ids'])
                ? array_map('intval', $_POST['question_ids'])
                : [];
            $selectedQuestionIds = array_values(array_unique(array_filter($selectedQuestionIds, static function ($value) {
                return $value > 0;
            })));

            if (empty($selectedQuestionIds)) {
                RedirectToAppHome();
            }

            $questions = FetchQuestionsByIds($selectedQuestionIds, 'category', (int)$category['id'], null);
        } else {
            $limit = min($requestedCount, $totalAvailable);
            $questions = FetchQuestionsForCategory((int)$category['id'], $limit);
            $selectedQuestionIds = array_map(static function ($questionRow) {
                return (int)$questionRow['id'];
            }, $questions);
        }

        $sourceLabel = $category['name'];
        $contextLabel = 'Kategoria';
    } else {
        $sourceType = 'qualification';
        $qualificationKey = CleanLink((string)$_GET['qualification']);
        $qualification = FetchQualificationByKey($qualificationKey);
        if ($qualification === null) {
            RedirectToAppHome();
        }

        $totalAvailable = CountQuestionsForQualification($qualification);
        if ($totalAvailable === 0) {
            RedirectToAppHome();
        }

        if ($isSubmitted) {
            $selectedQuestionIds = isset($_POST['question_ids']) && is_array($_POST['question_ids'])
                ? array_map('intval', $_POST['question_ids'])
                : [];
            $selectedQuestionIds = array_values(array_unique(array_filter($selectedQuestionIds, static function ($value) {
                return $value > 0;
            })));

            if (empty($selectedQuestionIds)) {
                RedirectToAppHome();
            }

            $questions = FetchQuestionsByIds($selectedQuestionIds, 'qualification', null, $qualification);
        } else {
            $limit = min($requestedCount, $totalAvailable);
            $questions = FetchQuestionsForQualification($qualification, $limit);
            $selectedQuestionIds = array_map(static function ($questionRow) {
                return (int)$questionRow['id'];
            }, $questions);
        }

        $sourceLabel = $qualification;
        $contextLabel = 'Kwalifikacja';
    }

    if (empty($questions)) {
        RedirectToAppHome();
    }

    if ($isSubmitted && count($questions) !== count($selectedQuestionIds)) {
        RedirectToAppHome();
    }

    $questionCount = count($questions);

    $resultTotals = [
        'correct' => 0,
        'incorrect' => 0,
        'unanswered' => 0,
        'percentage' => 0
    ];

    if ($isSubmitted) {
        $correctCount = 0;
        $incorrectCount = 0;
        $unansweredCount = 0;

        foreach ($questions as &$question) {
            $questionId = (int)$question['id'];
            $correctLetter = strtoupper(trim((string)($question['correct'] ?? '')));
            if (!in_array($correctLetter, ['A', 'B', 'C', 'D'], true)) {
                $correctLetter = 'A';
            }

            $question['correct_letter'] = $correctLetter;

            $userAnswer = $submittedAnswers[$questionId] ?? '';
            $question['user_answer'] = $userAnswer;
            $question['is_correct'] = false;

            if ($userAnswer === '') {
                $unansweredCount++;
                continue;
            }

            if ($userAnswer === $correctLetter) {
                $correctCount++;
                $question['is_correct'] = true;
            } else {
                $incorrectCount++;
            }
        }
        unset($question);

        $resultTotals['correct'] = $correctCount;
        $resultTotals['incorrect'] = $incorrectCount;
        $resultTotals['unanswered'] = $unansweredCount;
        $resultTotals['percentage'] = $questionCount > 0 ? round(($correctCount / $questionCount) * 100, 2) : 0;
    }

    $percentageLabel = number_format($resultTotals['percentage'], 2, ',', ' ');
    $retakeLink = '';
    if ($sourceType === 'category' && $categoryKey !== null) {
        $retakeLink = RELATIVE_PATH_APP . "/view/exam?category=" . urlencode($categoryKey) . "&count=" . $requestedCount;
    }
    if ($sourceType === 'qualification' && $qualificationKey !== null) {
        $retakeLink = RELATIVE_PATH_APP . "/view/exam?qualification=" . urlencode($qualificationKey) . "&count=" . $requestedCount;
    }

    $selectionLink = '';
    if ($sourceType === 'category' && $categoryKey !== null) {
        $selectionLink = RELATIVE_PATH_APP . "/view/category?name=" . urlencode($categoryKey);
    }
    if ($sourceType === 'qualification' && $qualificationKey !== null) {
        $selectionLink = RELATIVE_PATH_APP . "/view/qualification?name=" . urlencode($qualificationKey);
    }

    $shouldShowTopActions = $isSubmitted && $questionCount > 1;
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <title>Test ~ <?php echo htmlspecialchars($sourceLabel, ENT_QUOTES, 'UTF-8'); ?></title>
    <?php
        include_once "../module/bootstrap.php";
        include MODULE . "/common_head.html";
    ?>
</head>
<body class="exam-page">
    <?php 
        include_once MODULE . "/header.php";
        echo Common_Header();
    ?>

    <main class="container">
        <section class="exam-summary">
            <header class="exam-summary-header">
                <h1 class="display-5 fw-bold text-gradient">Egzamin próbny</h1>
                <p><?php echo htmlspecialchars($contextLabel, ENT_QUOTES, 'UTF-8'); ?>: <span class="fw-bold"><?php echo htmlspecialchars($sourceLabel, ENT_QUOTES, 'UTF-8'); ?></span></p>
                <p>Liczba pytań: <span class="fw-bold"><?php echo $questionCount; ?></span></p>
                <?php if ($sourceType === 'category' && isset($category['qualification'])): ?>
                    <p>Kwalifikacja: <span class="fw-bold"><?php echo htmlspecialchars($category['qualification'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                <?php endif; ?>
                <?php if (!$isSubmitted && $questionCount < $requestedCount): ?>
                    <p>Dostępnych pytań w bazie: <span class="fw-bold"><?php echo $totalAvailable; ?></span></p>
                <?php endif; ?>
            </header>

            <?php if ($isSubmitted): ?>
                <div id="result-summary"
                     class="exam-result exam-summary-result"
                     data-correct="<?php echo (int)$resultTotals['correct']; ?>"
                     data-incorrect="<?php echo (int)$resultTotals['incorrect']; ?>"
                     data-unanswered="<?php echo (int)$resultTotals['unanswered']; ?>"
                     data-total="<?php echo $questionCount; ?>">
                    <div class="result-statistics">
                        <p class="mb-1">Poprawne odpowiedzi: <span class="fw-bold text-success"><?php echo (int)$resultTotals['correct']; ?></span></p>
                        <p class="mb-1">Niepoprawne odpowiedzi: <span class="fw-bold text-danger"><?php echo (int)$resultTotals['incorrect']; ?></span></p>
                        <p class="mb-2">Bez odpowiedzi: <span class="fw-bold text-warning"><?php echo (int)$resultTotals['unanswered']; ?></span></p>
                        <p class="mb-0 result-score">Wynik: <span class="fw-bold"><?php echo $percentageLabel; ?>%</span></p>
                    </div>
                    <canvas id="result-chart" width="320" height="320" role="img" aria-label="Wykres wyników"></canvas>
                </div>

                <?php if ($shouldShowTopActions): ?>
                    <div class="exam-actions exam-actions-top">
                        <?php if ($retakeLink !== ''): ?>
                            <a href="<?php echo htmlspecialchars($retakeLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary">Nowy test</a>
                        <?php endif; ?>
                        <?php if ($selectionLink !== ''): ?>
                            <a href="<?php echo htmlspecialchars($selectionLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary">Wybierz inną liczbę pytań</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>

        <form id="exam-form" method="post">
            <?php if (!$isSubmitted): ?>
                <?php foreach ($selectedQuestionIds as $hiddenId): ?>
                    <input type="hidden" name="question_ids[]" value="<?php echo (int)$hiddenId; ?>">
                <?php endforeach; ?>
            <?php endif; ?>

            <?php foreach ($questions as $index => $question):
                $questionNumber = $index + 1;
                $questionIdentifier = isset($question['question_id']) && $question['question_id'] !== ''
                    ? str_pad((string)$question['question_id'], 3, '0', STR_PAD_LEFT)
                    : str_pad((string)$questionNumber, 3, '0', STR_PAD_LEFT);
                $rawQuestionText = (string)($question['question'] ?? '');
                $questionBody = ltrim($rawQuestionText);
                if ($questionBody !== '') {
                    if (function_exists('mb_substr')) {
                        $questionBody = mb_substr($questionBody, 4);
                    } else {
                        $questionBody = substr($questionBody, 4);
                    }
                    $questionBody = ltrim((string)$questionBody);
                }
                $questionText = nl2br(htmlspecialchars($questionBody, ENT_QUOTES, 'UTF-8'));

                $imageSource = '';
                if (!empty($question['image'])) {
                    $imageSource = $question['image'];
                } elseif (!empty($question['image_fallback'])) {
                    $imageSource = $question['image_fallback'];
                }
                $imageSource = $imageSource !== '' ? htmlspecialchars($imageSource, ENT_QUOTES, 'UTF-8') : '';

                $questionClasses = 'exam-question';
                $userAnswer = $question['user_answer'] ?? '';
                $questionIsCorrect = !empty($question['is_correct']);
                if ($isSubmitted) {
                    if ($userAnswer === '') {
                        $questionClasses .= ' question-unanswered';
                    } elseif ($questionIsCorrect) {
                        $questionClasses .= ' question-correct';
                    } else {
                        $questionClasses .= ' question-incorrect';
                    }
                }

                $correctLetter = '';
                $correctAnswerText = '';
                $optionMap = [
                    'A' => 'a',
                    'B' => 'b',
                    'C' => 'c',
                    'D' => 'd'
                ];

                if ($isSubmitted) {
                    $correctLetter = strtoupper(trim((string)($question['correct_letter'] ?? $question['correct'] ?? '')));
                    if (!in_array($correctLetter, array_keys($optionMap), true)) {
                        $correctLetter = 'A';
                    }
                    $correctField = $optionMap[$correctLetter] ?? null;
                    if ($correctField !== null && isset($question[$correctField])) {
                        $correctAnswerText = htmlspecialchars($question[$correctField], ENT_QUOTES, 'UTF-8');
                    }
                }
            ?>
            <article class="<?php echo $questionClasses; ?>">
                <div class="question-header">
                    <div>
                        <span class="question-label">#<?php echo htmlspecialchars($questionIdentifier, ENT_QUOTES, 'UTF-8'); ?></span>
                        <h2 class="h4 mb-0">Pytanie <?php echo $questionNumber; ?></h2>
                    </div>
                    <?php if ($sourceType === 'qualification' && isset($question['category_name'])): ?>
                        <span class="question-meta">Kategoria: <?php echo htmlspecialchars($question['category_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
                <div class="question-body">
                    <p><?php echo $questionText; ?></p>
                    <?php if ($imageSource !== ''): ?>
                        <img src="<?php echo $imageSource; ?>" alt="Ilustracja do pytania <?php echo $questionNumber; ?>">
                    <?php endif; ?>
                </div>

                <?php if ($isSubmitted && $correctAnswerText !== ''):
                    $answerBoxClass = 'question-answer-box';
                    if ($userAnswer === '') {
                        $answerBoxClass .= ' question-answer-missed';
                    } elseif ($questionIsCorrect) {
                        $answerBoxClass .= ' question-answer-correct';
                    } else {
                        $answerBoxClass .= ' question-answer-incorrect';
                    }
                ?>
                    <div class="<?php echo $answerBoxClass; ?>">
                        <span class="question-answer-label">Poprawna odpowiedź:</span>
                        <span class="question-answer-value"><?php echo $correctLetter; ?> &ndash; <?php echo $correctAnswerText; ?></span>
                    </div>
                <?php endif; ?>

                <div class="exam-options">
                    <?php
                        foreach ($optionMap as $label => $field):
                            if (!isset($question[$field]) || trim((string)$question[$field]) === '') {
                                continue;
                            }
                            $optionText = htmlspecialchars($question[$field], ENT_QUOTES, 'UTF-8');

                            $optionClasses = 'exam-option';
                            if ($isSubmitted) {
                                if ($userAnswer === $label) {
                                    $optionClasses .= ' is-selected';
                                    if ($questionIsCorrect && $userAnswer === $label) {
                                        $optionClasses .= ' is-correct';
                                    } else {
                                        $optionClasses .= ' is-incorrect';
                                    }
                                }
                                if ($correctLetter === $label) {
                                    $optionClasses .= ' is-answer';
                                }
                            }
                            $isChecked = ($userAnswer === $label) ? ' checked' : '';
                            $isDisabled = $isSubmitted ? ' disabled' : '';
                    ?>
                    <label class="<?php echo $optionClasses; ?>">
                        <input type="radio" name="answers[<?php echo (int)$question['id']; ?>]" value="<?php echo $label; ?>"<?php echo $isChecked; ?><?php echo $isDisabled; ?>>
                        <span><span class="option-label"><?php echo $label; ?>.</span> <?php echo $optionText; ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </article>
            <?php endforeach; ?>

            <div class="exam-actions exam-actions-bottom">
                <?php if (!$isSubmitted): ?>
                    <button type="submit" class="btn btn-primary">Sprawdź swoje odpowiedzi</button>
                <?php else: ?>
                    <?php if ($retakeLink !== ''): ?>
                        <a href="<?php echo htmlspecialchars($retakeLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary">Nowy test</a>
                    <?php endif; ?>
                    <?php if ($selectionLink !== ''): ?>
                        <a href="<?php echo htmlspecialchars($selectionLink, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary">Wybierz inną liczbę pytań</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </form>
    </main>
</body>
</html>
