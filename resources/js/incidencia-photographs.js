const allowedExtensions = new Map([
    ['jpg', 'image/jpeg'],
    ['jpeg', 'image/jpeg'],
    ['png', 'image/png'],
    ['webp', 'image/webp'],
]);

function initializePhotographPicker(picker) {
    const input = picker.querySelector('[data-photograph-input]');
    const cameraInput = picker.querySelector('[data-photograph-camera-input]');
    const takePhotoButton = picker.querySelector('[data-photograph-take-photo]');
    const cameraModal = picker.querySelector('[data-photograph-camera-modal]');
    const preview = picker.querySelector('[data-photograph-preview]');
    const counter = picker.querySelector('[data-photograph-count]');
    const feedback = picker.querySelector('[data-photograph-feedback]');

    if (!input || !preview || !counter || !feedback) {
        return;
    }

    const savedCount = Number(picker.dataset.savedCount || 0);
    const maxCount = Number(picker.dataset.maxCount || 5);
    const maxFileSize = Number(picker.dataset.maxFileSize || 5242880);
    const selectedFiles = [];
    let cameraStream = null;
    let cameraRequestId = 0;
    let cameraModalOpen = false;
    let previousFocus = null;

    const fileKey = (file) => [file.name, file.size, file.lastModified, file.type].join(':');

    const cameraVideo = cameraModal?.querySelector('[data-photograph-camera-video]');
    const cameraStatus = cameraModal?.querySelector('[data-photograph-camera-status]');
    const cameraError = cameraModal?.querySelector('[data-photograph-camera-error]');
    const captureButton = cameraModal?.querySelector('[data-photograph-capture]');
    const closeCameraButtons = cameraModal?.querySelectorAll('[data-photograph-camera-close]');
    const nativeCameraButton = cameraModal?.querySelector('[data-photograph-native-camera]');

    function synchronizeInput() {
        const transfer = new DataTransfer();
        selectedFiles.forEach(({ file }) => transfer.items.add(file));
        input.files = transfer.files;
    }

    function render() {
        preview.replaceChildren();

        selectedFiles.forEach((entry, index) => {
            const column = document.createElement('div');
            column.className = 'col-6 col-md-4 col-lg-3';

            const card = document.createElement('div');
            card.className = 'photograph-preview-card';

            const image = document.createElement('img');
            image.src = entry.url;
            image.alt = `Vista previa de ${entry.file.name}`;
            image.className = 'photograph-preview-image';

            const fileName = document.createElement('span');
            fileName.className = 'photograph-preview-name';
            fileName.textContent = entry.file.name;
            fileName.title = entry.file.name;

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-outline-danger btn-sm photograph-preview-remove';
            remove.textContent = '×';
            remove.setAttribute('aria-label', `Quitar ${entry.file.name}`);
            remove.addEventListener('click', () => {
                URL.revokeObjectURL(entry.url);
                selectedFiles.splice(index, 1);
                synchronizeInput();
                render();
            });

            card.append(image, fileName, remove);
            column.append(card);
            preview.append(column);
        });

        const totalCount = savedCount + selectedFiles.length;
        counter.textContent = `${totalCount} de ${maxCount} fotografías (${savedCount} guardadas, ${selectedFiles.length} nuevas)`;
        if (takePhotoButton) {
            takePhotoButton.disabled = totalCount >= maxCount;
        }
    }

    function addFiles(files) {
        const messages = [];
        const knownFiles = new Set(selectedFiles.map(({ file }) => fileKey(file)));
        const availableSlots = Math.max(0, maxCount - savedCount - selectedFiles.length);
        let added = 0;

        files.forEach((file) => {
            const extension = file.name.split('.').pop()?.toLowerCase() || '';
            const expectedMime = allowedExtensions.get(extension);

            if (!expectedMime || (file.type && file.type !== expectedMime)) {
                messages.push(`${file.name}: formato no permitido. Use JPG, JPEG, PNG o WebP.`);
                return;
            }

            if (file.size > maxFileSize) {
                messages.push(`${file.name}: supera el límite de 5 MB.`);
                return;
            }

            const key = fileKey(file);
            if (knownFiles.has(key)) {
                messages.push(`${file.name}: ya está seleccionada; no se agregó otra copia.`);
                return;
            }

            if (added >= availableSlots) {
                messages.push(`Solo puede adjuntar ${maxCount} fotografías activas en total.`);
                return;
            }

            knownFiles.add(key);
            selectedFiles.push({ file, url: URL.createObjectURL(file) });
            added += 1;
        });

        synchronizeInput();
        render();

        feedback.textContent = messages.join(' ');
        feedback.hidden = messages.length === 0;
    }

    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach((track) => track.stop());
            cameraStream = null;
        }

        if (cameraVideo) {
            cameraVideo.pause();
            cameraVideo.srcObject = null;
        }
    }

    function closeCameraModal() {
        if (!cameraModal || !cameraModalOpen) {
            return;
        }

        cameraModalOpen = false;
        cameraRequestId += 1;
        stopCamera();
        cameraModal.classList.remove('show');
        cameraModal.setAttribute('aria-hidden', 'true');
        cameraModal.style.display = 'none';
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');

        if (previousFocus instanceof HTMLElement) {
            previousFocus.focus();
        }
    }

    function cameraErrorMessage(error) {
        if (error?.name === 'NotAllowedError' || error?.name === 'PermissionDeniedError') {
            return 'No se concedió permiso para usar la cámara. Revise los permisos del navegador y vuelva a intentarlo.';
        }
        if (error?.name === 'NotFoundError' || error?.name === 'DevicesNotFoundError') {
            return 'No se encontró una cámara disponible en este dispositivo.';
        }
        if (error?.name === 'NotReadableError' || error?.name === 'TrackStartError') {
            return 'La cámara está ocupada por otra aplicación o no se pudo iniciar.';
        }
        if (error?.name === 'SecurityError') {
            return 'El navegador bloqueó el acceso a la cámara. Use localhost o una conexión HTTPS.';
        }
        return 'No se pudo iniciar la cámara. Compruebe los permisos, la conexión segura y que ninguna otra aplicación la esté usando.';
    }

    async function requestCameraStream() {
        const mediaDevices = navigator.mediaDevices;
        if (!mediaDevices?.getUserMedia) {
            throw new Error('unsupported');
        }

        try {
            return await mediaDevices.getUserMedia({
                video: { facingMode: { exact: 'environment' } },
                audio: false,
            });
        } catch (error) {
            if (!['OverconstrainedError', 'NotFoundError', 'TypeError'].includes(error?.name)) {
                throw error;
            }

            return mediaDevices.getUserMedia({ video: true, audio: false });
        }
    }

    async function openCameraModal() {
        if (!cameraModal || !cameraVideo || !cameraStatus || !cameraError || !captureButton) {
            feedback.textContent = 'La ventana de cámara no está disponible en este formulario.';
            feedback.hidden = false;
            return;
        }

        previousFocus = document.activeElement;
        cameraModalOpen = true;
        const requestId = ++cameraRequestId;
        cameraModal.style.display = 'block';
        cameraModal.classList.add('show');
        cameraModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
        cameraError.hidden = true;
        cameraError.textContent = '';
        cameraStatus.textContent = 'Solicitando permiso para acceder a la cámara…';
        captureButton.disabled = true;
        cameraModal.querySelector('[data-photograph-camera-close]')?.focus();

        try {
            const stream = await requestCameraStream();
            if (!cameraModalOpen || requestId !== cameraRequestId) {
                stream.getTracks().forEach((track) => track.stop());
                return;
            }

            cameraStream = stream;
            cameraVideo.srcObject = stream;
            await cameraVideo.play();
            if (!cameraModalOpen || requestId !== cameraRequestId) {
                stopCamera();
                return;
            }

            cameraStatus.textContent = 'Encuadre la imagen y presione “Capturar foto”.';
            captureButton.disabled = false;
        } catch (error) {
            if (!cameraModalOpen || requestId !== cameraRequestId) {
                return;
            }

            stopCamera();
            cameraStatus.textContent = '';
            cameraError.textContent = error.message === 'unsupported'
                ? 'Este navegador no permite acceder a la cámara desde esta página. Use localhost o HTTPS, o abra la cámara del dispositivo.'
                : cameraErrorMessage(error);
            cameraError.hidden = false;
        }
    }

    async function capturePhoto() {
        if (!cameraVideo || !cameraStream || !cameraVideo.videoWidth || !cameraVideo.videoHeight) {
            return;
        }

        captureButton.disabled = true;
        cameraStatus.textContent = 'Procesando la fotografía…';
        const canvas = document.createElement('canvas');
        canvas.width = cameraVideo.videoWidth;
        canvas.height = cameraVideo.videoHeight;

        try {
            const context = canvas.getContext('2d');
            if (!context) {
                throw new Error('No se pudo procesar la imagen capturada.');
            }

            context.drawImage(cameraVideo, 0, 0, canvas.width, canvas.height);
            const blob = await new Promise((resolve, reject) => {
                canvas.toBlob((result) => {
                    if (result) {
                        resolve(result);
                    } else {
                        reject(new Error('No se pudo convertir la imagen a JPG.'));
                    }
                }, 'image/jpeg', 0.9);
            });

            if (!cameraModalOpen) {
                return;
            }

            const timestamp = new Date().toISOString().replace(/[-:.TZ]/g, '');
            addFiles([new File([blob], `fotografia-${timestamp}.jpg`, {
                type: 'image/jpeg',
                lastModified: Date.now(),
            })]);
            closeCameraModal();
        } catch (error) {
            cameraStatus.textContent = '';
            cameraError.textContent = error.message || 'No se pudo capturar la fotografía.';
            cameraError.hidden = false;
            captureButton.disabled = false;
        }
    }

    input.addEventListener('change', () => {
        addFiles(Array.from(input.files || []));
    });

    if (cameraInput && takePhotoButton) {
        takePhotoButton.addEventListener('click', openCameraModal);
        cameraInput.addEventListener('change', () => {
            addFiles(Array.from(cameraInput.files || []));
            cameraInput.value = '';
        });
    }

    if (cameraModal) {
        closeCameraButtons?.forEach((button) => button.addEventListener('click', closeCameraModal));
        captureButton?.addEventListener('click', capturePhoto);
        nativeCameraButton?.addEventListener('click', () => {
            closeCameraModal();
            cameraInput?.click();
        });

        cameraModal.addEventListener('click', (event) => {
            if (event.target === cameraModal) {
                closeCameraModal();
            }
        });

        cameraModal.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeCameraModal();
            }

            if (event.key === 'Tab') {
                const focusable = Array.from(cameraModal.querySelectorAll('button:not(:disabled)'));
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last?.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first?.focus();
                }
            }
        });
    }

    render();

    window.addEventListener('pagehide', stopCamera, { once: true });
    window.addEventListener('pagehide', () => {
        selectedFiles.forEach(({ url }) => URL.revokeObjectURL(url));
    }, { once: true });
}

document.querySelectorAll('[data-photograph-picker]').forEach(initializePhotographPicker);
