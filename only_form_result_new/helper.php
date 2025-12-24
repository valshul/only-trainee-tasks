<?php
define(
    "TEL_PARAM",
    'type="tel"
    x-autocompletetype="phone-full"
    data-inputmask="\'mask\': \'+79999999999\', \'clearIncomplete\': \'true\'"'
);

function generateHeaderWithClass(string $formHeader, string $className) : string
{
    $headerWithClass = implode(
        ' class="' . $className . '" ',
        preg_split("/\s/", $formHeader, 2)
    );
    return $headerWithClass;
}
function isInputError(string $sid, array $question, string $errors) : bool
{
    $preg = "/(" . $sid . "|" . preg_quote($question["CAPTION"], "/") . ")/i";
    if (preg_match($preg, $errors) == 1) {
        return true;
    }
    return false;
}
function generateInput(string $sid, array $question, string $errors) : string
{
    $inputType = $question["STRUCTURE"][0]["FIELD_TYPE"];
    $isTelType = preg_match("/phone/i", $sid) == 1;
    $inputHTML =
        '
        <div class="input' . ($inputType != "textarea" ? " contact-form__input" : "") . '">
            <label class="input__label" for="' . $sid . '">' .
                '<div class="input__label-text">' .
                    $question["CAPTION"] .
                    ($question["REQUIRED"] == "Y" ? "*" : "") .
                '</div>' .
                '<' . ($inputType == "textarea" ? "textarea" : "input") .
                    ' class="input__input" ' .
                    ($isTelType ? TEL_PARAM : 'type="text"') .
                    ' id="' . $sid . '"' .
                    ' name="form_' . $inputType . "_" . $question["STRUCTURE"][0]["QUESTION_ID"] . '"' .
                    ' value="' . $question["STRUCTURE"][0]["VALUE"] . '"' .
                    ($question["REQUIRED"] == "Y" ? " required" : "") .
                    ($inputType == "textarea" ? "></textarea>" : "/>") .
                '<div class="input__notification"' .
                    (isInputError($sid, $question, $errors) ? ' style="visibility: visible; opacity: 100%"' : "") .
                    '>' .
                    $question["STRUCTURE"][0]["MESSAGE"] .
                '</div>
            </label>
        </div>
        ';
    return $inputHTML;
}
function generateAgreement(string $buttonText) : string
{
    $agreement =
        'Нажимая &laquo;' .
        $buttonText .
        '&raquo;, Вы подтверждаете, что ознакомлены,
        полностью согласны и принимаете условия
        &laquo;Согласия на обработку персональных данных&raquo;.';
    return $agreement;
}