'use strict';

// Same rules as App\Domain\Shared\ValueObject\Cpf. The server stays the
// source of truth: this file only improves the experience while typing.

const CPF_LENGTH = 11;

function onlyDigits(value) {
    return value.replace(/\D/g, '');
}

function maskCpf(value) {
    return onlyDigits(value)
        .slice(0, CPF_LENGTH)
        .replace(/^(\d{3})(\d)/, '$1.$2')
        .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/\.(\d{3})(\d{1,2})$/, '.$1-$2');
}

function checkDigit(base) {
    let weight = base.length + 1;
    let sum = 0;

    for (const digit of base) {
        sum += Number(digit) * weight--;
    }

    const remainder = sum % 11;

    return remainder < 2 ? 0 : 11 - remainder;
}

function isValidCpf(value) {
    const digits = onlyDigits(value);

    if (digits.length !== CPF_LENGTH || /^(\d)\1+$/.test(digits)) {
        return false;
    }

    return checkDigit(digits.slice(0, 9)) === Number(digits[9])
        && checkDigit(digits.slice(0, 10)) === Number(digits[10]);
}

function setupCpfInput(input) {
    const feedback = document.getElementById(input.getAttribute('aria-describedby'));

    const showFeedback = (message, valid) => {
        feedback.textContent = message;
        feedback.classList.toggle('field__feedback--valid', valid);
        input.setAttribute('aria-invalid', String(!valid));
    };

    input.addEventListener('input', () => {
        input.value = maskCpf(input.value);
        const digits = onlyDigits(input.value);

        if (digits.length < CPF_LENGTH) {
            feedback.textContent = '';
            input.removeAttribute('aria-invalid');
        } else if (isValidCpf(digits)) {
            showFeedback('CPF válido.', true);
        } else {
            showFeedback('CPF inválido. Verifique os números digitados.', false);
        }
    });
}

// The search field accepts names or CPFs: the mask applies only while the
// user types nothing but digits and CPF punctuation.
function setupCpfSearch(input) {
    input.addEventListener('input', () => {
        if (/^[\d.\-\s]+$/.test(input.value)) {
            input.value = maskCpf(input.value);
        }
    });
}

document.querySelectorAll('[data-cpf-input]').forEach(setupCpfInput);
document.querySelectorAll('[data-cpf-search]').forEach(setupCpfSearch);
