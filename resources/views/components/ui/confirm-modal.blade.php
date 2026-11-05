{{--
    Modal de confirmación único. Se activa desde un formulario con
    data-confirm-title, data-confirm-message, data-confirm-item y data-confirm-variant="danger".
    app.js rellena los textos con textContent (sin HTML). Escape cancela.
--}}
<div class="modal fade" id="confirm-modal" tabindex="-1" aria-labelledby="confirm-modal-title" aria-describedby="confirm-modal-message" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="confirm-modal-title">
                    <i data-confirm-slot="icon" class="bi bi-question-circle-fill text-primary" aria-hidden="true"></i>
                    <span data-confirm-slot="title">¿Confirmar la acción?</span>
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cancelar"></button>
            </div>
            <div class="modal-body">
                <p id="confirm-modal-message" class="mb-2" data-confirm-slot="message"></p>
                <div class="confirm-item" data-confirm-slot="item"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" data-confirm-accept>Confirmar</button>
            </div>
        </div>
    </div>
</div>
