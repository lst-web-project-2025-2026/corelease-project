@props([
    'id',
    'title' => '',
    'maxWidth' => 'md'
])

<dialog id="{{ $id }}" class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h3>{{ $title }}</h3>
            <button type="button" class="modal-close" onclick="document.getElementById('{{ $id }}').close()">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div class="modal-body">
            {{ $slot }}
        </div>
        @if(isset($footer))
            <div class="modal-footer">
                {{ $footer }}
            </div>
        @endif
    </div>
</dialog>

<script>
    (function() {
        const modal = document.getElementById('{{ $id }}');
        if (!modal) return;

        function updateBodyClass() {
            if (modal.open) {
                document.documentElement.classList.add('modal-open');
                document.body.classList.add('modal-open');
            } else {
                // Only remove if no other dialogs are open
                const otherOpenModals = Array.from(document.querySelectorAll('dialog')).filter(d => d.open && d !== modal);
                if (otherOpenModals.length === 0) {
                    document.documentElement.classList.remove('modal-open');
                    document.body.classList.remove('modal-open');
                }
            }
        }

        // Native <dialog> backdrop click to close
        modal.addEventListener('click', function (event) {
            if (event.target === this) {
                this.close();
            }
        });

        // The 'close' event is fired when the dialog is closed
        modal.addEventListener('close', updateBodyClass);

        // Use MutationObserver for the 'open' attribute (covers showModal)
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'open') {
                    updateBodyClass();
                }
            });
        });
        
        observer.observe(modal, { attributes: true });

        // Global helper
        window.toggleModal = function(id, state = true) {
            const m = document.getElementById(id);
            if (!m) return;
            if (state) {
                if (typeof m.showModal === 'function') m.showModal();
                else m.setAttribute('open', 'true');
            } else {
                if (typeof m.close === 'function') m.close();
                else m.removeAttribute('open');
            }
            updateBodyClass(); // Force update
        }
    })();
</script>

