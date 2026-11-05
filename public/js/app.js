/*
 * Sistema de Gestión Académica — comportamiento de la interfaz.
 * Sin dependencias aparte de Bootstrap 5 (bundle). Sin scripts inline.
 * Importante: aquí no se calcula ninguna nota. Total, promedio y estado vienen
 * solo del servidor (GradeCalculator). Esta validación solo complementa la del servidor.
 */
(function () {
    'use strict';

    const bs = window.bootstrap;
    const root = document.documentElement;
    const STORAGE_KEY = 'sga.sidebar.collapsed';
    const PARTIAL_RE = /^\d{1,2}([.,]\d{1,2})?$/;

    /* Sidebar ---------------------------------------------------------------- */
    const sidebar = document.getElementById('sidebar');
    let sidebarTooltips = [];

    function storedCollapsed() {
        try { return window.localStorage.getItem(STORAGE_KEY) === '1'; } catch (e) { return false; }
    }

    function setCollapsed(collapsed, persist) {
        root.classList.toggle('sidebar-collapsed', collapsed);
        document.querySelectorAll('[data-sidebar-toggle]').forEach(function (btn) {
            btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            btn.setAttribute('aria-label', collapsed ? 'Expandir menú' : 'Contraer menú');
            const icon = btn.querySelector('.bi');
            if (icon) { icon.className = 'bi ' + (collapsed ? 'bi-layout-sidebar' : 'bi-layout-sidebar-inset'); }
        });
        sidebarTooltips.forEach(function (t) { t.dispose(); });
        sidebarTooltips = [];
        if (collapsed && bs && sidebar) {
            sidebar.querySelectorAll('[data-sidebar-tooltip]').forEach(function (el) {
                sidebarTooltips.push(new bs.Tooltip(el, { title: el.dataset.sidebarTooltip, placement: 'right', trigger: 'hover focus' }));
            });
        }
        if (persist) {
            try { window.localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0'); } catch (e) { /* almacenamiento no disponible */ }
        }
    }

    if (sidebar) {
        setCollapsed(storedCollapsed(), false);
        window.requestAnimationFrame(function () { root.classList.add('sidebar-ready'); });

        document.querySelectorAll('[data-sidebar-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setCollapsed(!root.classList.contains('sidebar-collapsed'), true);
            });
        });

        // Móvil: el Offcanvas se cierra al navegar.
        sidebar.addEventListener('click', function (e) {
            if (e.target.closest('a.sidebar-link') && bs) {
                const oc = bs.Offcanvas.getInstance(sidebar);
                if (oc) { oc.hide(); }
            }
        });
    }

    /* Tooltips de acciones y toasts ----------------------------------------- */
    if (bs) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) { new bs.Tooltip(el); });
        document.querySelectorAll('.toast-stack .toast').forEach(function (el) { bs.Toast.getOrCreateInstance(el).show(); });
    }

    function showToast(message, variant) {
        const stack = document.querySelector('.toast-stack');
        if (!stack || !bs) { return; }
        const danger = variant === 'danger';
        const toast = document.createElement('div');
        toast.className = 'toast toast-' + (danger ? 'danger' : 'success');
        toast.setAttribute('role', danger ? 'alert' : 'status');
        toast.setAttribute('aria-live', danger ? 'assertive' : 'polite');
        toast.setAttribute('aria-atomic', 'true');
        toast.dataset.bsAutohide = danger ? 'false' : 'true';
        toast.dataset.bsDelay = '5000';
        const body = document.createElement('div');
        body.className = 'toast-body';
        const icon = document.createElement('i');
        icon.className = 'bi toast-icon ' + (danger ? 'bi-exclamation-octagon-fill' : 'bi-check-circle-fill');
        icon.setAttribute('aria-hidden', 'true');
        const text = document.createElement('div');
        text.className = 'flex-grow-1';
        text.textContent = message;
        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'btn-close';
        close.setAttribute('data-bs-dismiss', 'toast');
        close.setAttribute('aria-label', 'Cerrar');
        body.append(icon, text, close);
        toast.append(body);
        stack.append(toast);
        bs.Toast.getOrCreateInstance(toast).show();
    }

    /* Mostrar / ocultar contraseña ----------------------------------------- */
    document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById(btn.dataset.passwordToggle);
            if (!input) { return; }
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            btn.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
            const icon = btn.querySelector('.bi');
            if (icon) { icon.className = 'bi ' + (show ? 'bi-eye-slash' : 'bi-eye'); }
        });
    });

    /* Imprimir --------------------------------------------------------------- */
    document.querySelectorAll('[data-print]').forEach(function (btn) {
        btn.addEventListener('click', function () { window.print(); });
    });

    /* Validación complementaria en el cliente ------------------------------- */
    function feedbackFor(el) {
        const holder = el.closest('.input-group') || el;
        let fb = holder.parentElement.querySelector('.js-feedback[data-for="' + el.name + '"]');
        if (!fb) {
            fb = document.createElement('div');
            fb.className = 'invalid-feedback js-feedback';
            fb.dataset.for = el.name;
            holder.insertAdjacentElement('afterend', fb);
        }
        return fb;
    }

    function messageFor(el) {
        const v = el.validity;
        if (v.valueMissing) { return 'Este campo es obligatorio.'; }
        if (v.typeMismatch && el.type === 'email') { return 'Ingrese un correo válido.'; }
        if (v.tooLong) { return 'Máximo ' + el.maxLength + ' caracteres.'; }
        if (v.patternMismatch && el.dataset.patternMessage) { return el.dataset.patternMessage; }
        return el.validationMessage;
    }

    function validateField(el) {
        if (!el.willValidate || el.type === 'hidden') { return true; }
        const ok = el.checkValidity();
        el.classList.toggle('is-invalid', !ok);
        const fb = feedbackFor(el);
        fb.textContent = ok ? '' : messageFor(el);
        fb.classList.toggle('d-block', !ok);
        return ok;
    }

    function validateForm(form) {
        let firstInvalid = null;
        Array.from(form.elements).forEach(function (el) {
            if (el.matches('input, select, textarea') && !validateField(el) && !firstInvalid) { firstInvalid = el; }
        });
        if (firstInvalid) { firstInvalid.focus(); }
        return !firstInvalid;
    }

    document.addEventListener('input', function (e) {
        const el = e.target;
        if (el.form && el.form.matches('[data-validate]') && el.classList.contains('is-invalid')) { validateField(el); }
    });

    /* Spinner al enviar ------------------------------------------------------ */
    function setLoading(form, submitter) {
        const btn = submitter || form.querySelector('[type="submit"]') ||
            (form.id ? document.querySelector('[type="submit"][form="' + form.id + '"]') : null);
        if (!btn || btn.classList.contains('is-loading')) { return; }
        btn.classList.add('is-loading');
        btn.setAttribute('aria-disabled', 'true');
        btn.dataset.originalHtml = btn.innerHTML;
        const spinner = document.createElement('span');
        spinner.className = 'spinner-border me-1';
        spinner.setAttribute('aria-hidden', 'true');
        btn.prepend(spinner);
        // Se deshabilita después del envío para que el valor del botón viaje si lo tiene.
        window.setTimeout(function () { btn.disabled = true; }, 0);
    }

    window.addEventListener('pageshow', function () {
        document.querySelectorAll('.is-loading').forEach(function (btn) {
            btn.classList.remove('is-loading');
            btn.removeAttribute('aria-disabled');
            btn.disabled = false;
            if (btn.dataset.originalHtml) { btn.innerHTML = btn.dataset.originalHtml; }
        });
    });

    /* Modal de confirmación reutilizable ------------------------------------ */
    const confirmEl = document.getElementById('confirm-modal');
    let pending = null;

    function askConfirm(form, submitter) {
        if (!confirmEl || !bs) { return false; }
        const danger = form.dataset.confirmVariant === 'danger';
        confirmEl.querySelector('[data-confirm-slot="title"]').textContent = form.dataset.confirmTitle || '¿Confirmar la acción?';
        confirmEl.querySelector('[data-confirm-slot="message"]').textContent = form.dataset.confirmMessage || '';
        confirmEl.querySelector('[data-confirm-slot="item"]').textContent = form.dataset.confirmItem || '';
        const icon = confirmEl.querySelector('[data-confirm-slot="icon"]');
        icon.className = 'bi ' + (danger ? 'bi-exclamation-triangle-fill text-danger' : 'bi-question-circle-fill text-primary');
        const ok = confirmEl.querySelector('[data-confirm-accept]');
        ok.className = 'btn ' + (danger ? 'btn-danger' : 'btn-primary');
        ok.textContent = form.dataset.confirmButton || (danger ? 'Eliminar' : 'Confirmar');
        pending = { form: form, submitter: submitter };
        bs.Modal.getOrCreateInstance(confirmEl).show();
        return true;
    }

    if (confirmEl && bs) {
        confirmEl.addEventListener('shown.bs.modal', function () { confirmEl.querySelector('[data-confirm-accept]').focus(); });
        confirmEl.addEventListener('hidden.bs.modal', function () { pending = null; });
        confirmEl.querySelector('[data-confirm-accept]').addEventListener('click', function () {
            if (!pending) { return; }
            const p = pending;
            p.form.dataset.confirmed = '1';
            bs.Modal.getInstance(confirmEl).hide();
            if (p.form.requestSubmit) { p.form.requestSubmit(p.submitter || undefined); } else { p.form.submit(); }
        });
    }

    /* Planilla de calificaciones -------------------------------------------- */
    const sheet = document.querySelector('[data-grade-sheet]');
    const dirtyRows = new Set();

    function rowOf(input) { return document.querySelector('tr[data-grade-row="' + input.getAttribute('form') + '"]'); }

    function partialError(value) {
        const v = value.trim();
        if (v === '') { return null; } // vacío = pendiente (NULL)
        if (!PARTIAL_RE.test(v)) { return 'número de 0 a 20 con máximo dos decimales'; }
        if (parseFloat(v.replace(',', '.')) > 20) { return 'no puede ser mayor que 20'; }
        return null;
    }

    function checkRow(row) {
        const formId = row.dataset.gradeRow;
        const inputs = document.querySelectorAll('input.grade-input[form="' + formId + '"]');
        const messages = [];
        let dirty = false;
        inputs.forEach(function (input) {
            const err = partialError(input.value);
            input.classList.toggle('is-invalid', !!err);
            input.setAttribute('aria-invalid', err ? 'true' : 'false');
            if (err) { messages.push(input.dataset.label + ': ' + err + '.'); }
            if (input.value.trim() !== input.dataset.original) { dirty = true; }
        });
        row.classList.toggle('is-dirty', dirty);
        if (dirty) { dirtyRows.add(formId); } else { dirtyRows.delete(formId); }
        const btn = document.querySelector('button[form="' + formId + '"]');
        if (btn) {
            btn.classList.toggle('btn-primary', dirty);
            btn.classList.toggle('btn-outline-primary', !dirty);
        }
        const box = document.getElementById('msg-' + formId);
        if (box) {
            const cell = box.querySelector('[data-client-messages]');
            cell.textContent = messages.join(' ');
            const hasServer = !!box.querySelector('[data-server-messages]');
            box.classList.toggle('d-none', messages.length === 0 && !hasServer);
        }
        return messages.length === 0;
    }

    if (sheet) {
        sheet.addEventListener('input', function (e) {
            if (e.target.matches('input.grade-input')) {
                const row = rowOf(e.target);
                if (row) { checkRow(row); }
            }
        });

        window.addEventListener('beforeunload', function (e) {
            if (dirtyRows.size > 0) { e.preventDefault(); e.returnValue = ''; }
        });
    }

    /* Corrección del administrador: vista previa antes de enviar ------------- */
    const correctionEl = document.getElementById('correction-modal');

    function askCorrection(form) {
        if (!correctionEl || !bs) { return false; }
        const tbody = correctionEl.querySelector('[data-compare-body]');
        tbody.replaceChildren();
        form.querySelectorAll('input[data-old]').forEach(function (input) {
            const tr = document.createElement('tr');
            const label = document.createElement('td');
            label.textContent = input.dataset.label;
            const before = document.createElement('td');
            before.textContent = input.dataset.old === '' ? '—' : input.dataset.old;
            const after = document.createElement('td');
            const v = input.value.trim();
            after.textContent = v === '' ? '—' : v;
            if (v.replace(',', '.') !== input.dataset.old.replace(',', '.')) { after.classList.add('changed'); }
            tr.append(label, before, after);
            tbody.append(tr);
        });
        correctionEl.querySelector('[data-compare-reason]').textContent = form.querySelector('[name="reason"]').value.trim();
        pending = { form: form, submitter: null };
        bs.Modal.getOrCreateInstance(correctionEl).show();
        return true;
    }

    if (correctionEl && bs) {
        correctionEl.addEventListener('shown.bs.modal', function () { correctionEl.querySelector('[data-correction-accept]').focus(); });
        correctionEl.querySelector('[data-correction-accept]').addEventListener('click', function () {
            const form = document.querySelector('form[data-correction-form]');
            form.dataset.confirmed = '1';
            bs.Modal.getInstance(correctionEl).hide();
            if (form.requestSubmit) { form.requestSubmit(); } else { form.submit(); }
        });
    }

    /* Un único manejador de envío: validar → confirmar → spinner ------------- */
    document.addEventListener('submit', function (e) {
        const form = e.target;
        const submitter = e.submitter || null;

        if (form.matches('[data-validate]') && !validateForm(form)) {
            e.preventDefault();
            showToast('Revise los campos marcados.', 'danger');
            return;
        }

        const row = document.querySelector('tr[data-grade-row="' + form.id + '"]');
        if (row && form.id) {
            if (!checkRow(row)) {
                e.preventDefault();
                const bad = document.querySelector('input.grade-input.is-invalid[form="' + form.id + '"]');
                if (bad) { bad.focus(); }
                return;
            }
            // Esta fila se guarda; si quedan otras con cambios, el navegador avisará al salir.
            dirtyRows.delete(form.id);
        }

        if (form.dataset.confirmed !== '1') {
            if (form.matches('[data-correction-form]') && askCorrection(form)) { e.preventDefault(); return; }
            if ((form.dataset.confirmTitle || form.dataset.confirmMessage) && askConfirm(form, submitter)) { e.preventDefault(); return; }
        }

        if (form.method.toLowerCase() === 'post') { setLoading(form, submitter); }
    });
})();
