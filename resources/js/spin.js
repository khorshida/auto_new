document.addEventListener('alpine:init', () => {
    window.Alpine.data('spinViewer', (frames) => ({
        frames,
        index: 0,
        pointerId: null,
        lastX: 0,

        get src() {
            return this.frames[this.index] ?? '';
        },

        previous() {
            this.step(-1);
        },

        next() {
            this.step(1);
        },

        step(direction) {
            if (this.frames.length === 0) {
                return;
            }

            this.index = (this.index + direction + this.frames.length) % this.frames.length;
        },

        start(event) {
            this.pointerId = event.pointerId;
            this.lastX = event.clientX;
            event.currentTarget.setPointerCapture(event.pointerId);
        },

        move(event) {
            if (this.pointerId !== event.pointerId) {
                return;
            }

            const delta = event.clientX - this.lastX;

            if (Math.abs(delta) < 24) {
                return;
            }

            this.step(delta > 0 ? -1 : 1);
            this.lastX = event.clientX;
        },

        end(event) {
            if (this.pointerId !== event.pointerId) {
                return;
            }

            this.pointerId = null;
        },
    }));
});
