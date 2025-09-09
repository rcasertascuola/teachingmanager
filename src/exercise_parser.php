<?php

/**
 * Main function to parse exercise wikitext and render the appropriate HTML.
 *
 * @param Exercise $exercise The exercise object.
 * @param array|null $student_answer The student's answer data from the database.
 * @param bool $is_correction_view Is this for the teacher's correction view? (Not fully implemented yet).
 * @return string The generated HTML for the exercise.
 */
function parse_exercise_wikitext($exercise, $student_answer = null, $is_correction_view = false)
{
    if (!$exercise) {
        return '<p class="text-danger">Errore: Esercizio non valido.</p>';
    }

    switch ($exercise->type) {
        case 'multiple_choice':
            return render_multiple_choice($exercise, $student_answer, $is_correction_view);
        case 'open_answer':
            return render_open_answer($exercise, $student_answer, $is_correction_view);
        case 'fill_in_the_blanks':
            return render_fill_in_the_blanks($exercise, $student_answer, $is_correction_view);
        default:
            return '<p class="text-danger">Tipo di esercizio non supportato.</p>';
    }
}

/**
 * Renders a multiple-choice exercise.
 */
function render_multiple_choice($exercise, $student_answer = null, $is_correction_view = false)
{
    $content = $exercise->content;
    preg_match('/\[question\](.*?)\[\/question\]/s', $content, $question_matches);
    $question = trim($question_matches[1] ?? 'Domanda non trovata.');

    preg_match('/\[options\](.*?)\[\/options\]/s', $content, $options_matches);
    $options_text = $options_matches[1] ?? '';
    $options_lines = explode("\n", trim($options_text));

    $is_answered = $student_answer !== null;
    $student_choices = $is_answered ? ($student_answer['answer']['options'] ?? []) : [];

    $options_data = [];
    foreach ($options_lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        $is_correct_answer = preg_match('/^\(\s*[x*]\s*\)/', $line);
        $option_text = trim(preg_replace('/^\(\s*[x*]?\s*\)/', '', $line));
        $options_data[] = ['text' => $option_text, 'is_correct' => $is_correct_answer];
    }

    // Determine if auto-correction is enabled
    $exercise_options = json_decode($exercise->options, true);
    $show_correction = $is_answered && ($exercise_options['correction_type'] ?? 'teacher') === 'student';

    $html = '<p class="fw-bold">' . htmlspecialchars($question) . '</p>';
    $html .= '<div class="list-group">';

    foreach ($options_data as $option) {
        $option_text = $option['text'];
        $is_correct_answer = $option['is_correct'];
        $is_checked_by_student = in_array($option_text, $student_choices);

        $li_class = 'list-group-item';
        $feedback_icon = '';

        if ($show_correction) {
            if ($is_checked_by_student && $is_correct_answer) {
                $li_class .= ' list-group-item-success'; // Correctly chosen
                $feedback_icon = ' <i class="bi bi-check-circle-fill text-success"></i>';
            } elseif ($is_checked_by_student && !$is_correct_answer) {
                $li_class .= ' list-group-item-danger'; // Incorrectly chosen
                $feedback_icon = ' <i class="bi bi-x-circle-fill text-danger"></i>';
            } elseif (!$is_checked_by_student && $is_correct_answer) {
                $li_class .= ' list-group-item-warning'; // Missed correct answer
                $feedback_icon = ' <span class="text-muted fst-italic">(Risposta corretta)</span>';
            }
        }

        $html .= '<label class="' . $li_class . '">';
        $html .= '<input class="form-check-input me-1" type="checkbox" name="answer[options][]" value="' . htmlspecialchars($option_text) . '"';
        if ($is_checked_by_student) $html .= ' checked';
        if ($is_answered) $html .= ' disabled';
        $html .= '>';
        $html .= ' ' . htmlspecialchars($option_text);
        $html .= $feedback_icon;
        $html .= '</label>';
    }

    $html .= '</div>';
    return $html;
}

/**
 * Renders an open-answer exercise.
 */
function render_open_answer($exercise, $student_answer = null, $is_correction_view = false)
{
    $content = $exercise->content;
    preg_match('/\[question\](.*?)\[\/question\]/s', $content, $question_matches);
    $question = trim($question_matches[1] ?? 'Domanda non trovata.');

    $is_answered = $student_answer !== null;
    $student_text = $is_answered ? ($student_answer['answer']['text'] ?? '') : '';

    $html = '<p class="fw-bold">' . htmlspecialchars($question) . '</p>';
    $html .= '<textarea class="form-control" name="answer[text]" rows="8"';
    if ($is_answered) {
        $html .= ' disabled';
    }
    $html .= '>' . htmlspecialchars($student_text) . '</textarea>';

    return $html;
}

/**
 * Renders a fill-in-the-blanks exercise.
 */
function render_fill_in_the_blanks($exercise, $student_answer = null, $is_correction_view = false)
{
    $content = $exercise->content;
    preg_match('/\[blanks\](.*?)\[\/blanks\]/s', $content, $blanks_matches);
    $blanks_text = $blanks_matches[1] ?? '';
    $main_text = trim(str_replace($blanks_matches[0] ?? '', '', $content));

    $correct_answers = [];
    if (!empty($blanks_text)) {
        $blanks_lines = explode("\n", trim($blanks_text));
        foreach ($blanks_lines as $line) {
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $correct_answers[trim($parts[0])] = trim($parts[1]);
            }
        }
    }

    $is_answered = $student_answer !== null;
    $student_answers = $is_answered ? ($student_answer['answer']['blanks'] ?? []) : [];

    $exercise_options = json_decode($exercise->options, true);
    $show_correction = $is_answered && ($exercise_options['correction_type'] ?? 'teacher') === 'student';

    $rendered_text = preg_replace_callback('/__(\d+)__/', function($matches) use ($student_answers, $correct_answers, $is_answered, $show_correction) {
        $blank_num = $matches[1];
        $student_value = $student_answers[$blank_num] ?? '';

        $input_html = '<input type="text" name="answer[blanks]['.$blank_num.']" value="'.htmlspecialchars($student_value).'" style="width: 150px; display: inline-block;"';

        $input_class = 'form-control form-control-sm';
        if ($show_correction) {
            $correct_answer_for_blank = $correct_answers[$blank_num] ?? null;
            if ($correct_answer_for_blank !== null && strcasecmp(trim($student_value), $correct_answer_for_blank) == 0) {
                $input_class .= ' is-valid';
            } else {
                $input_class .= ' is-invalid';
            }
        }

        if ($is_answered) {
            $input_html .= ' disabled';
        }

        $input_html .= ' class="' . $input_class . '">';

        if ($show_correction && (isset($correct_answers[$blank_num]) && strcasecmp(trim($student_value), $correct_answers[$blank_num]) != 0)) {
            $input_html .= ' <span class="text-success small">('.htmlspecialchars($correct_answers[$blank_num]).')</span>';
        }

        return $input_html;
    }, $main_text);

    return '<div class="fill-in-the-blanks" style="line-height: 2.5;">' . nl2br($rendered_text) . '</div>';
}
