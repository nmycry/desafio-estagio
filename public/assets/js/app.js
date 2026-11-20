'use strict';

// Everything here is progressive enhancement: the server validates every
// field again and remains the source of truth.

const CPF_LENGTH = 11;
const IBGE_STATES_URL = 'https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome';

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

// Same algorithm as App\Domain\Shared\ValueObject\Cpf.
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

// The message below the field shows its help text, or the error when there is one.
function fieldMessage(control) {
    return document.getElementById(control.getAttribute('aria-describedby'));
}

function showFieldError(control, error) {
    control.closest('.field').classList.add('field--error');
    control.setAttribute('aria-invalid', 'true');
    fieldMessage(control).textContent = error;
}

function clearFieldError(control) {
    const message = fieldMessage(control);

    control.closest('.field').classList.remove('field--error');
    control.removeAttribute('aria-invalid');
    message.textContent = message.dataset.help;
}

function setupCpfInput(input) {
    input.addEventListener('input', () => {
        input.value = maskCpf(input.value);
        const digits = onlyDigits(input.value);

        if (digits.length === CPF_LENGTH && !isValidCpf(digits)) {
            showFieldError(input, 'CPF inválido. Verifique os números digitados.');
        } else {
            clearFieldError(input);
        }
    });
}

// The search field accepts names or CPFs: the mask applies only while the
// user types nothing but digits and CPF punctuation. Clearing the field after
// a search goes back to the full list (submitting it empty is a validation error).
function setupSearchInput(input) {
    const searched = new URLSearchParams(window.location.search).has(input.name);

    input.addEventListener('input', () => {
        if (input.value === '' && searched) {
            window.location.assign(input.form.action);
        } else if (/^[\d.\-\s]+$/.test(input.value)) {
            input.value = maskCpf(input.value);
        }
    });
}

// Fills the select with the states from the IBGE Localidades API and
// restores the option chosen before a failed submission (data-selected).
async function setupStatesSelect(select) {
    select.addEventListener('change', () => clearFieldError(select));

    try {
        const response = await fetch(IBGE_STATES_URL);

        if (!response.ok) {
            throw new Error(`IBGE API responded with ${response.status}`);
        }

        for (const state of await response.json()) {
            select.add(new Option(state.nome, state.sigla));
        }

        select.value = select.dataset.selected;
    } catch {
        showFieldError(select, 'Não foi possível carregar os estados. Recarregue a página.');
    }
}

document.querySelectorAll('[data-cpf-input]').forEach(setupCpfInput);
document.querySelectorAll('[data-search-input]').forEach(setupSearchInput);
document.querySelectorAll('[data-states-select]').forEach(setupStatesSelect);
